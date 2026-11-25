<?php

namespace App\Module\Config\Entity;

use App\Module\Config\Repository\EsquemaRegionalRepository;
use App\Module\Geo\Entity\Region;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EsquemaRegionalRepository::class)
 * @ORM\Table(name="esquemas_regionales")
 */
class EsquemaRegional
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
     * @ORM\Column(name="es_activo", type="boolean", options={"default"=false})
     */
    private $esActivo = false;

    /**
     * @ORM\Column(name="es_predeterminado", type="boolean", options={"default"=false})
     */
    private $esPredeterminado = false;

    /**
     * @ORM\OneToMany(targetEntity=Region::class, mappedBy="esquema", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $regiones;

    public function __construct()
    {
        $this->regiones = new ArrayCollection();
    }

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

    public function isEsActivo(): bool
    {
        return (bool) $this->esActivo;
    }

    public function setEsActivo(bool $esActivo): self
    {
        $this->esActivo = $esActivo;
        return $this;
    }

    public function isEsPredeterminado(): bool
    {
        return (bool) $this->esPredeterminado;
    }

    public function setEsPredeterminado(bool $esPredeterminado): self
    {
        $this->esPredeterminado = $esPredeterminado;
        return $this;
    }

    /**
     * @return Collection|Region[]
     */
    public function getRegiones(): Collection
    {
        return $this->regiones;
    }

    public function addRegion(Region $region): self
    {
        if (!$this->regiones->contains($region)) {
            $this->regiones[] = $region;
            $region->setEsquema($this);
        }

        return $this;
    }

    public function removeRegion(Region $region): self
    {
        if ($this->regiones->removeElement($region)) {
            if ($region->getEsquema() === $this) {
                $region->setEsquema(null);
            }
        }

        return $this;
    }
}
