<?php

namespace App\Service;

use App\Entity\Compra;
use App\Entity\Departamento;
use App\Entity\Municipio;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;

class CompraService extends BaseService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getCompras(): array
    {
        return $this->em
            ->getRepository(Compra::class)
            ->findAll();
    }

    public function getDepartamentos(): array
    {
        return $this->em
            ->getRepository(Departamento::class)
            ->findBy([], ['nombre' => 'ASC']);
    }

    public function getMunicipios(int $departamentoId): array
    {
        $departamento = $this->em
            ->getRepository(Departamento::class)
            ->find($departamentoId);

        if (!$departamento) {
            return [];
        }

        return $this->em
            ->getRepository(Municipio::class)
            ->findBy(
                ['departamento' => $departamento],
                ['nombre' => 'ASC']
            );
    }

    public function save(array $data): Compra
    {
        // Validar departamento
        if (empty($data['departamento'])) {
            throw new \Exception('Debe seleccionar un departamento.');
        }

        // Validar municipio
        if (empty($data['municipio'])) {
            throw new \Exception('Debe seleccionar un municipio/comuna.');
        }

        // Vlaidar campo fecha
        if (empty($data['fecha'])) {
            throw new \Exception('Debe ingresar una fecha.');
        }

        $departamento = $this->em
            ->getRepository(Departamento::class)
            ->find((int) $data['departamento']);

        if (!$departamento) {
            throw new \Exception('El departamento seleccionado no existe.');
        }

        $municipio = $this->em
            ->getRepository(Municipio::class)
            ->find((int) $data['municipio']);

        if (!$municipio) {
            throw new \Exception('El municipio/comuna seleccionado no existe.');
        }

        // Crear compra
        $compra = new Compra();

        $compra->setDescripcion(
            !empty($data['descripcion'])
                ? trim($data['descripcion'])
                : null
        );
        $compra->setUnidades(
            !empty($data['unidades'])
                ? trim($data['unidades'])
                : null
            );
        $compra->setDepartamento($departamento);
        $compra->setMunicipio($municipio);

        // Observación puede ser opcional
        $compra->setObservacion(
            !empty($data['observacion'])
                ? trim($data['observacion'])
                : null
        );

        $compra->setFecha(new \DateTimeImmutable($data['fecha']));

        // Fechas
        $ahora = new \DateTimeImmutable();

        $compra->setFechaCreacion($ahora);
        $compra->setFechaActualizacion($ahora);

        // Guardar
        $this->em->persist($compra);
        $this->em->flush();

        return $compra;
    }

    public function getById(int $id): ?Compra
    {
        return $this->em
            ->getRepository(Compra::class)
            ->find($id);
    }

    public function update(int $id, array $data): Compra
    {
        $compra = $this->em
            ->getRepository(Compra::class)
            ->find($id);

        if (!$compra) {
            throw new \Exception('La compra no existe.');
        }

        if (empty($data['departamento'])) {
            throw new \Exception('Debe seleccionar un departamento.');
        }

        if (empty($data['municipio'])) {
            throw new \Exception('Debe seleccionar un municipio/comuna.');
        }

        // Validar municipio
        if (empty($data['municipio'])) {
            throw new \Exception('Debe seleccionar un municipio/comuna.');
        }

        $departamento = $this->em
            ->getRepository(Departamento::class)
            ->find((int) $data['departamento']);

        if (!$departamento) {
            throw new \Exception('El departamento seleccionado no existe.');
        }

        $municipio = $this->em
            ->getRepository(Municipio::class)
            ->find((int) $data['municipio']);

        if (!$municipio) {
            throw new \Exception('El municipio/comuna seleccionado no existe.');
        }

        $compra->setDescripcion(
            !empty($data['descripcion'])
                ? trim($data['descripcion'])
                : null
        );
        $compra->setUnidades(
            !empty($data['unidades'])
                ? trim($data['unidades'])
                : null
            );
        $compra->setDepartamento($departamento);
        $compra->setMunicipio($municipio);
        $compra->setFecha(new \DateTimeImmutable($data['fecha']));

        $compra->setObservacion(
            !empty($data['observacion'])
                ? trim($data['observacion'])
                : null
        );

        $compra->setFechaActualizacion(new \DateTimeImmutable());

        $this->em->flush();

        return $compra;
    }

    public function delete($id) 
    {
        $compra = $this->em->getRepository(Compra::class)->find($id);

        if (!$compra) {
            throw new HttpException(409, "Compra no encontrada");
        }

        $this->em->remove($compra);
        $this->em->flush();
        return 'Compra eliminado con éxito';
    }
}