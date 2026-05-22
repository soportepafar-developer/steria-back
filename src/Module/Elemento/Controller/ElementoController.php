<?php

namespace App\Module\Elemento\Controller;

use App\Module\Elemento\Repository\ElementoRepository;
use App\Module\Elemento\Service\ElementoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/elementos")
 * @OA\Tag(name="Elementos")
 */
class ElementoController extends AbstractController
{
    private $elementoRepository;
    private $elementoService;

    public function __construct(
        ElementoRepository $elementoRepository,
        ElementoService $elementoService
    ) {
        $this->elementoRepository = $elementoRepository;
        $this->elementoService = $elementoService;
    }

    /**
     * @Route("/listado", name="elementos_list", methods={"GET"})
     * @OA\Get(
     *     summary="Lista todos los elementos activos",
     *     @OA\Response(
     *         response=200,
     *         description="Retorna el listado de elementos",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="nombre", type="string"),
     *                 @OA\Property(property="tipo", type="string"),
     *                 @OA\Property(property="estado_id", type="integer"),
     *                 @OA\Property(property="estado_nombre", type="string"),
     *                 @OA\Property(property="region_nombre", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function listElementos(): JsonResponse
    {
        $elementos = $this->elementoRepository->findBy(['activo' => true]);
        $data = [];
        foreach ($elementos as $e) {
            $data[] = [
                'id' => $e->getId(),
                'nombre' => $e->getNombre(),
                'tipo' => $e->getTipo(),
                'estado_id' => $e->getEstado()->getId(),
                'estado_nombre' => $e->getEstado()->getNombreEstado(),
                'region_nombre' => $e->getEstado()->getRegion()->getNombreRegion(),
                'created_at' => $e->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/crear", name="elemento_create", methods={"POST"})
     * @OA\Post(
     *     summary="Crea un nuevo elemento geográfico",
     *     @OA\RequestBody(
     *         description="Datos del elemento a crear",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Antena Principal"),
     *             @OA\Property(property="tipo", type="string", example="antenas"),
     *             @OA\Property(property="estado_id", type="integer", example=1),
     *             @OA\Property(property="latitud", type="string", example="10.4806"),
     *             @OA\Property(property="longitud", type="string", example="-66.9036"),
     *             @OA\Property(property="direccion", type="string", example="Av. Principal, Edificio A"),
     *             @OA\Property(property="actividad", type="string", example="Operativa"),
     *             @OA\Property(property="tecnologia", type="string", example="4G LTE"),
     *             @OA\Property(property="cantidad", type="integer", example=1),
     *             @OA\Property(property="segmentacion", type="string", example="Urbano"),
     *             @OA\Property(property="codigo_dealer", type="string", example="DL-001"),
     *             @OA\Property(property="clasificacion", type="string", example="VIP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Elemento creado exitosamente"
     *     ),
     *     @OA\Response(response=400, description="Datos inválidos o faltantes")
     * )
     */
    public function createElemento(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Cuerpo de petición inválido.'], 400);
        }

        try {
            $elemento = $this->elementoService->createElemento($data);
            return $this->json([
                'status' => 'success',
                'message' => 'Elemento creado exitosamente.',
                'elemento' => [
                    'id' => $elemento->getId(),
                    'nombre' => $elemento->getNombre(),
                    'tipo' => $elemento->getTipo(),
                    'estado_nombre' => $elemento->getEstado()->getNombreEstado()
                ]
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/actualizar/{id}", name="elemento_update", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualiza un elemento existente",
     *     @OA\RequestBody(
     *         description="Datos del elemento a actualizar",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Antena Editada"),
     *             @OA\Property(property="tipo", type="string", example="antenas"),
     *             @OA\Property(property="estado_id", type="integer", example=1),
     *             @OA\Property(property="latitud", type="string", example="10.4806"),
     *             @OA\Property(property="longitud", type="string", example="-66.9036"),
     *             @OA\Property(property="direccion", type="string", example="Av. Principal, Edificio A"),
     *             @OA\Property(property="actividad", type="string", example="Operativa"),
     *             @OA\Property(property="tecnologia", type="string", example="4G LTE"),
     *             @OA\Property(property="cantidad", type="integer", example=1),
     *             @OA\Property(property="segmentacion", type="string", example="Urbano"),
     *             @OA\Property(property="codigo_dealer", type="string", example="DL-001"),
     *             @OA\Property(property="clasificacion", type="string", example="VIP")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Elemento actualizado correctamente"),
     *     @OA\Response(response=400, description="Datos inválidos o faltantes"),
     *     @OA\Response(response=404, description="Elemento no encontrado")
     * )
     */
    public function updateElemento(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Cuerpo de petición inválido.'], 400);
        }

        try {
            $this->elementoService->updateElemento($id, $data);
            return $this->json([
                'status' => 'success',
                'message' => 'Elemento actualizado correctamente.'
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * @Route("/desactivar/{id}", name="elemento_deactivate", methods={"PUT"})
     */
    public function deactivateElemento(int $id): JsonResponse
    {
        try {
            $this->elementoService->deactivateElemento($id);
            return $this->json([
                'status' => 'success',
                'message' => 'Elemento desactivado correctamente (borrado lógico).'
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * @Route("/resumen", name="elemento_resumen", methods={"GET"})
     */
    public function resumenPorEstado(): JsonResponse
    {
        $resumen = $this->elementoService->getResumenPorEstado();
        return $this->json([
            'status' => 'success',
            'data' => $resumen
        ]);
    }
}
