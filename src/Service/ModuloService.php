<?php

namespace App\Service;

use App\Service\BaseService;
use App\Entity\Modulo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\Serializer\SerializationContext;

class ModuloService extends BaseService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function getModulos($serializer)
    {
        $modulos = $this->em->getRepository(Modulo::class)->findAll();
       // Crear el contexto de serialización
       $context = SerializationContext::create()->setGroups(['modulo']);
       return $serializer->toArray($modulos, $context);
    }
}