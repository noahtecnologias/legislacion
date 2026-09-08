<?php

namespace App\Entity;

use App\Repository\PotencialDiarioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Entity(repositoryClass: PotencialDiarioRepository::class)]
#[ORM\Table(name: 'potencial_diario')]
class PotencialDiario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[JMS\Groups(['diario_listado'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[JMS\Groups(['diario_listado'])]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\ManyToOne(targetEntity: Potenciales::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[JMS\Groups(['diario_listado'])]
    private ?Potenciales $potencialBase = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[JMS\Groups(['diario_listado'])]
    private ?string $downtime = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 3)]
    #[JMS\Groups(['diario_listado'])]
    private ?string $realProd = '0.000';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 3)]
    #[JMS\Groups(['diario_listado'])]
    private ?string $perdida = '0.000';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[JMS\Groups(['diario_listado'])]
    private ?string $difercHs = '24.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 3, nullable: true)]
    #[JMS\Groups(['diario_listado'])]
    private ?string $valorControl = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[JMS\Groups(['diario_listado'])]
    private ?string $porcentajeControl = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[JMS\Groups(['diario_listado'])]
    private ?string $gor = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[JMS\Groups(['diario_listado'])] 
    private ?string $tipo = null;

    public function getId(): ?int { return $this->id; }

    public function getFecha(): ?\DateTimeInterface { return $this->fecha; }
    public function setFecha(\DateTimeInterface $fecha): static { $this->fecha = $fecha; return $this; }

    public function getPotencialBase(): ?Potenciales { return $this->potencialBase; }
    public function setPotencialBase(?Potenciales $potencialBase): static { $this->potencialBase = $potencialBase; return $this; }

    public function getDowntime(): ?string { return $this->downtime; }
    public function setDowntime(string $downtime): static { $this->downtime = $downtime; return $this; }

    public function getRealProd(): ?string { return $this->realProd; }
    public function setRealProd(string $realProd): static { $this->realProd = $realProd; return $this; }

    public function getPerdida(): ?string { return $this->perdida; }
    public function setPerdida(string $perdida): static { $this->perdida = $perdida; return $this; }

    public function getDiff(): ?string { return $this->difercHs; }
    public function getDifercHs(): ?string { return $this->difercHs; }
    public function setDifercHs(string $difercHs): static { $this->difercHs = $difercHs; return $this; }

    public function getValorControl(): ?string { return $this->valorControl; }
    public function setValorControl(?string $valorControl): static { $this->valorControl = $valorControl; return $this; }

    public function getPorcentajeControl(): ?string { return $this->porcentajeControl; }
    public function setPorcentajeControl(?string $porcentajeControl): static { $this->porcentajeControl = $porcentajeControl; return $this; }

    public function getGor(): ?string { return $this->gor; }
    public function setGor(?string $gor): static { $this->gor = $gor; return $this; }

    public function getTipo(): ?string { return $this->tipo; }
    public function setTipo(?string $tipo): self 
    { 
        $this->tipo = $tipo ? strtolower(trim($tipo)) : null; 
        return $this; 
    }
}