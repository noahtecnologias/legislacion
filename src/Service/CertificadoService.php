<?php

namespace App\Service;

use App\Service\BaseService;
use App\Entity\Certificado;
use App\Entity\Configuracion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\Serializer\SerializationContext;
use DateTime;

class CertificadoService extends BaseService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getCertificadoById(int $id): ?Certificado
    {
        return $this->em->getRepository(Certificado::class)->find($id);
    }

    public function getCertificados(
        ?int $mes = null,
        ?int $anio = null
    )
    {
        return $this->em
            ->getRepository(Certificado::class)
            ->getCertificados(
                $mes,
                $anio
            );
    }

    public function getById($id, $serializer)
    {
        $certificado = $this->em->getRepository(Certificado::class)->find($id);
       // Crear el contexto de serialización
       $context = SerializationContext::create()->setGroups(['certificado']);
       return $serializer->toArray($certificado, $context);
    }

    private function parseNumber(string $value): float
    {
        // 1. eliminar separadores de miles
        $value = str_replace('.', '', $value);

        // 2. cambiar coma decimal por punto
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    private function decimal(string $valor): string
    {
        return str_replace(',', '.', $valor);
    }

    public function crear(array $data): Certificado
    {
        $config = $this->em->getRepository(Configuracion::class)->findOneBy([]);

        $certificado = new Certificado();

        // =========================
        // INPUTS
        // =========================
        $metrosCubicos = $this->decimal($data['metrosCubicos']);
        $precioUsd     = $this->decimal($data['precioBarrilUsd']);
        $cotizacion    = $this->decimal($data['cotizacionUsd']);
        $numero        = (int) $data['numero'];
        $observacion = $data['observacion'] ?? null;

        // =========================
        // CONFIG
        // =========================
        $valorBarril = (float) $config->getValorBarril();

        // =========================
        // BARRILES
        // =========================
        $barriles = bcmul($metrosCubicos, $valorBarril, 10);

        // =========================
        // USD
        // =========================
        $dolarNeto = bcmul($barriles, $precioUsd, 10);
        $dolarIva = bcmul($dolarNeto, '0.21', 10);
        $dolarTotal = bcadd($dolarNeto, $dolarIva, 10);

        // =========================
        // PESOS
        // =========================
        $pesoNeto = bcmul($dolarNeto, $cotizacion, 10);
        $pesoIva = bcmul($dolarIva, $cotizacion, 10);
        $pesoTotal = bcadd($pesoNeto, $pesoIva, 10);

        // =========================
        // SET ENTITY
        // =========================
        $certificado->setNumero($numero);
        $certificado->setDestino($data['destino']);
        $certificado->setFecha(new \DateTime($data['fecha']));

        $certificado->setMetrosCubicos($metrosCubicos);
        $certificado->setCantidadBarriles($barriles);
        $certificado->setValorBarril($valorBarril);

        $certificado->setPrecioBarrilUsd($precioUsd);
        $certificado->setCotizacionUsd($cotizacion);

        $certificado->setDolarNeto($dolarNeto);
        $certificado->setDolarIva($dolarIva);
        $certificado->setDolarTotal($dolarTotal);

        $certificado->setPesoNeto($pesoNeto);
        $certificado->setPesoIva($pesoIva);
        $certificado->setPesoTotal($pesoTotal);
        $certificado->setObservacion($observacion);

        $certificado->setFechaCreacion(new \DateTime());

        $this->em->persist($certificado);
        $this->em->flush();

        return $certificado;
    }

    public function actualizar(int $id, array $data): Certificado
    {
        $certificado = $this->getCertificadoById($id);

        $config = $this->em->getRepository(Configuracion::class)->findOneBy([]);

        // INPUTS
        $cotizacion    = $this->parseNumber($data['cotizacionUsd']);
        $precioUsd  = $this->parseNumber($data['precioBarrilUsd']);
        $metrosCubicos = $this->parseNumber($data['metrosCubicos']);
        $observacion = $data['observacion'] ?? null;

        // CONFIG
        $valorBarril = $config->getValorBarril();

        // CALCULOS
        $barriles = $metrosCubicos * $valorBarril;

        $barriles = bcmul($metrosCubicos, $valorBarril, 10);
        $dolarNeto = bcmul($barriles, $precioUsd, 10);
        $dolarIva = bcmul($dolarNeto, '0.21', 10);
        $dolarTotal = bcadd($dolarNeto, $dolarIva, 10);
        $pesoNeto = bcmul($dolarNeto, $cotizacion, 10);
        $pesoIva = bcmul($dolarIva, $cotizacion, 10);
        $pesoTotal = bcadd($pesoNeto, $pesoIva, 10);

        // UPDATE ENTITY
        $certificado->setDestino($data['destino']);
        $certificado->setFecha(new \DateTime($data['fecha']));

        $certificado->setMetrosCubicos($metrosCubicos);
        $certificado->setCantidadBarriles($barriles);
        $certificado->setValorBarril($valorBarril);

        $certificado->setPrecioBarrilUsd($precioUsd);
        $certificado->setCotizacionUsd($cotizacion);

        $certificado->setDolarNeto($dolarNeto);
        $certificado->setDolarIva($dolarIva);
        $certificado->setDolarTotal($dolarTotal);

        $certificado->setPesoNeto($pesoNeto);
        $certificado->setPesoIva($pesoIva);
        $certificado->setPesoTotal($pesoTotal);
        $certificado->setObservacion($observacion);

        $certificado->setFechaActualizacion(new \DateTime());

        $this->em->flush();

        return $certificado;
    }
}