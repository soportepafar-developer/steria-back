<?php

namespace App\Module\Historial\Controller;

use App\Module\Historial\Service\HistorialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/historial")
 * @OA\Tag(name="Historial")
 */
class HistorialController extends AbstractController
{
    private $historialService;

    public function __construct(HistorialService $historialService)
    {
        $this->historialService = $historialService;
    }

    /**
     * @Route("/listado", name="historial_list", methods={"GET"})
     * @OA\Get(
     *     summary="Lista el historial de cambios con filtros y paginación",
     *     @OA\Parameter(name="accion", in="query", required=false, description="Filtrar por operación: crear, actualizar, desactivar, etc.", @OA\Schema(type="string")),
     *     @OA\Parameter(name="tipo", in="query", required=false, description="Filtrar por tipo de elemento: antena, agente, oficina, abonado", @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", required=false, description="Buscar por elemento o responsable", @OA\Schema(type="string")),
     *     @OA\Parameter(name="fecha_inicio", in="query", required=false, description="Fecha de inicio (YYYY-MM-DD)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="fecha_fin", in="query", required=false, description="Fecha de fin (YYYY-MM-DD)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", required=false, description="Número de página (default: 1)", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", required=false, description="Registros por página (default: 10)", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Retorna el listado paginado y filtrado del historial",
     *         @OA\JsonContent(
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="page", type="integer"),
     *             @OA\Property(property="limit", type="integer"),
     *             @OA\Property(property="pages", type="integer"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="usuario_id", type="integer", nullable=true),
     *                     @OA\Property(property="usuario_email", type="string", nullable=true),
     *                     @OA\Property(property="usuario_nombre", type="string", nullable=true),
     *                     @OA\Property(property="accion", type="string"),
     *                     @OA\Property(property="tabla_afectada", type="string"),
     *                     @OA\Property(property="elemento_id", type="integer"),
     *                     @OA\Property(property="identificador_elemento", type="string"),
     *                     @OA\Property(property="elemento_tipo", type="string", nullable=true),
     *                     @OA\Property(property="detalles", type="object", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function listHistorial(Request $request): JsonResponse
    {
        $accion = $request->query->get('accion');
        $tipo = $request->query->get('tipo');
        $search = $request->query->get('search');
        $fechaInicio = $request->query->get('fecha_inicio');
        $fechaFin = $request->query->get('fecha_fin');
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $result = $this->historialService->getListadoPaginado(
            $accion,
            $tipo,
            $search,
            $fechaInicio,
            $fechaFin,
            $page,
            $limit
        );

        $formattedData = [];
        foreach ($result['data'] as $item) {
            $h = $item['historial'];
            $usuario = $h->getUsuario();
            $formattedData[] = [
                'id' => $h->getId(),
                'usuario_id' => $usuario ? $usuario->getId() : null,
                'usuario_email' => $usuario ? $usuario->getEmail() : null,
                'usuario_nombre' => $usuario ? ($usuario->getPrimerNombre() . ' ' . $usuario->getPrimerApellido()) : null,
                'accion' => $h->getAccion(),
                'tabla_afectada' => $h->getTablaAfectada(),
                'elemento_id' => $h->getElementoId(),
                'identificador_elemento' => $h->getIdentificadorElemento(),
                'elemento_tipo' => $item['elementoTipo'],
                'detalles' => $h->getDetalles(),
                'created_at' => $h->getCreatedAt() ? $h->getCreatedAt()->format('Y-m-d H:i:s') : null
            ];
        }

        return $this->json([
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'pages' => $result['pages'],
            'data' => $formattedData
        ]);
    }
}
