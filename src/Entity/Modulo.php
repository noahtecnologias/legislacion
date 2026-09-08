<?php

namespace App\Entity;

use App\Repository\ModuloRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: ModuloRepository::class)]
class Modulo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(["modulo", "userdata"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Serializer\Groups(["modulo", "userdata"])]
    private ?string $nombre = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Serializer\Groups(["modulo", "userdata"])]
    private ?string $codigo = null;

    #[ORM\Column(nullable: true)]
    #[Serializer\Groups(["modulo", "userdata"])]
    private ?int $orden = null;

    #[ORM\Column]
    #[Serializer\Groups(["modulo", "userdata"])]
    private bool $activo = true;

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

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): static
    {
        $this->orden = $orden;

        return $this;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

        return $this;
    }
}