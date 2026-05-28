<?php

namespace App\Module\Elemento\Service;

use App\Module\Elemento\Entity\Elemento;
use App\Module\Elemento\Repository\ElementoRepository;
use App\Module\Geo\Repository\EstadoRepository;
use Doctrine\ORM\EntityManagerInterface;

class ElementoService
{
    private $em;
    private $elementoRepository;
    private $estadoRepository;

    public function __construct(
        EntityManagerInterface $em,
        ElementoRepository $elementoRepository,
        EstadoRepository $estadoRepository
    ) {
        $this->em = $em;
        $this->elementoRepository = $elementoRepository;
        $this->estadoRepository = $estadoRepository;
    }

    public function createElemento(array $data): Elemento
    {
        $nombre = $data['nombre'] ?? null;
        $tipo = $data['tipo'] ?? null;
        $estadoId = $data['estado_id'] ?? null;

        if (!$nombre || !$tipo || !$estadoId) {
            throw new \InvalidArgumentException('Faltan campos obligatorios: nombre, tipo, estado_id.');
        }

        $estado = $this->estadoRepository->find($estadoId);
        if (!$estado) {
            throw new \InvalidArgumentException('Estado no encontrado.');
        }

        $elemento = new Elemento();
        $elemento->setNombre($nombre);
        $elemento->setTipo($tipo);
        $elemento->setEstado($estado);
        $elemento->setActivo(true);

        $this->em->persist($elemento);
        $this->em->flush();

        return $elemento;
    }

    public function updateElemento(int $id, array $data): Elemento
    {
        $elemento = $this->elementoRepository->find($id);
        if (!$elemento) {
            throw new \InvalidArgumentException('Elemento no encontrado.');
        }

        if (isset($data['nombre'])) {
            $elemento->setNombre($data['nombre']);
        }

        if (isset($data['tipo'])) {
            $elemento->setTipo($data['tipo']);
        }

        if (isset($data['estado_id'])) {
            $estado = $this->estadoRepository->find($data['estado_id']);
            if (!$estado) {
                throw new \InvalidArgumentException('Estado no encontrado.');
            }
            $elemento->setEstado($estado);
        }

        if (isset($data['activo'])) {
            $elemento->setActivo((bool) $data['activo']);
        }

        $this->em->flush();

        return $elemento;
    }

    public function deactivateElemento(int $id): void
    {
        $elemento = $this->elementoRepository->find($id);
        if (!$elemento) {
            throw new \InvalidArgumentException('Elemento no encontrado.');
        }

        $elemento->setActivo(false);
        $this->em->flush();
    }

    public function getResumenPorEstado(): array
    {
        $elementos = $this->elementoRepository->findBy(['activo' => true]);
        
        $grupos = [];
        foreach ($elementos as $elem) {
            $tipo = $elem->getTipo();
            $estadoName = $elem->getEstado()->getNombreEstado();
            $regionName = $elem->getEstado()->getRegion()->getNombreRegion();
            $key = $tipo . '|' . $estadoName;

            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'tipo' => $tipo,
                    'estado' => $estadoName,
                    'region' => $regionName,
                    'total' => 0
                ];
            }
            
            $cantidad = $elem->getCantidad() ?: 1;
            $grupos[$key]['total'] += $cantidad;
        }

        return array_values($grupos);
    }
}
