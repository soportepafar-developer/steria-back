<?php

namespace App\Module\Elemento\Service;

use App\Module\Elemento\Entity\Elemento;
use App\Module\Elemento\Repository\ElementoRepository;
use App\Module\Geo\Repository\EstadoRepository;
use App\Module\Historial\Service\HistorialService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ElementoService
{
    private $em;
    private $elementoRepository;
    private $estadoRepository;
    private $security;
    private $historialService;

    public function __construct(
        EntityManagerInterface $em,
        ElementoRepository $elementoRepository,
        EstadoRepository $estadoRepository,
        Security $security,
        HistorialService $historialService
    ) {
        $this->em = $em;
        $this->elementoRepository = $elementoRepository;
        $this->estadoRepository = $estadoRepository;
        $this->security = $security;
        $this->historialService = $historialService;
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

        if (isset($data['latitud'])) {
            $elemento->setLatitud($data['latitud']);
        }
        if (isset($data['longitud'])) {
            $elemento->setLongitud($data['longitud']);
        }
        if (isset($data['direccion'])) {
            $elemento->setDireccion($data['direccion']);
        }
        if (isset($data['actividad'])) {
            $elemento->setActividad($data['actividad']);
        }
        if (isset($data['tecnologia'])) {
            $elemento->setTecnologia($data['tecnologia']);
        }
        if (isset($data['cantidad'])) {
            $elemento->setCantidad((int) $data['cantidad']);
        }
        if (isset($data['segmentacion'])) {
            $elemento->setSegmentacion($data['segmentacion']);
        }
        if (isset($data['codigo_dealer'])) {
            $elemento->setCodigoDealer($data['codigo_dealer']);
        }
        if (isset($data['clasificacion'])) {
            $elemento->setClasificacion($data['clasificacion']);
        }

        $this->em->persist($elemento);
        $this->em->flush();

        // Registrar en el historial
        $this->historialService->registrar(
            'crear',
            'elementos',
            $elemento->getId(),
            $elemento->getNombre(),
            $data
        );

        return $elemento;
    }

    public function updateElemento(int $id, array $data): Elemento
    {
        $elemento = $this->elementoRepository->find($id);
        if (!$elemento) {
            throw new \InvalidArgumentException('Elemento no encontrado.');
        }

        $detalles = [];
        $checkChange = function (string $field, $oldValue, $newValue) use (&$detalles) {
            if ($oldValue !== $newValue) {
                $detalles[$field] = [
                    'antes' => $oldValue,
                    'despues' => $newValue
                ];
                return true;
            }
            return false;
        };

        if (isset($data['nombre'])) {
            if ($checkChange('nombre', $elemento->getNombre(), $data['nombre'])) {
                $elemento->setNombre($data['nombre']);
            }
        }

        if (isset($data['tipo'])) {
            if ($checkChange('tipo', $elemento->getTipo(), $data['tipo'])) {
                $elemento->setTipo($data['tipo']);
            }
        }

        if (isset($data['estado_id'])) {
            $oldEstadoId = $elemento->getEstado() ? $elemento->getEstado()->getId() : null;
            if ($oldEstadoId !== (int) $data['estado_id']) {
                $estado = $this->estadoRepository->find($data['estado_id']);
                if (!$estado) {
                    throw new \InvalidArgumentException('Estado no encontrado.');
                }
                $detalles['estado_id'] = [
                    'antes' => $oldEstadoId,
                    'despues' => (int) $data['estado_id']
                ];
                $elemento->setEstado($estado);
            }
        }

        if (isset($data['latitud'])) {
            if ($checkChange('latitud', $elemento->getLatitud(), $data['latitud'])) {
                $elemento->setLatitud($data['latitud']);
            }
        }

        if (isset($data['longitud'])) {
            if ($checkChange('longitud', $elemento->getLongitud(), $data['longitud'])) {
                $elemento->setLongitud($data['longitud']);
            }
        }

        if (isset($data['direccion'])) {
            if ($checkChange('direccion', $elemento->getDireccion(), $data['direccion'])) {
                $elemento->setDireccion($data['direccion']);
            }
        }

        if (isset($data['actividad'])) {
            if ($checkChange('actividad', $elemento->getActividad(), $data['actividad'])) {
                $elemento->setActividad($data['actividad']);
            }
        }

        if (isset($data['tecnologia'])) {
            if ($checkChange('tecnologia', $elemento->getTecnologia(), $data['tecnologia'])) {
                $elemento->setTecnologia($data['tecnologia']);
            }
        }

        if (isset($data['cantidad'])) {
            $newCantidad = (int) $data['cantidad'];
            if ($checkChange('cantidad', $elemento->getCantidad(), $newCantidad)) {
                $elemento->setCantidad($newCantidad);
            }
        }

        if (isset($data['segmentacion'])) {
            if ($checkChange('segmentacion', $elemento->getSegmentacion(), $data['segmentacion'])) {
                $elemento->setSegmentacion($data['segmentacion']);
            }
        }

        if (isset($data['codigo_dealer'])) {
            if ($checkChange('codigo_dealer', $elemento->getCodigoDealer(), $data['codigo_dealer'])) {
                $elemento->setCodigoDealer($data['codigo_dealer']);
            }
        }

        if (isset($data['clasificacion'])) {
            if ($checkChange('clasificacion', $elemento->getClasificacion(), $data['clasificacion'])) {
                $elemento->setClasificacion($data['clasificacion']);
            }
        }

        if (isset($data['activo'])) {
            $newActivo = (bool) $data['activo'];
            if ($checkChange('activo', $elemento->getActivo(), $newActivo)) {
                $elemento->setActivo($newActivo);
            }
        }

        $this->em->flush();

        // Registrar historial si hubo cambios
        if (!empty($detalles)) {
            $this->historialService->registrar(
                'actualizar',
                'elementos',
                $elemento->getId(),
                $elemento->getNombre(),
                $detalles
            );
        }

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

        // Registrar en el historial
        $this->historialService->registrar(
            'desactivar',
            'elementos',
            $elemento->getId(),
            $elemento->getNombre(),
            ['activo' => ['antes' => true, 'despues' => false]]
        );
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
