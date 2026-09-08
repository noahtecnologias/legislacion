<?php

namespace App\Entity;

use App\Repository\SaharaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SaharaRepository::class)]
class Sahara
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['sahara'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['sahara'])]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $oilDc = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $oil = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $npP = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $aguaDc = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $wpP = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $gasDc = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $gas = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $bruta = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $porcentajeW = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['sahara'])]
    private ?float $rgp = null;

    // ... Getters y Setters generados (puedes usar php bin/console make:entity para autocompletarlos)
    public function getId(): ?int { return $this->id; }
    public function getFecha(): ?\DateTimeInterface { return $this->fecha; }
    public function setFecha(\DateTimeInterface $fecha): self { $this->fecha = $fecha; return $this; }
    public function getOilDc(): ?float { return $this->oilDc; }
    public function setOilDc(?float $oilDc): self { $this->oilDc = $oilDc; return $this; }
    public function getOil(): ?float { return $this->oil; }
    public function setOil(?float $oil): self { $this->oil = $oil; return $this; }
    public function getNpP(): ?float { return $this->npP; }
    public function setNpP(?float $npP): self { $this->npP = $npP; return $this; }
    public function getAguaDc(): ?float { return $this->aguaDc; }
    public function setAguaDc(?float $aguaDc): self { $this->aguaDc = $aguaDc; return $this; }
    public function getWpP(): ?float { return $this->wpP; }
    public function setWpP(?float $wpP): self { $this->wpP = $wpP; return $this; }
    public function getGasDc(): ?float { return $this->gasDc; }
    public function setGasDc(?float $gasDc): self { $this->gasDc = $gasDc; return $this; }
    public function getGas(): ?float { return $this->gas; }
    public function setGas(?float $gas): self { $this->gas = $gas; return $this; }
    public function getBruta(): ?float { return $this->bruta; }
    public function setBruta(?float $bruta): self { $this->bruta = $bruta; return $this; }
    public function getPorcentajeW(): ?float { return $this->porcentajeW; }
    public function setPorcentajeW(?float $porcentajeW): self { $this->porcentajeW = $porcentajeW; return $this; }
    public function getRgp(): ?float { return $this->rgp; }
    public function setRgp(?float $rgp): self { $this->rgp = $rgp; return $this; }
}