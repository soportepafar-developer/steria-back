<?php

namespace App\Module\Historial\Entity;

use App\Module\Historial\Repository\HistorialRepository;
use App\Module\User\Entity\Usuario;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HistorialRepository::class)
 * @ORM\Table(name="historial")
 */
class Historial
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $usuario;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $accion;

    /**
     * @ORM\Column(name="tabla_afectada", type="string", length=100)
     */
    private $tablaAfectada;

    /**
     * @ORM\Column(name="elemento_id", type="integer")
     */
    private $elementoId;

    /**
     * @ORM\Column(name="identificador_elemento", type="string", length=255)
     */
    private $identificadorElemento;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $detalles;

    /**
     * @ORM\Column(name="created_at", type="datetime", options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getAccion(): ?string
    {
        return $this->accion;
    }

    public function setAccion(string $accion): self
    {
        $this->accion = $accion;

        return $this;
    }

    public function getTablaAfectada(): ?string
    {
        return $this->tablaAfectada;
    }

    public function setTablaAfectada(string $tablaAfectada): self
    {
        $this->tablaAfectada = $tablaAfectada;

        return $this;
    }

    public function getElementoId(): ?int
    {
        return $this->elementoId;
    }

    public function setElementoId(int $elementoId): self
    {
        $this->elementoId = $elementoId;

        return $this;
    }

    public function getIdentificadorElemento(): ?string
    {
        return $this->identificadorElemento;
    }

    public function setIdentificadorElemento(string $identificadorElemento): self
    {
        $this->identificadorElemento = $identificadorElemento;

        return $this;
    }

    public function getDetalles(): ?array
    {
        return $this->detalles;
    }

    public function setDetalles(?array $detalles): self
    {
        $this->detalles = $detalles;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
