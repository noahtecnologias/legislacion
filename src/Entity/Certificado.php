<?php

namespace App\Entity;

use App\Repository\CertificadoRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CertificadoRepository::class)]
class Certificado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(["certificado"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Serializer\Groups(["certificado"])]
    private ?int $numero = null;

    #[ORM\Column(length: 255)]
    #[Serializer\Groups(["certificado"])]
    private ?string $destino = null;

    #[ORM\Column]
    #[Serializer\Groups(["certificado"])]
    private ?\DateTime $fecha = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $metrosCubicos = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $cantidadBarriles = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $precioBarrilUsd = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $dolarNeto = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $dolarIva = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $dolarTotal = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $cotizacionUsd = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $pesoNeto = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $pesoIva = null;

    #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $pesoTotal = null;

    #[ORM\Column]
    #[Serializer\Groups(["certificado"])]
    private ?\DateTime $fechaCreacion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechaActualizacion = null;

   #[ORM\Column(type: 'decimal', precision: 24, scale: 10)]
    #[Serializer\Groups(["certificado"])]
    private ?string $valorBarril = null;

    #[ORM\Column(type: 'string', nullable: true, length: 255)]
    #[Serializer\Groups(["certificado"])]
    private ?string $observacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;
        return $this;
    }

    public function getDestino(): ?string
    {
        return $this->destino;
    }

    public function setDestino(string $destino): self
    {
        $this->destino = $destino;
        return $this;
    }

    public function getFecha(): ?\DateTime
    {
        return $this->fecha;
    }

    public function setFecha(\DateTime $fecha): self
    {
        $this->fecha = $fecha;
        return $this;
    }

    public function getMetrosCubicos(): ?string
    {
        return $this->metrosCubicos;
    }

    public function setMetrosCubicos(string $metrosCubicos): self
    {
        $this->metrosCubicos = $metrosCubicos;
        return $this;
    }

    public function getCantidadBarriles(): ?string
    {
        return $this->cantidadBarriles;
    }

    public function setCantidadBarriles(string $cantidadBarriles): self
    {
        $this->cantidadBarriles = $cantidadBarriles;
        return $this;
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

    public function getPrecioBarrilUsd(): ?string
    {
        return $this->precioBarrilUsd;
    }

    public function setPrecioBarrilUsd(string $precioBarrilUsd): self
    {
        $this->precioBarrilUsd = $precioBarrilUsd;
        return $this;
    }

    public function getDolarNeto(): ?string
    {
        return $this->dolarNeto;
    }

    public function setDolarNeto(string $dolarNeto): self
    {
        $this->dolarNeto = $dolarNeto;
        return $this;
    }

    public function getDolarIva(): ?string
    {
        return $this->dolarIva;
    }

    public function setDolarIva(string $dolarIva): self
    {
        $this->dolarIva = $dolarIva;
        return $this;
    }

    public function getDolarTotal(): ?string
    {
        return $this->dolarTotal;
    }

    public function setDolarTotal(string $dolarTotal): self
    {
        $this->dolarTotal = $dolarTotal;
        return $this;
    }

    public function getCotizacionUsd(): ?string
    {
        return $this->cotizacionUsd;
    }

    public function setCotizacionUsd(string $cotizacionUsd): self
    {
        $this->cotizacionUsd = $cotizacionUsd;
        return $this;
    }

    public function getPesoNeto(): ?string
    {
        return $this->pesoNeto;
    }

    public function setPesoNeto(string $pesoNeto): self
    {
        $this->pesoNeto = $pesoNeto;
        return $this;
    }

    public function getPesoIva(): ?string
    {
        return $this->pesoIva;
    }

    public function setPesoIva(string $pesoIva): self
    {
        $this->pesoIva = $pesoIva;
        return $this;
    }

    public function getPesoTotal(): ?string
    {
        return $this->pesoTotal;
    }

    public function setPesoTotal(string $pesoTotal): self
    {
        $this->pesoTotal = $pesoTotal;
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

    public function setObservacion(string $observacion): self
    {
        $this->observacion = $observacion;
        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }
}