<?php

namespace App\Service;

use App\Entity\ProduccionDiaria;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;

class ProduccionService extends BaseService
{
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * Obtiene el listado completo para el DataTable
     */
    public function getListado($serializer) {
        // Buscamos todos los registros ordenados por fecha descendente
        $data = $this->em->getRepository(ProduccionDiaria::class)->findBy([], ['fecha' => 'DESC']);
        
        // Configuramos el contexto para que solo serialice lo que marcamos con @Groups({"produccion"})
        $context = SerializationContext::create()->setGroups(['produccion']);
        
        // Retornamos el array ya procesado
        return $serializer->toArray($data, $context);
    }

    public function save($datos) {
        try {
            if (!isset($datos['fecha']) || empty($datos['fecha'])) {
                throw new \Exception("La fecha es obligatoria.");
            }

            $fechaObj = \DateTime::createFromFormat('d/m/Y', $datos['fecha']);
            if (!$fechaObj) {
                throw new \Exception("Formato de fecha inválido.");
            }

            $produccion = $this->em->getRepository(ProduccionDiaria::class)->findOneBy(['fecha' => $fechaObj]);

            if (!$produccion) {
                $produccion = new ProduccionDiaria();
                $produccion->setFecha($fechaObj);
                $produccion->setFechaCreacion(new \DateTime());
            } else {
                $produccion->setFechaActualizacion(new \DateTime());
            }

            // Mapeo de campos
            $produccion->setGasProducido($this->limpiarNum($datos['gas_producido'] ?? null));
            $produccion->setGasVenteado($this->limpiarNum($datos['gas_venteado'] ?? null));
            $produccion->setGasInyectado($this->limpiarNum($datos['gas_inyectado'] ?? null));
            $produccion->setGas9300Kcal($this->limpiarNum($datos['gas_9300_kcal'] ?? null));
            $produccion->setPoderCalorifico($this->limpiarNum($datos['poder_calorifico'] ?? null));
            $produccion->setGasCombustible($this->limpiarNum($datos['gas_combustible'] ?? null));
            $produccion->setGasConvertido($this->limpiarNum($datos['gas_convertido'] ?? null));
            $produccion->setPropano($this->limpiarNum($datos['propano'] ?? null));
            $produccion->setBruta($this->limpiarNum($datos['bruta'] ?? null));
            $produccion->setNeta($this->limpiarNum($datos['neta'] ?? null));
            $produccion->setAguaProducida($this->limpiarNum($datos['agua_producida'] ?? null));
            $produccion->setAguaInyectada($this->limpiarNum($datos['agua_inyectada'] ?? null));
            $produccion->setPetroleoDespachado($this->limpiarNum($datos['petroleo_despachado'] ?? null));
            $produccion->setStockActual($this->limpiarNum($datos['stock_actual'] ?? null));
            $produccion->setLluviaCaida($this->limpiarNum($datos['lluvia_caida'] ?? null));
            
            if(isset($datos['horas_generador'])) $produccion->setHorasEscioGenerador($this->limpiarNum($datos['horas_generador']));
            if(isset($datos['horas_lts'])) $produccion->setHorasEscioLts($this->limpiarNum($datos['horas_lts']));

            $this->em->persist($produccion);
            $this->em->flush();
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function limpiarNum($v) {
        if ($v === null || $v === '' || $v === 'null') return null;
        $v = str_replace('.', '', $v);
        return str_replace(',', '.', $v);
    }
}