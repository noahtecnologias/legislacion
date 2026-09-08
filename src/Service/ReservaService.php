<?php

namespace App\Service;

use App\Service\BaseService;
use App\Entity\Reserva;
use App\Entity\Cabania;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\Serializer\SerializationContext;
use DateTime;

class ReservaService extends BaseService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createCliente(string $title, string $content): Reserva
    {
        $reserva = new Reserva($title, $content);
        $this->em->persist($reserva);
        $this->em->flush();

        return $reserva;
    }

    public function getReservaById(int $id): ?Reserva
    {
        return $this->em->getRepository(Reserva::class)->find($id);
    }

    public function getReservas(): array
    {
        return $this->em->getRepository(Reserva::class)->findAll();
    }

    public function getReservasByCabania($cabaniaId)
    {
        return $this->em->getRepository(Reserva::class)->findBy(['cabania' => $cabaniaId]);
    }

    public function getById($id, $serializer)
    {
        $reserva = $this->em->getRepository(Reserva::class)->find($id);
       // Crear el contexto de serialización
       $context = SerializationContext::create()->setGroups(['reserva']);
       return $serializer->toArray($reserva, $context);
    }

    public function save($datos, $usuario)
    {
        $reserva = new Reserva();
        $reserva->setFechaCreacion(new DateTime());
        $reserva->setFechaDesde(new DateTime($datos['fecha_desde']));
        $reserva->setFechaHasta(new DateTime($datos['fecha_hasta']));
        $reserva->setNombre($datos['nombre']);
        $reserva->setApellido($datos['apellido']);
        $reserva->setDni($datos['dni']);
        $reserva->setOrigen($datos['origen']);
        $reserva->setCantidad((int)$datos['cantidad']);
        $reserva->setImporte((float)$datos['importe']);
        $cabania = $this->em->getRepository(Cabania::class)->find((int)$datos['_id']);
        $reserva->setCabania($cabania);
        $reserva->setUsuario($usuario);
        // Verificar si hay solapamientos con otras reservas
        if ($this->em->getRepository(Reserva::class)->checkReservaOverlap(null, $cabania->getId(), $reserva->getFechaDesde(), $reserva->getFechaHasta())) {
            throw new HttpException(409, "Las fechas seleccionadas ya están ocupadas por otra reserva.");
        }
        //Validar que la Fecha Desde no sea mayor a la Fecha Hasta
        if ($reserva->getFechaDesde() > $reserva->getFechaHasta()) {
            throw new HttpException(409, "Las Fecha Desde no debe ser mayor que la Fecha Hasta.");
        }
        
        $this->em->persist($reserva);
        $this->em->flush();
        return $reserva;
    }

    public function update ($datos, $usuario)
    {
        $reserva = $this->em->getRepository(Reserva::class)->find((int)$datos['reserva_id']);
        // Verificar si la reserva existe
        if (!$reserva) {
            throw new HttpException(409, "Reserva no encontrada");
        }

        $reserva->setFechaActualizacion(new DateTime());
        $reserva->setFechaDesde(new DateTime($datos['fecha_desde']));
        $reserva->setFechaHasta(new DateTime($datos['fecha_hasta']));
        $reserva->setNombre($datos['nombre']);
        $reserva->setApellido($datos['apellido']);
        $reserva->setDni($datos['dni']);
        $reserva->setOrigen($datos['origen']);
        $reserva->setCantidad((int)$datos['cantidad']);
        $reserva->setImporte((float)$datos['importe']);
        $cabania = $this->em->getRepository(Cabania::class)->find((int)$datos['_id']);
        $reserva->setCabania($cabania);
        $reserva->setUsuario($usuario);
        // Verificar si hay solapamientos con otras reservas
        if ($this->em->getRepository(Reserva::class)->checkReservaOverlap($reserva->getId(), $cabania->getId(), $reserva->getFechaDesde(), $reserva->getFechaHasta())) {
            throw new HttpException(409, "Las fechas seleccionadas ya están ocupadas por otra reserva.");
        }
        //Validar que la Fecha Desde no sea mayor a la Fecha Hasta
        if ($reserva->getFechaDesde() > $reserva->getFechaHasta()) {
            throw new HttpException(409, "Las Fecha Desde no debe ser mayor que la Fecha Hasta.");
        }

        $this->em->flush();
        return $reserva;

    }
}