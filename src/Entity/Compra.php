<?php

namespace App\Entity;

use App\Repository\CompraRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: CompraRepository::class)]
class Compra
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(["compra"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Serializer\Groups(["compra"])]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Serializer\Groups(["compra"])]
    private ?string $unidades = null;

    /** Fecha de la compra */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Serializer\Groups(["compra"])]
    private ?\DateTimeImmutable $fecha = null;

    #[ORM\Column]
    #[Serializer\Groups(["compra"])]
    private ?\DateTimeImmutable $fechaCreacion = null;

    #[ORM\Column]
    #[Serializer\Groups(["compra"])]
    private ?\DateTimeImmutable $fechaActualizacion = null;

    #[ORM\ManyToOne(targetEntity: Municipio::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Serializer\Groups(["compra"])]
    private ?Municipio $municipio = null;

    #[ORM\ManyToOne(targetEntity: Departamento::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Serializer\Groups(["compra"])]
    private ?Departamento $departamento = null;

    /** Observación de la compra */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Serializer\Groups(["compra"])]
    private ?string $observacion = null;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTimeImmutable();
        $this->fechaActualizacion = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getUnidades(): ?string
    {
        return $this->unidades;
    }

    public function setUnidades(string $unidades): static
    {
        $this->unidades = $unidades;

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

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(string $observacion): static
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getMunicipio(): ?Municipio
    {
        return $this->municipio;
    }

    public function setMunicipio(?Municipio $municipio): static
    {
        $this->municipio = $municipio;

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

    public function getFecha(): ?\DateTimeImmutable
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeImmutable $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }
}