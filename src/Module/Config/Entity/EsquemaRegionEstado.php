<?php

namespace App\Module\Config\Entity;

use App\Module\Config\Repository\EsquemaRegionEstadoRepository;
use App\Module\Geo\Entity\Estado;
use App\Module\Geo\Entity\Region;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EsquemaRegionEstadoRepository::class)
 * @ORM\Table(
 *     name="esquema_region_estados",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="uniq_esquema_estado", columns={"esquema_id", "estado_id"})
 *     }
 * )
 */
class EsquemaRegionEstado
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=EsquemaRegional::class)
     * @ORM\JoinColumn(name="esquema_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $esquema;

    /**
     * @ORM\ManyToOne(targetEntity=Region::class)
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity=Estado::class)
     * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $estado;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEsquema(): ?EsquemaRegional
    {
        return $this->esquema;
    }

    public function setEsquema(?EsquemaRegional $esquema): self
    {
        $this->esquema = $esquema;
        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;
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
}
