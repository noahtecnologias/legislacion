<?php

namespace App\Entity;

use App\Repository\PotencialesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

#[ORM\Entity(repositoryClass: PotencialesRepository::class)]
#[ORM\Table(name: 'potenciales')]
class Potenciales
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[JMS\Groups(['diario_listado'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "El número de pozo es obligatorio.")]
    #[Assert\Type(type: "integer", message: "El pozo debe ser un número entero.")]
    #[JMS\Groups(['diario_listado'])]
    private ?int $pozo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3)]
    #[Assert\NotBlank(message: "El potencial neto es obligatorio.")]
    #[Assert\Type(type: "numeric", message: "Debe ser un valor numérico.")]
    #[JMS\Groups(['diario_listado'])]
    private ?string $potencialNeta = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "El tipo es obligatorio.")]
    #[Assert\Choice(
        choices: ['petróleo', 'agua'], // CORRECCIÓN: Eliminamos 'gas' de las opciones válidas del admin
        message: "El tipo debe ser: petróleo o agua."
    )]
    #[JMS\Groups(['diario_listado'])]
    private ?string $tipo = null;

    public function getId(): ?int { return $this->id; }

    public function getPozo(): ?int { return $this->pozo; }
    public function setPozo(int $pozo): static { $this->pozo = $pozo; return $this; }

    public function getPotencialNeta(): ?string { return $this->potencialNeta; }
    public function setPotencialNeta(string $potencialNeta): static { $this->potencialNeta = $potencialNeta; return $this; }

    public function getTipo(): ?string { return $this->tipo; }
    public function setTipo(string $tipo): static { $this->tipo = $tipo; return $this; }
}