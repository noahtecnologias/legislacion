<?php

namespace App\Entity;

use App\Repository\MunicipioRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: MunicipioRepository::class)]
class Municipio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(["municipio", "departamento", "voto"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Serializer\Groups(["municipio", "departamento", "voto"])]
    private ?string $nombre = null;

    #[ORM\Column]
    #[Serializer\Groups(["municipio", "departamento"])]
    private ?\DateTimeImmutable $fechaCreacion = null;

    #[ORM\Column]
    #[Serializer\Groups(["municipio", "departamento"])]
    private ?\DateTimeImmutable $fechaActualizacion = null;

    #[ORM\ManyToOne(
        targetEntity: Departamento::class,
        inversedBy: 'municipios'
    )]
    #[ORM\JoinColumn(
        nullable: false,
        onDelete: 'CASCADE'
    )]
    #[Serializer\Groups(["municipio"])]
    private ?Departamento $departamento = null;

    public function __construct()
    {
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

    public function getDepartamento(): ?Departamento
    {
        return $this->departamento;
    }

    public function setDepartamento(?Departamento $departamento): static
    {
        $this->departamento = $departamento;

        return $this;
    }
}