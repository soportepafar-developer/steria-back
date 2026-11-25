<?php

namespace App\Module\Config\Entity;

use App\Module\Config\Repository\ConfigElementoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfigElementoRepository::class)
 * @ORM\Table(name="config_elementos")
 */
class ConfigElemento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $clave;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $icono;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $color;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $visible = true;

    /**
     * @ORM\Column(type="smallint", options={"default"=0})
     */
    private $orden = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClave(): ?string
    {
        return $this->clave;
    }

    public function setClave(string $clave): self
    {
        $this->clave = $clave;
        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getIcono(): ?string
    {
        return $this->icono;
    }

    public function setIcono(string $icono): self
    {
        $this->icono = $icono;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function isVisible(): bool
    {
        return (bool) $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }

    public function getOrden(): int
    {
        return (int) $this->orden;
    }

    public function setOrden(int $orden): self
    {
        $this->orden = $orden;
        return $this;
    }
}
