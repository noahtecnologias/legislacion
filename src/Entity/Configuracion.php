<?php

namespace App\Entity;

use App\Repository\ConfiguracionRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: ConfiguracionRepository::class)]
class Configuracion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(['configuracion'])]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 18, scale: 4)]
    #[Serializer\Groups(['configuracion'])]
    private ?string $valorBarril = null;

    #[ORM\Column]
    private ?\DateTime $fechaCreacion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechaActualizacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValorBarril(): ?string
    {
        return $this->valorBarril;
    }

    public function setValorBarril(string $valorBarril): self
    {
        $this->valorBarril = $valorBarril;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTime
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTime $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getFechaActualizacion(): ?\DateTime
    {
        return $this->fechaActualizacion;
    }

    public function setFechaActualizacion(?\DateTime $fechaActualizacion): self
    {
        $this->fechaActualizacion = $fechaActualizacion;

        return $this;
    }
}