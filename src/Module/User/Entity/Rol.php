<?php

namespace App\Module\User\Entity;

use App\Module\User\Repository\RolRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RolRepository::class)
 * @ORM\Table(name="roles")
 */
class Rol
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="nombre_rol", type="string", length=50, unique=true)
     */
    private $nombreRol;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreRol(): ?string
    {
        return $this->nombreRol;
    }

    public function setNombreRol(string $nombreRol): self
    {
        $this->nombreRol = $nombreRol;

        return $this;
    }
}
