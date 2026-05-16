<?php

namespace App\Entity;

use App\Repository\ElementoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ElementoRepository::class)
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
     * @ORM\Column(type="string", length=255)
     */
    private $tipo;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $latitud;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $longitud;

    /**
     * @ORM\Column(type="integer")
     */
    private $cantidad;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $id_estado;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $id_segmentacion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $id_region;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLatitud(): ?float
    {
        return $this->latitud;
    }

    public function setLatitud(?float $latitud): self
    {
        $this->latitud = $latitud;

        return $this;
    }

    public function getLongitud(): ?float
    {
        return $this->longitud;
    }

    public function setLongitud(?float $longitud): self
    {
        $this->longitud = $longitud;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getIdEstado(): ?int
    {
        return $this->id_estado;
    }

    public function setIdEstado(?int $id_estado): self
    {
        $this->id_estado = $id_estado;

        return $this;
    }

    public function getIdSegmentacion(): ?int
    {
        return $this->id_segmentacion;
    }

    public function setIdSegmentacion(?int $id_segmentacion): self
    {
        $this->id_segmentacion = $id_segmentacion;

        return $this;
    }

    public function getIdRegion(): ?int
    {
        return $this->id_region;
    }

    public function setIdRegion(?int $id_region): self
    {
        $this->id_region = $id_region;

        return $this;
    }
}
