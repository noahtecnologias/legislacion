<?php

namespace App\Entity;

use App\Repository\DepartamentoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: DepartamentoRepository::class)]
class Departamento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(["departamento", "municipio", "voto", "compra"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Serializer\Groups(["departamento", "municipio", "voto", "compra"])]
    private ?string $nombre = null;

    #[ORM\Column]
    #[Serializer\Groups(["departamento", "municipio"])]
    private ?\DateTimeImmutable $fechaCreacion = null;

    #[ORM\Column]
    #[Serializer\Groups(["departamento", "municipio"])]
    private ?\DateTimeImmutable $fechaActualizacion = null;

    /**
     * @var Collection<int, Municipio>
     */
    #[ORM\OneToMany(
        mappedBy: 'departamento',
        targetEntity: Municipio::class,
        orphanRemoval: true
    )]
    #[Serializer\Groups(["departamento"])]
    private Collection $municipios;

    public function __construct()
    {
        $this->municipios = new ArrayCollection();

        $this->fechaCreacion = new \DateTimeImmutable();
        $this->fechaActualizacion = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeImmutable
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeImmutable $fechaCreacion): static
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getFechaActualizacion(): ?\DateTimeImmutable
    {
        return $this->fechaActualizacion;
    }

    public function setFechaActualizacion(\DateTimeImmutable $fechaActualizacion): static
    {
        $this->fechaActualizacion = $fechaActualizacion;

        return $this;
    }

    /**
     * @return Collection<int, Municipio>
     */
    public function getMunicipios(): Collection
    {
        return $this->municipios;
    }

    public function addMunicipio(Municipio $municipio): static
    {
        if (!$this->municipios->contains($municipio)) {
            $this->municipios->add($municipio);
            $municipio->setDepartamento($this);
        }

        return $this;
    }

    public function removeMunicipio(Municipio $municipio): static
    {
        if ($this->municipios->removeElement($municipio)) {
            if ($municipio->getDepartamento() === $this) {
                $municipio->setDepartamento(null);
            }
        }

        return $this;
    }
}