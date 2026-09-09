<?php

namespace App\Entity;

use App\Repository\VotoRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: VotoRepository::class)]
class Voto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(["voto"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Serializer\Groups(["voto"])]
    private ?string $lista = null;

    #[ORM\Column(length: 255)]
    #[Serializer\Groups(["voto"])]
    private ?string $frente = null;

    #[ORM\Column]
    #[Serializer\Groups(["voto"])]
    private ?\DateTimeImmutable $fechaCreacion = null;

    #[ORM\Column]
    #[Serializer\Groups(["voto"])]
    private ?\DateTimeImmutable $fechaActualizacion = null;

    #[ORM\ManyToOne(targetEntity: Municipio::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Serializer\Groups(["voto"])]
    private ?Municipio $municipio = null;

    #[ORM\ManyToOne(targetEntity: Departamento::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Serializer\Groups(["voto"])]
    private ?Departamento $departamento = null;

    /** Periodo nombre del mes */
    #[ORM\Column(length: 100)]
    #[Serializer\Groups(["voto"])]
    private ?string $periodo = null;

    /* resultado de la votacion */
    #[ORM\Column]
    #[Serializer\Groups(["voto"])]
    private ?int $resultado = null;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTimeImmutable();
        $this->fechaActualizacion = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLista(): ?string
    {
        return $this->lista;
    }

    public function setLista(string $lista): static
    {
        $this->lista = $lista;

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

    public function getPeriodo(): ?string
    {
        return $this->periodo;
    }

    public function setPeriodo(string $periodo): static
    {
        $this->periodo = $periodo;

        return $this;
    }

    public function getResultado(): ?int
    {
        return $this->resultado;
    }

    public function setResultado(int $resultado): static
    {
        $this->resultado = $resultado;

        return $this;
    }

    public function getFrente(): ?string
    {
        return $this->frente;
    }

    public function setFrente(string $frente): static
    {
        $this->frente = $frente;

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
}