<?php

namespace App\Module\Config\Service;

use App\Module\Config\Entity\ConfigElemento;
use App\Module\Config\Entity\EsquemaRegional;
use App\Module\Config\Entity\EsquemaRegionEstado;
use App\Module\Config\Repository\ConfigElementoRepository;
use App\Module\Config\Repository\EsquemaRegionalRepository;
use App\Module\Config\Repository\EsquemaRegionEstadoRepository;
use App\Module\Geo\Entity\Estado;
use App\Module\Geo\Entity\Region;
use App\Module\Geo\Repository\EstadoRepository;
use App\Module\Historial\Service\HistorialService;
use Doctrine\ORM\EntityManagerInterface;

class ConfigService
{
    private $em;
    private $esquemaRepository;
    private $esquemaRegionEstadoRepository;
    private $configElementoRepository;
    private $estadoRepository;
    private $historialService;

    public function __construct(
        EntityManagerInterface $em,
        EsquemaRegionalRepository $esquemaRepository,
        EsquemaRegionEstadoRepository $esquemaRegionEstadoRepository,
        ConfigElementoRepository $configElementoRepository,
        EstadoRepository $estadoRepository,
        HistorialService $historialService
    ) {
        $this->em = $em;
        $this->esquemaRepository = $esquemaRepository;
        $this->esquemaRegionEstadoRepository = $esquemaRegionEstadoRepository;
        $this->configElementoRepository = $configElementoRepository;
        $this->estadoRepository = $estadoRepository;
        $this->historialService = $historialService;
    }

    public function getConfig(): array
    {
        $this->ensureDefaults();

        $esquemas = $this->esquemaRepository->findBy([], ['id' => 'ASC']);
        $esquemaActivo = $this->esquemaRepository->findActivo();
        $esquemaActivoClave = $esquemaActivo ? $esquemaActivo->getClave() : ($esquemas[0] ? $esquemas[0]->getClave() : 'estandar');

        $esquemasData = [];
        foreach ($esquemas as $esquema) {
            $relaciones = $this->esquemaRegionEstadoRepository->findBy(['esquema' => $esquema], ['id' => 'ASC']);
            $estadosPorRegion = [];
            foreach ($relaciones as $rel) {
                $regionId = $rel->getRegion()->getId();
                $estadosPorRegion[$regionId][] = $rel->getEstado()->getNombreEstado();
            }

            $regionesData = [];
            foreach ($esquema->getRegiones() as $region) {
                $regionesData[] = [
                    'id' => $region->getClave() ?: (string) $region->getId(),
                    'nombre' => $region->getNombreRegion(),
                    'color' => $region->getColor() ?: '#3B82F6',
                    'estados' => $estadosPorRegion[$region->getId()] ?? [],
                ];
            }

            $esquemasData[] = [
                'id' => $esquema->getClave(),
                'nombre' => $esquema->getNombre(),
                'regiones' => $regionesData,
            ];
        }

        $elementos = $this->configElementoRepository->findAllOrdered();
        $elementosData = [];
        foreach ($elementos as $el) {
            $elementosData[] = [
                'id' => $el->getClave(),
                'nombre' => $el->getNombre(),
                'icono' => $el->getIcono(),
                'color' => $el->getColor(),
                'visible' => $el->isVisible(),
            ];
        }

        return [
            'esquemaActivoId' => $esquemaActivoClave,
            'esquemas' => $esquemasData,
            'elementos' => $elementosData,
        ];
    }

    public function saveConfig(array $data, bool $marcarPredeterminado = false, string $accionHistorial = 'actualizar'): array
    {
        $validated = $this->validatePayload($data);
        $estadosPorNombre = $this->buildEstadosLookup();

        $this->em->wrapInTransaction(function () use ($validated, $estadosPorNombre, $marcarPredeterminado) {
            $this->em->createQuery('DELETE FROM App\Module\Config\Entity\EsquemaRegionEstado')->execute();
            $this->em->createQuery('DELETE FROM App\Module\Geo\Entity\Region r WHERE r.esquema IS NOT NULL')->execute();
            $this->em->createQuery('DELETE FROM App\Module\Config\Entity\EsquemaRegional')->execute();

            foreach ($validated['esquemas'] as $esqData) {
                $esquema = new EsquemaRegional();
                $esquema->setClave($esqData['id']);
                $esquema->setNombre($esqData['nombre']);
                $esquema->setEsActivo($esqData['id'] === $validated['esquemaActivoId']);
                $esquema->setEsPredeterminado($marcarPredeterminado);
                $this->em->persist($esquema);

                foreach ($esqData['regiones'] as $regData) {
                    $region = new Region();
                    $region->setClave($regData['id']);
                    $region->setNombreRegion($regData['nombre']);
                    $region->setColor($regData['color']);
                    $region->setEsquema($esquema);
                    $this->em->persist($region);

                    foreach ($regData['estados'] as $nombreEstado) {
                        $estado = $this->resolveEstado($nombreEstado, $estadosPorNombre);
                        $rel = new EsquemaRegionEstado();
                        $rel->setEsquema($esquema);
                        $rel->setRegion($region);
                        $rel->setEstado($estado);
                        $this->em->persist($rel);
                    }
                }
            }

            $this->em->createQuery('DELETE FROM App\Module\Config\Entity\ConfigElemento')->execute();
            foreach ($validated['elementos'] as $orden => $elData) {
                $elemento = new ConfigElemento();
                $elemento->setClave($elData['id']);
                $elemento->setNombre($elData['nombre']);
                $elemento->setIcono($elData['icono']);
                $elemento->setColor($elData['color']);
                $elemento->setVisible($elData['visible']);
                $elemento->setOrden($orden);
                $this->em->persist($elemento);
            }

            $this->em->flush();
        });

        $this->historialService->registrar(
            $accionHistorial,
            'configuracion',
            0,
            'SistemaConfig',
            [
                'esquemaActivoId' => $validated['esquemaActivoId'],
                'total_esquemas' => count($validated['esquemas']),
                'total_elementos' => count($validated['elementos']),
            ]
        );

        return $this->getConfig();
    }

    public function restaurarPredeterminados(): array
    {
        return $this->saveConfig($this->getDefaultConfigData(), true, 'restaurar');
    }

    private function ensureDefaults(): void
    {
        $hasEsquemas = $this->esquemaRepository->count([]) > 0;
        $hasElementos = $this->configElementoRepository->count([]) > 0;

        if ($hasEsquemas && $hasElementos) {
            return;
        }

        $defaults = $this->getDefaultConfigData();
        $estadosPorNombre = $this->buildEstadosLookup();

        $this->em->wrapInTransaction(function () use ($hasEsquemas, $hasElementos, $defaults, $estadosPorNombre) {
            if (!$hasEsquemas) {
                foreach ($defaults['esquemas'] as $esqData) {
                    $esquema = new EsquemaRegional();
                    $esquema->setClave($esqData['id']);
                    $esquema->setNombre($esqData['nombre']);
                    $esquema->setEsActivo($esqData['id'] === $defaults['esquemaActivoId']);
                    $esquema->setEsPredeterminado(true);
                    $this->em->persist($esquema);

                    foreach ($esqData['regiones'] as $regData) {
                        $region = new Region();
                        $region->setClave($regData['id']);
                        $region->setNombreRegion($regData['nombre']);
                        $region->setColor($regData['color']);
                        $region->setEsquema($esquema);
                        $this->em->persist($region);

                        foreach ($regData['estados'] as $nombreEstado) {
                            $key = mb_strtolower(trim($nombreEstado));
                            if (!isset($estadosPorNombre[$key])) {
                                continue;
                            }
                            $rel = new EsquemaRegionEstado();
                            $rel->setEsquema($esquema);
                            $rel->setRegion($region);
                            $rel->setEstado($estadosPorNombre[$key]);
                            $this->em->persist($rel);
                        }
                    }
                }
            }

            if (!$hasElementos) {
                foreach ($defaults['elementos'] as $orden => $elData) {
                    $elemento = new ConfigElemento();
                    $elemento->setClave($elData['id']);
                    $elemento->setNombre($elData['nombre']);
                    $elemento->setIcono($elData['icono']);
                    $elemento->setColor($elData['color']);
                    $elemento->setVisible($elData['visible']);
                    $elemento->setOrden($orden);
                    $this->em->persist($elemento);
                }
            }

            $this->em->flush();
        });
    }

    private function validatePayload(array $data): array
    {
        $esquemaActivoId = isset($data['esquemaActivoId']) ? trim((string) $data['esquemaActivoId']) : '';
        $esquemas = $data['esquemas'] ?? null;
        $elementos = $data['elementos'] ?? null;

        if (!is_array($esquemas) || count($esquemas) === 0) {
            throw new \InvalidArgumentException('Debe existir al menos un esquema regional.');
        }

        if (!is_array($elementos) || count($elementos) === 0) {
            throw new \InvalidArgumentException('Debe existir al menos un elemento configurado.');
        }

        $esquemasValidadas = [];
        $clavesEsquemas = [];

        foreach ($esquemas as $esq) {
            if (!is_array($esq)) {
                throw new \InvalidArgumentException('Formato de esquema inválido.');
            }
            $esqId = trim((string) ($esq['id'] ?? ''));
            $esqNombre = trim((string) ($esq['nombre'] ?? ''));
            $regiones = $esq['regiones'] ?? null;

            if ($esqId === '' || $esqNombre === '') {
                throw new \InvalidArgumentException('Cada esquema debe tener id y nombre válidos.');
            }
            if (in_array($esqId, $clavesEsquemas, true)) {
                throw new \InvalidArgumentException("Clave de esquema duplicada: {$esqId}.");
            }
            $clavesEsquemas[] = $esqId;

            if (!is_array($regiones) || count($regiones) === 0) {
                throw new \InvalidArgumentException("El esquema '{$esqNombre}' debe tener al menos una región.");
            }

            $regionesValidadas = [];
            $estadosUsadosEnEsquema = [];

            foreach ($regiones as $reg) {
                if (!is_array($reg)) {
                    throw new \InvalidArgumentException('Formato de región inválido.');
                }
                $regId = trim((string) ($reg['id'] ?? ''));
                $regNombre = trim((string) ($reg['nombre'] ?? ''));
                $regColor = trim((string) ($reg['color'] ?? ''));
                $regEstados = $reg['estados'] ?? [];

                if ($regId === '' || $regNombre === '') {
                    throw new \InvalidArgumentException("Cada región del esquema '{$esqNombre}' debe tener id y nombre.");
                }
                if (!$this->isValidHexColor($regColor)) {
                    throw new \InvalidArgumentException("Color hexadecimal inválido en región '{$regNombre}': {$regColor}.");
                }
                if (!is_array($regEstados)) {
                    throw new \InvalidArgumentException("Lista de estados inválida en región '{$regNombre}'.");
                }

                $estadosLimpios = [];
                foreach ($regEstados as $est) {
                    $nombreEstado = trim((string) $est);
                    if ($nombreEstado === '') {
                        continue;
                    }
                    $key = mb_strtolower($nombreEstado);
                    if (isset($estadosUsadosEnEsquema[$key])) {
                        throw new \InvalidArgumentException("El estado '{$nombreEstado}' está asignado a más de una región en '{$esqNombre}'.");
                    }
                    $estadosUsadosEnEsquema[$key] = true;
                    $estadosLimpios[] = $nombreEstado;
                }

                $regionesValidadas[] = [
                    'id' => $regId,
                    'nombre' => $regNombre,
                    'color' => $regColor,
                    'estados' => $estadosLimpios,
                ];
            }

            $esquemasValidadas[] = [
                'id' => $esqId,
                'nombre' => $esqNombre,
                'regiones' => $regionesValidadas,
            ];
        }

        if ($esquemaActivoId === '' || !in_array($esquemaActivoId, $clavesEsquemas, true)) {
            throw new \InvalidArgumentException('El esquemaActivoId no corresponde a ninguno de los esquemas enviados.');
        }

        $elementosValidados = [];
        $clavesElementos = [];

        foreach ($elementos as $el) {
            if (!is_array($el)) {
                throw new \InvalidArgumentException('Formato de elemento inválido.');
            }
            $elId = trim((string) ($el['id'] ?? ''));
            $elNombre = trim((string) ($el['nombre'] ?? ''));
            $elIcono = trim((string) ($el['icono'] ?? ''));
            $elColor = trim((string) ($el['color'] ?? ''));
            $elVisible = isset($el['visible']) ? (bool) $el['visible'] : true;

            if ($elId === '' || $elNombre === '' || $elIcono === '') {
                throw new \InvalidArgumentException('Cada elemento debe tener id, nombre e icono válidos.');
            }
            if (in_array($elId, $clavesElementos, true)) {
                throw new \InvalidArgumentException("Clave de elemento duplicada: {$elId}.");
            }
            $clavesElementos[] = $elId;

            if (!$this->isValidHexColor($elColor)) {
                throw new \InvalidArgumentException("Color hexadecimal inválido en elemento '{$elNombre}': {$elColor}.");
            }

            $elementosValidados[] = [
                'id' => $elId,
                'nombre' => $elNombre,
                'icono' => $elIcono,
                'color' => $elColor,
                'visible' => $elVisible,
            ];
        }

        return [
            'esquemaActivoId' => $esquemaActivoId,
            'esquemas' => $esquemasValidadas,
            'elementos' => $elementosValidados,
        ];
    }

    private function isValidHexColor(string $color): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
    }

    /**
     * @return array<string, Estado>
     */
    private function buildEstadosLookup(): array
    {
        $estados = $this->estadoRepository->findAll();
        $map = [];
        foreach ($estados as $estado) {
            $map[mb_strtolower(trim($estado->getNombreEstado()))] = $estado;
        }
        return $map;
    }

    /**
     * @param array<string, Estado> $estadosPorNombre
     */
    private function resolveEstado(string $nombreEstado, array $estadosPorNombre): Estado
    {
        $key = mb_strtolower(trim($nombreEstado));
        if (!isset($estadosPorNombre[$key])) {
            throw new \InvalidArgumentException("Estado no encontrado en base de datos: '{$nombreEstado}'.");
        }
        return $estadosPorNombre[$key];
    }

    private function getDefaultConfigData(): array
    {
        return [
            'esquemaActivoId' => 'estandar',
            'esquemas' => [
                [
                    'id' => 'estandar',
                    'nombre' => 'Estándar (9 Regiones)',
                    'regiones' => [
                        ['id' => 'capital', 'nombre' => 'Capital', 'color' => '#8B5CF6', 'estados' => ['Distrito Capital', 'Miranda', 'La Guaira']],
                        ['id' => 'central', 'nombre' => 'Central', 'color' => '#EF4444', 'estados' => ['Carabobo', 'Aragua', 'Cojedes']],
                        ['id' => 'llanos', 'nombre' => 'Los Llanos', 'color' => '#F59E0B', 'estados' => ['Guárico', 'Apure']],
                        ['id' => 'centro_occ', 'nombre' => 'Centro Occidental', 'color' => '#10B981', 'estados' => ['Falcón', 'Lara', 'Portuguesa', 'Yaracuy']],
                        ['id' => 'zuliana', 'nombre' => 'Zuliana', 'color' => '#3B82F6', 'estados' => ['Zulia']],
                        ['id' => 'andes', 'nombre' => 'Los Andes', 'color' => '#6366F1', 'estados' => ['Mérida', 'Táchira', 'Trujillo', 'Barinas']],
                        ['id' => 'nororiental', 'nombre' => 'Nororiental', 'color' => '#EC4899', 'estados' => ['Anzoátegui', 'Monagas', 'Sucre']],
                        ['id' => 'insular', 'nombre' => 'Insular', 'color' => '#06B6D4', 'estados' => ['Nueva Esparta', 'Dependencias Federales']],
                        ['id' => 'guayana', 'nombre' => 'Guayana', 'color' => '#14B8A6', 'estados' => ['Bolívar', 'Amazonas', 'Delta Amacuro']],
                    ],
                ],
                [
                    'id' => 'operaciones_7',
                    'nombre' => 'Operaciones / Transporte (7 Regiones)',
                    'regiones' => [
                        ['id' => 'cap7', 'nombre' => 'Capital', 'color' => '#EF4444', 'estados' => ['Distrito Capital', 'Miranda', 'La Guaira']],
                        ['id' => 'cen7', 'nombre' => 'Central', 'color' => '#F59E0B', 'estados' => ['Aragua', 'Carabobo', 'Guárico', 'Apure', 'Amazonas']],
                        ['id' => 'coc7', 'nombre' => 'Centro-Occidente', 'color' => '#EC4899', 'estados' => ['Lara', 'Yaracuy', 'Portuguesa', 'Cojedes']],
                        ['id' => 'occ7', 'nombre' => 'Occidente', 'color' => '#3B82F6', 'estados' => ['Zulia', 'Trujillo', 'Falcón']],
                        ['id' => 'and7', 'nombre' => 'Andes', 'color' => '#8B5CF6', 'estados' => ['Táchira', 'Barinas', 'Mérida']],
                        ['id' => 'ori7', 'nombre' => 'Oriente', 'color' => '#10B981', 'estados' => ['Anzoátegui', 'Sucre', 'Nueva Esparta']],
                        ['id' => 'gua7', 'nombre' => 'Guayana', 'color' => '#22C55E', 'estados' => ['Bolívar', 'Monagas']],
                    ],
                ],
            ],
            'elementos' => [
                ['id' => 'antenas', 'nombre' => 'Radio Bases', 'icono' => 'fas fa-broadcast-tower', 'color' => '#FF1493', 'visible' => true],
                ['id' => 'abonados', 'nombre' => 'Abonados', 'icono' => 'fas fa-user-check', 'color' => '#00BFFF', 'visible' => true],
                ['id' => 'oficinas', 'nombre' => 'Oficinas', 'icono' => 'fas fa-building', 'color' => '#32CD32', 'visible' => true],
                ['id' => 'agentes', 'nombre' => 'Agentes', 'icono' => 'fas fa-store', 'color' => '#FF8C00', 'visible' => true],
            ],
        ];
    }
}
