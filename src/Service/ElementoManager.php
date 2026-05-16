<?php
namespace App\Service;

use App\Entity\Elemento;
use App\Repository\ElementoRepository;
use Doctrine\ORM\EntityManagerInterface;

class ElementoManager
{
    private $em;
    private $repository;

    public function __construct(EntityManagerInterface $em, ElementoRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function createFromData(array $data): Elemento
    {
        $elemento = new Elemento();
        $elemento->setTipo($data['tipo']);
        $elemento->setLatitud($data['latitud']);
        $elemento->setLongitud($data['longitud']);
        $elemento->setCantidad($data['cantidad'] ?? 1);
        // ... setear campos de estado y región ...

        $this->em->persist($elemento);
        $this->em->flush();

        return $elemento;
    }
}