<?php

namespace App\Module\Elemento\Entity;

use App\Module\Elemento\Repository\ElementoRepository;
use App\Module\Geo\Entity\Estado;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ElementoRepository::class)
 * @ORM\Table(name="elementos")
 */
class Elemento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $tipo;

    /**
     * @ORM\ManyToOne(targetEntity=Estado::class)
     * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $latitud;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $longitud;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $direccion;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $actividad;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $tecnologia;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cantidad;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $segmentacion;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $codigoDealer;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $clasificacion;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $activo = true;

    /**
     * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getLatitud(): ?string
    {
        return $this->latitud;
    }

    public function setLatitud(?string $latitud): self
    {
        $this->latitud = $latitud;

        return $this;
    }

    public function getLongitud(): ?string
    {
        return $this->longitud;
    }

    public function setLongitud(?string $longitud): self
    {
        $this->longitud = $longitud;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getActividad(): ?string
    {
        return $this->actividad;
    }

    public function setActividad(?string $actividad): self
    {
        $this->actividad = $actividad;

        return $this;
    }

    public function getTecnologia(): ?string
    {
        return $this->tecnologia;
    }

    public function setTecnologia(?string $tecnologia): self
    {
        $this->tecnologia = $tecnologia;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(?int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getSegmentacion(): ?string
    {
        return $this->segmentacion;
    }

    public function setSegmentacion(?string $segmentacion): self
    {
        $this->segmentacion = $segmentacion;

        return $this;
    }

    public function getCodigoDealer(): ?string
    {
        return $this->codigoDealer;
    }

    public function setCodigoDealer(?string $codigoDealer): self
    {
        $this->codigoDealer = $codigoDealer;

        return $this;
    }

    public function getClasificacion(): ?string
    {
        return $this->clasificacion;
    }

    public function setClasificacion(?string $clasificacion): self
    {
        $this->clasificacion = $clasificacion;

        return $this;
    }

    public function getEstado(): ?Estado
    {
        return $this->estado;
    }

    public function setEstado(?Estado $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;

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
