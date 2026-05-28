<?php

namespace App\Module\Geo\Controller;

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

    public function __construct(
        RegionRepository $regionRepository,
        EstadoRepository $estadoRepository,
        NominatimService $nominatimService
    ) {
        $this->regionRepository = $regionRepository;
        $this->estadoRepository = $estadoRepository;
        $this->nominatimService = $nominatimService;
    }

    /**
     * @Route("/regiones", name="geo_regiones", methods={"GET"})
     */
    public function getRegiones(): JsonResponse
    {
        $regiones = $this->regionRepository->findAll();
        $data = [];
        foreach ($regiones as $r) {
            $data[] = [
                'id' => $r->getId(),
                'nombre_region' => $r->getNombreRegion()
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/estados", name="geo_estados", methods={"GET"})
     */
    public function getEstados(): JsonResponse
    {
        $estados = $this->estadoRepository->findAll();
        $data = [];
        foreach ($estados as $e) {
            $data[] = [
                'id' => $e->getId(),
                'nombre_estado' => $e->getNombreEstado(),
                'region_id' => $e->getRegion()->getId(),
                'region_nombre' => $e->getRegion()->getNombreRegion(),
                'color_estado' => $e->getColor(),
                'color_region' => $e->getRegion()->getColor(),
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
