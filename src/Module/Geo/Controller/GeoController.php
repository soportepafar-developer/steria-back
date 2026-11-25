<?php

namespace App\Module\Geo\Controller;

use App\Module\Config\Repository\EsquemaRegionalRepository;
use App\Module\Config\Repository\EsquemaRegionEstadoRepository;
use App\Module\Geo\Repository\EstadoRepository;
use App\Module\Geo\Repository\RegionRepository;
use App\Module\Geo\Service\NominatimService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/geo")
 * @OA\Tag(name="Geografía")
 */
class GeoController extends AbstractController
{
    private $regionRepository;
    private $estadoRepository;
    private $nominatimService;
    private $esquemaRepository;
    private $esquemaRegionEstadoRepository;

    public function __construct(
        RegionRepository $regionRepository,
        EstadoRepository $estadoRepository,
        NominatimService $nominatimService,
        EsquemaRegionalRepository $esquemaRepository,
        EsquemaRegionEstadoRepository $esquemaRegionEstadoRepository
    ) {
        $this->regionRepository = $regionRepository;
        $this->estadoRepository = $estadoRepository;
        $this->nominatimService = $nominatimService;
        $this->esquemaRepository = $esquemaRepository;
        $this->esquemaRegionEstadoRepository = $esquemaRegionEstadoRepository;
    }

    /**
     * @Route("/regiones", name="geo_regiones", methods={"GET"})
     */
    public function getRegiones(): JsonResponse
    {
        $esquemaActivo = $this->esquemaRepository->findActivo();
        $regiones = $esquemaActivo
            ? $esquemaActivo->getRegiones()->toArray()
            : $this->regionRepository->findBy(['esquema' => null]);

        if (empty($regiones)) {
            $regiones = $this->regionRepository->findAll();
        }

        $data = [];
        foreach ($regiones as $r) {
            $data[] = [
                'id' => $r->getId(),
                'clave' => $r->getClave(),
                'nombre_region' => $r->getNombreRegion(),
                'color' => $r->getColor()
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/estados", name="geo_estados", methods={"GET"})
     */
    public function getEstados(): JsonResponse
    {
        $esquemaActivo = $this->esquemaRepository->findActivo();
        $regionMap = $esquemaActivo
            ? $this->esquemaRegionEstadoRepository->findEstadoRegionMapByEsquema($esquemaActivo)
            : [];

        $estados = $this->estadoRepository->findAll();
        $data = [];
        foreach ($estados as $e) {
            $region = $regionMap[$e->getId()] ?? $e->getRegion();
            $data[] = [
                'id' => $e->getId(),
                'nombre_estado' => $e->getNombreEstado(),
                'region_id' => $region ? $region->getId() : null,
                'region_nombre' => $region ? $region->getNombreRegion() : null,
                'color_estado' => $e->getColor(),
                'color_region' => $region ? $region->getColor() : null,
                'latitud' => $e->getLatitud(),
                'longitud' => $e->getLongitud()
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/buscar", name="geo_buscar", methods={"GET"})
     */
    public function buscarUbicacion(Request $request): JsonResponse
    {
        $query = $request->query->get('q');

        try {
            $data = $this->nominatimService->buscarDireccion((string) $query);
            return $this->json($data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error al contactar Nominatim: ' . $e->getMessage()], 500);
        }
    }
}
