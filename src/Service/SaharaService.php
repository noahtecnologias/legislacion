<?php

namespace App\Service;

use App\Entity\Sahara;
use App\Entity\ProduccionDiaria;
use Doctrine\ORM\EntityManagerInterface;

class SaharaService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Calcula todos los indicadores para el formulario Sahara basados en la fecha elegida.
     */
    public function calcularDatosSahara($mes, $anio)
    {
        // 1. Configuración de fechas
        $fechaInicio = new \DateTime("$anio-$mes-01 00:00:00");
        $fechaFin = (clone $fechaInicio)->modify('last day of this month')->setTime(23, 59, 59);
        $diasDelMes = (int)$fechaFin->format('d');

        // 2. Obtener registros de producción diaria del mes seleccionado
        $registrosDiarios = $this->em->getRepository(ProduccionDiaria::class)->createQueryBuilder('p')
            ->where('p.fecha >= :inicio')
            ->andWhere('p.fecha <= :fin')
            ->setParameter('inicio', $fechaInicio)
            ->setParameter('fin', $fechaFin)
            ->getQuery()
            ->getResult();

        $sumaNeta = 0;
        $sumaAguaProducida = 0;
        $sumaGasProducido = 0;
        $cantidadDiasCargados = count($registrosDiarios);

        foreach ($registrosDiarios as $reg) {
            $sumaNeta += (float) $reg->getNeta();
            $sumaAguaProducida += (float) $reg->getAguaProducida();
            $sumaGasProducido += (float) $reg->getGasProducido();
        }

        // --- CÁLCULOS BASE ---
        $oilDc = ($cantidadDiasCargados > 0) ? ($sumaNeta / $cantidadDiasCargados) : 0;
        $oilMensual = $sumaNeta;
        
        $aguaDc = ($cantidadDiasCargados > 0) ? ($sumaAguaProducida / $cantidadDiasCargados) : 0;
        
        $gasDc = ($cantidadDiasCargados > 0) ? ($sumaGasProducido / $cantidadDiasCargados) : 0;
        $gasMensual = $sumaGasProducido;

        // --- FÓRMULAS DE INGENIERÍA ---
        $bruta = $aguaDc + $oilDc;
        $porcentajeW = ($bruta > 0) ? ($aguaDc / $bruta) * 100 : 0;
        $rgp = ($oilMensual > 0) ? ($gasMensual / $oilMensual) * 1000 : 0;

        // --- ACUMULADOS (Buscando Mes Anterior) ---
        $fechaMesAnterior = (clone $fechaInicio)->modify('-1 month');
        $saharaAnterior = $this->em->getRepository(Sahara::class)->findOneBy(['fecha' => $fechaMesAnterior]);

        $nppAnterior = $saharaAnterior ? (float)$saharaAnterior->getNpP() : 0;
        $nppCalculado = $nppAnterior + $oilMensual;

        $wppAnterior = $saharaAnterior ? (float)$saharaAnterior->getWpP() : 0;
        $wppCalculado = $wppAnterior + ($aguaDc * $diasDelMes);

        return [
            'oil_dc'       => $oilDc,
            'oil'          => $oilMensual,
            'npp'          => $nppCalculado,
            'agua_dc'      => $aguaDc,
            'wpp'          => $wppCalculado,
            'gas_dc'       => $gasDc,
            'gas'          => $gasMensual,
            'bruta'        => $bruta,
            'porcentaje_w' => $porcentajeW,
            'rgp'          => $rgp
        ];
    }

    /**
     * Guarda o actualiza un registro de Sahara.
     */
    public function save($datos)
    {
        if (empty($datos['fecha'])) {
            throw new \Exception("La fecha es obligatoria.");
        }

        $fechaObj = \DateTime::createFromFormat('d/m/Y', $datos['fecha']);
        // Normalizamos al día 1 del mes para consistencia en la DB
        $fechaObj->modify('first day of this month');

        // Buscamos si ya existe para ese mes para actualizar, o creamos uno nuevo
        $registro = $this->em->getRepository(Sahara::class)->findOneBy(['fecha' => $fechaObj]) ?? new Sahara();

        $registro->setFecha($fechaObj);
        $registro->setOilDc($this->limpiar($datos['oil_dc'] ?? 0));
        $registro->setOil($this->limpiar($datos['oil'] ?? 0));
        $registro->setNpP($this->limpiar($datos['npp'] ?? 0));
        $registro->setAguaDc($this->limpiar($datos['agua_dc'] ?? 0));
        $registro->setWpP($this->limpiar($datos['wpp'] ?? 0));
        $registro->setGasDc($this->limpiar($datos['gas_dc'] ?? 0));
        $registro->setGas($this->limpiar($datos['gas'] ?? 0));
        $registro->setBruta($this->limpiar($datos['bruta'] ?? 0));
        $registro->setPorcentajeW($this->limpiar($datos['porcentaje_w'] ?? 0));
        $registro->setRgp($this->limpiar($datos['rgp'] ?? 0));

        $this->em->persist($registro);
        $this->em->flush();

        return $registro;
    }

    /**
     * Convierte los strings del formulario (con comas y puntos) a float compatible con SQL.
     */
    private function limpiar($valor)
    {
        if (is_null($valor) || $valor === '') return 0;
        
        // Si el valor viene como "1.234,56", lo convierte a "1234.56"
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        
        return (float) $valor;
    }
}