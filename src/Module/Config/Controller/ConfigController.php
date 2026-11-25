<?php

namespace App\Module\Config\Controller;

use App\Module\Config\Service\ConfigService;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/config")
 * @OA\Tag(name="Configuración")
 */
class ConfigController extends AbstractController
{
    private $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * @Route("", name="config_get", methods={"GET"})
     * @OA\Get(
     *     summary="Obtiene la configuración actual del sistema (esquemas regionales y elementos visibles)",
     *     @OA\Response(
     *         response=200,
     *         description="Configuración del sistema"
     *     )
     * )
     */
    public function getConfig(): JsonResponse
    {
        try {
            return $this->json($this->configService->getConfig());
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error al obtener configuración: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("", name="config_update", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualiza la configuración del sistema",
     *     @OA\RequestBody(
     *         description="Objeto SistemaConfig con esquemaActivoId, esquemas y elementos",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="esquemaActivoId", type="string", example="estandar"),
     *             @OA\Property(property="esquemas", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="elementos", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Configuración guardada exitosamente"),
     *     @OA\Response(response=400, description="Datos inválidos"),
     *     @OA\Response(response=403, description="Permisos insuficientes")
     * )
     */
    public function updateConfig(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_SUPER_ADMINISTRADOR') && !$this->isGranted('ROLE_ADMINISTRADOR')) {
            return $this->json(['error' => 'No tienes permisos para modificar la configuración.'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Cuerpo de petición inválido.'], 400);
        }

        try {
            $config = $this->configService->saveConfig($data);
            return $this->json([
                'status' => 'success',
                'message' => 'Configuración guardada y aplicada.',
                'config' => $config,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error al guardar configuración: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/restaurar", name="config_restore", methods={"POST"})
     * @OA\Post(
     *     summary="Restaura la configuración del sistema a los valores predeterminados",
     *     @OA\Response(response=200, description="Configuración restaurada"),
     *     @OA\Response(response=403, description="Permisos insuficientes")
     * )
     */
    public function restoreConfig(): JsonResponse
    {
        if (!$this->isGranted('ROLE_SUPER_ADMINISTRADOR') && !$this->isGranted('ROLE_ADMINISTRADOR')) {
            return $this->json(['error' => 'No tienes permisos para restaurar la configuración.'], 403);
        }

        try {
            $config = $this->configService->restaurarPredeterminados();
            return $this->json([
                'status' => 'success',
                'message' => 'Configuración restaurada a los valores predeterminados.',
                'config' => $config,
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error al restaurar configuración: ' . $e->getMessage()], 500);
        }
    }
}
