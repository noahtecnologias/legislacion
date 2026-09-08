<?php

namespace App\Service;

use App\Entity\PotencialDiario;
use App\Entity\Potenciales;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class PotencialDiarioService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function guardarCargaDiaria(string $fechaStr, array $detalles): void
    {
        $fecha = new DateTime($fechaStr);

        // 1. Limpieza de registros en la misma fecha para sobreescritura limpia
        $existentes = $this->em->getRepository(PotencialDiario::class)->findBy(['fecha' => $fecha]);
        foreach ($existentes as $doc) {
            $this->em->remove($doc);
        }
        
        // Sacamos el $this->em->flush() de acá arriba para consolidar todo en una única transacción al final

        // 2. Procesamiento e inserción de los detalles del parte diario
        foreach ($detalles as $item) {
            $potencialBase = $this->em->getRepository(Potenciales::class)->find((int)$item['potencial_base_id']);
            if (!$potencialBase) {
                continue;
            }

            $diario = new PotencialDiario();
            $diario->setFecha($fecha);
            $diario->setPotencialBase($potencialBase);

            // CORRECCIÓN FUNDAMENTAL: Persistimos el tipo explícito enviado desde el JS ('petroleo', 'agua' o 'gas')
            if (isset($item['tipo'])) {
                $diario->setTipo($item['tipo']);
            }

            $downtime = (float)str_replace(',', '.', $item['downtime']);
            $difercHs = 24 - $downtime;
            
            // VERIFICACIÓN DE TIPO DE CÁLCULO (GAS CON GOR vs ESTÁNDAR)
            if (isset($item['gor']) && $item['gor'] !== null && $item['gor'] !== '') {
                // Es un registro calculado de gas
                $gor = (float)$item['gor'];
                $diario->setGor(number_format($gor, 2, '.', ''));
                
                // Potencial Gas = Neta Petroleo * GOR
                $netaPetroleo = (float)$potencialBase->getPotencialNeta();
                $potencialGas = $netaPetroleo * $gor;
                
                // Real Gas = Potencial Gas * (24 - DT) / 24
                $realProd = $potencialGas * ($difercHs / 24);
                $perdida = $potencialGas - $realProd;
            } else {
                // Petróleo o Agua convencional
                $neta = (float)$potencialBase->getPotencialNeta();
                $realProd = $neta * (1 - ($downtime / 24));
                $perdida = $neta - $realProd;
            }

            // Persistencia formateada de métricas
            $diario->setDowntime(number_format($downtime, 2, '.', ''));
            $diario->setRealProd(number_format($realProd, 3, '.', ''));
            $diario->setPerdida(number_format($perdida, 3, '.', ''));
            $diario->setDifercHs(number_format($difercHs, 2, '.', ''));

            if (!empty($item['valor_control'])) {
                $diario->setValorControl(str_replace(',', '.', $item['valor_control']));
            }
            if (!empty($item['porcentaje_control'])) {
                $diario->setPorcentajeControl(str_replace(',', '.', $item['porcentaje_control']));
            }

            $this->em->persist($diario);
        }

        // Un solo flush guarda los nuevos y remueve los viejos de forma segura en la base de datos
        $this->em->flush();
    }
}