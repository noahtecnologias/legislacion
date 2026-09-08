<?php

namespace App\Service;

use App\Service\BaseService;
use App\Entity\Configuracion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\Serializer\SerializationContext;
use DateTime;

class ConfiguracionService extends BaseService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getConfiguracion(): Configuracion
    {
        $configuracion = $this->em
            ->getRepository(Configuracion::class)
            ->findOneBy([]);

        if (!$configuracion) {
            $configuracion = new Configuracion();
            $configuracion->setValorBarril('0.0000');
            $configuracion->setFechaCreacion(new \DateTime());

            $this->em->persist($configuracion);
            $this->em->flush();
        }

        return $configuracion;
    }

    public function actualizarValorBarril(string $valorBarril): Configuracion
    {
        $valorBarril = str_replace(',', '.', trim($valorBarril));

        if (!is_numeric($valorBarril)) {
            throw new \InvalidArgumentException('El valor del barril debe ser numérico.');
        }

        // DECIMAL(18,4)
        if (!preg_match('/^\d{1,14}(\.\d{1,4})?$/', $valorBarril)) {
            throw new \InvalidArgumentException(
                'El valor del barril debe tener hasta 14 enteros y 4 decimales.'
            );
        }

        $configuracion = $this->getConfiguracion();

        $configuracion->setValorBarril($valorBarril);
        $configuracion->setFechaActualizacion(new \DateTime());

        $this->em->flush();

        return $configuracion;
    }
}