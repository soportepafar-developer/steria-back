<?php

namespace App\Module\Geo\Entity;

use App\Module\Geo\Repository\RegionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RegionRepository::class)
 * @ORM\Table(name="regiones")
 */
class Region
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="nombre", type="string", length=100)
     */
    private $nombreRegion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreRegion(): ?string
    {
        return $this->nombreRegion;
    }

    public function setNombreRegion(string $nombreRegion): self
    {
        $this->nombreRegion = $nombreRegion;

        return $this;
    }
}
