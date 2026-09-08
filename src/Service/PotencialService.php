<?php

namespace App\Service;

use App\Service\BaseService;
use App\Entity\Potenciales;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DateTime;

class PotencialService extends BaseService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getPotenciales(): array
    {
        return $this->em->getRepository(Potenciales::class)->findAll();
    }

    public function getById($id)
    {
        return $this->em->getRepository(Potenciales::class)->find($id);
    }

    public function save($datos)
    {
        $potencial = new Potenciales();
        $potencial->setPozo((int)$datos['pozo']);
        // Usamos str_replace por si el input viene con coma en vez de punto
        $potencial->setPotencialNeta(str_replace(',', '.', $datos['potencial_neta']));
        $potencial->setTipo($datos['tipo']);

        $this->em->persist($potencial);
        $this->em->flush();
        return $potencial;
    }

    public function delete($id) 
    {
        $potencial = $this->em->getRepository(Potenciales::class)->find($id);
        if (!$potencial) {
            throw new HttpException(404, "Potencial no encontrado");
        }
        $this->em->remove($potencial);
        $this->em->flush();
    }

    public function update($id, $datos)
    {
        $potencial = $this->em->getRepository(Potenciales::class)->find($id);

        if (!$potencial) {
            throw new HttpException(404, "Registro no encontrado");
        }

        $potencial->setPozo((int)$datos['pozo']);
        $potencial->setPotencialNeta(str_replace(',', '.', $datos['potencial_neta']));
        $potencial->setTipo($datos['tipo']);

        $this->em->flush();
        return $potencial;
    }
}