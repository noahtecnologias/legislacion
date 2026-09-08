<?php

namespace App\Repository;

use App\Entity\Reserva;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @extends ServiceEntityRepository<Reserva>
 */
class ReservaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reserva::class);
    }

    public function checkReservaOverlap($reservaId, $cabaniaId, $fechaDesde, $fechaHasta)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where('r.cabania = :cabaniaId')->setParameter('cabaniaId', $cabaniaId);
        $qb->andWhere('r.fechaDesde < :fechaHasta')->setParameter('fechaHasta', $fechaHasta);
        $qb->andWhere('r.fechaHasta > :fechaDesde')->setParameter('fechaDesde', $fechaDesde);
        if (!is_null($reservaId)) {
            $qb->andWhere('r.id != :reservaId')->setParameter('reservaId', $reservaId);
        }
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result !== null;
    }
}
