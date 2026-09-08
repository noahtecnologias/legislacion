<?php

namespace App\Entity;

use App\Repository\ProduccionDiariaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: ProduccionDiariaRepository::class)]
#[ORM\Table(name: 'produccion_diaria')]
class ProduccionDiaria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(["produccion"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Serializer\Groups(["produccion"])]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(name: "gas_producido", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $gasProducido = null;

    #[ORM\Column(name: "gas_venteado", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $gasVenteado = null;

    #[ORM\Column(name: "gas_inyectado", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $gasInyectado = null;

    #[ORM\Column(name: "gas_9300_kcal", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $gas9300Kcal = null;

    #[ORM\Column(name: "poder_calorifico", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $poderCalorifico = null;

    #[ORM\Column(name: "gas_combustible", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $gasCombustible = null;

    #[ORM\Column(name: "gas_convertido", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $gasConvertido = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $propano = null;

    #[ORM\Column(name: "despacho_prop_buta", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $despachoPropButa = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $bruta = null;

    #[ORM\Column(name: "agua_merlin", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $aguaMerlin = null;

    #[ORM\Column(name: "agua_producida", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $aguaProducida = null;

    #[ORM\Column(name: "neta", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $neta = null;

    #[ORM\Column(name: "agua_inyectada", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $aguaInyectada = null;

    #[ORM\Column(name: "petroleo_despachado", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $petroleoDespachado = null;

    #[ORM\Column(name: "stock_actual", type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $stockActual = null;

    #[ORM\Column(name: "lluvia_caida", type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $lluviaCaida = null;

    #[ORM\Column(name: "horas_servicio_generador", type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $horasEscioGenerador = null;

    #[ORM\Column(name: "horas_servicio_lts", type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Serializer\Groups(["produccion"])]
    private ?string $horasEscioLts = null;

    #[ORM\Column(name: "fecha_creacion", type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaCreacion = null;

    #[ORM\Column(name: "fecha_actualizacion", type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fechaActualizacion = null;

    public function __construct() { $this->fechaCreacion = new \DateTime(); }

    public function getId(): ?int { return $this->id; }
    public function getFecha(): ?\DateTimeInterface { return $this->fecha; }
    public function setFecha(\DateTimeInterface $fecha): self { $this->fecha = $fecha; return $this; }
    public function getGasProducido(): ?string { return $this->gasProducido; }
    public function setGasProducido(?string $val): self { $this->gasProducido = $val; return $this; }
    public function getGasVenteado(): ?string { return $this->gasVenteado; }
    public function setGasVenteado(?string $val): self { $this->gasVenteado = $val; return $this; }
    public function getGasInyectado(): ?string { return $this->gasInyectado; }
    public function setGasInyectado(?string $val): self { $this->gasInyectado = $val; return $this; }
    public function getGas9300Kcal(): ?string { return $this->gas9300Kcal; }
    public function setGas9300Kcal(?string $val): self { $this->gas9300Kcal = $val; return $this; }
    public function getPoderCalorifico(): ?string { return $this->poderCalorifico; }
    public function setPoderCalorifico(?string $val): self { $this->poderCalorifico = $val; return $this; }
    public function getGasCombustible(): ?string { return $this->gasCombustible; }
    public function setGasCombustible(?string $val): self { $this->gasCombustible = $val; return $this; }
    public function getGasConvertido(): ?string { return $this->gasConvertido; }
    public function setGasConvertido(?string $val): self { $this->gasConvertido = $val; return $this; }
    public function getPropano(): ?string { return $this->propano; }
    public function setPropano(?string $val): self { $this->propano = $val; return $this; }
    public function getBruta(): ?string { return $this->bruta; }
    public function setBruta(?string $val): self { $this->bruta = $val; return $this; }
    public function getAguaProducida(): ?string { return $this->aguaProducida; }
    public function setAguaProducida(?string $val): self { $this->aguaProducida = $val; return $this; }
    public function getNeta(): ?string { return $this->neta; }
    public function setNeta(?string $val): self { $this->neta = $val; return $this; }
    public function getAguaInyectada(): ?string { return $this->aguaInyectada; }
    public function setAguaInyectada(?string $val): self { $this->aguaInyectada = $val; return $this; }
    public function getStockActual(): ?string { return $this->stockActual; }
    public function setStockActual(?string $val): self { $this->stockActual = $val; return $this; }
    public function getLluviaCaida(): ?string { return $this->lluviaCaida; }
    public function setLluviaCaida(?string $val): self { $this->lluviaCaida = $val; return $this; }
    public function getHorasEscioGenerador(): ?string { return $this->horasEscioGenerador; }
    public function setHorasEscioGenerador(?string $val): self { $this->horasEscioGenerador = $val; return $this; }
    public function getHorasEscioLts(): ?string { return $this->horasEscioLts; }
    public function setHorasEscioLts(?string $val): self { $this->horasEscioLts = $val; return $this; }
    public function getFechaCreacion(): ?\DateTimeInterface { return $this->fechaCreacion; }
    public function setFechaCreacion(\DateTimeInterface $val): self { $this->fechaCreacion = $val; return $this; }
    public function getFechaActualizacion(): ?\DateTimeInterface { return $this->fechaActualizacion; }
    public function setFechaActualizacion(?\DateTimeInterface $val): self { $this->fechaActualizacion = $val; return $this; }

    public function getPetroleoDespachado(): ?string { 
        return $this->petroleoDespachado; 
    }
    
    public function setPetroleoDespachado(?string $val): self { 
        $this->petroleoDespachado = $val; 
        return $this; 
    }

}