<?php

namespace App\Repository;

use App\Entity\Sahara;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sahara>
 *
 * @method Sahara|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sahara|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sahara[]    findAll()
 * @method Sahara[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaharaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sahara::class);
    }

    // Aquí puedes agregar métodos personalizados para consultas complejas en el futuro
}