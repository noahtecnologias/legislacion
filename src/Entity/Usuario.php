<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use App\Entity\Roles;
use App\Entity\Modulo;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(["userdata"])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Serializer\Groups(["userdata"])]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    #[Serializer\Groups(["userdata"])]
    private ?string $nombre = null;

    #[ORM\Column(length: 100)]
    #[Serializer\Groups(["userdata"])]
    private ?string $apellido = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Serializer\Groups(["userdata"])]
    private ?string $celular = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 1, nullable: true)]
    #[Serializer\Groups(["userdata"])]
    private ?int $activo = null;

    #[ORM\Column]
    private ?\DateTime  $fechaCreacion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime  $fechaActualizacion = null;

    #[Serializer\Groups(["userdata"])]
    #[ORM\ManyToMany(targetEntity: Roles::class, inversedBy: 'usuarios')]
    #[ORM\JoinTable(name: 'usuario_roles')]
    private $roles;

    #[Serializer\Groups(["userdata"])]
    #[ORM\ManyToMany(targetEntity: Modulo::class)]
    #[ORM\JoinTable(name: 'usuario_modulos')]
    private Collection $modulos;


    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->modulos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNombrel(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): static
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getCelular(): ?string
    {
        return $this->celular;
    }

    public function setCelular(string $celular): static
    {
        $this->celular = $celular;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getRoles(): array
    {
        $rolesAux = array();
        foreach ($this->roles as $role) {
            $rolesAux[] = $role->getNombre();
        }
        return $rolesAux;
    }

    public function addRol(Roles $roles): self
    {
        $this->roles[] = $roles;
        return $this;
    }

    public function removeRol(Roles $roles): self
    {
        $this->roles->removeElement($roles);

        return $this;
    }

    public function getActivo(): ?int
    {
        return $this->activo;
    }

    public function setActivo(int $activo): static
    {
        $this->activo = $activo;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTime 
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTime  $fechaCreacion): static
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getFechaActualizacion(): ?\DateTime 
    {
        return $this->fechaActualizacion;
    }

    public function setFechaActualizacion(\DateTime  $fechaActualizacion): static
    {
        $this->fechaActualizacion = $fechaActualizacion;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function addRole(Roles $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(Roles $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }

    public function getModulos(): Collection
    {
        return $this->modulos;
    }

    public function addModulo(Modulo $modulo): static
    {
        if (!$this->modulos->contains($modulo)) {
            $this->modulos->add($modulo);
        }

        return $this;
    }

    public function removeModulo(Modulo $modulo): static
    {
        $this->modulos->removeElement($modulo);

        return $this;
    }
}
