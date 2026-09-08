<?php

namespace App\Repository;

use App\Entity\Certificado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certificado>
 */
class CertificadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certificado::class);
    }

    public function getCertificados(
        ?int $mes = null,
        ?int $anio = null
    ): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($mes && $anio) {

            $desde = new \DateTime(sprintf(
                '%04d-%02d-01',
                $anio,
                $mes
            ));

            $hasta = (clone $desde)
                ->modify('first day of next month');

            $qb
                ->andWhere('c.fecha >= :desde')
                ->andWhere('c.fecha < :hasta')
                ->setParameter('desde', $desde)
                ->setParameter('hasta', $hasta);

        } elseif ($anio) {

            $desde = new \DateTime($anio . '-01-01');

            $hasta = new \DateTime(
                ($anio + 1) . '-01-01'
            );

            $qb
                ->andWhere('c.fecha >= :desde')
                ->andWhere('c.fecha < :hasta')
                ->setParameter('desde', $desde)
                ->setParameter('hasta', $hasta);
        }

        return $qb
            ->orderBy('c.fecha', 'ASC')
            ->addOrderBy('c.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
