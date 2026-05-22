<?php

namespace App\Module\Import\Controller;

use App\Module\Import\Service\ExcelImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/import")
 * @OA\Tag(name="Importación")
 */
class ImportController extends AbstractController
{
    private $excelImportService;

    public function __construct(ExcelImportService $excelImportService)
    {
        $this->excelImportService = $excelImportService;
    }

    /**
     * @Route("/masivo", name="import_masivo", methods={"POST"})
     * @OA\Post(
     *     summary="Importa elementos masivamente desde un archivo Excel",
     *     @OA\RequestBody(
     *         description="Archivo Excel (.xlsx) con los datos a importar",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="El archivo Excel a subir"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Importación completada con éxito"),
     *     @OA\Response(response=400, description="Archivo no proporcionado o formato inválido"),
     *     @OA\Response(response=500, description="Error interno procesando el archivo")
     * )
     */
    public function importMasivo(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        $filePath = null;

        if ($uploadedFile) {
            $filePath = $uploadedFile->getRealPath();
        } else {
            $rootPath = $this->getParameter('kernel.project_dir');
            $localPath = $rootPath . '/data_recibida.xlsx';
            if (file_exists($localPath)) {
                $filePath = $localPath;
            }
        }

        if (!$filePath) {
            return $this->json([
                'error' => 'No se cargó ningún archivo. Sube un archivo con la clave "file" o coloca un archivo "data_recibida.xlsx" en la raíz del proyecto backend.'
            ], 400);
        }

        try {
            $result = $this->excelImportService->import($filePath);
            
            return $this->json([
                'status' => 'success',
                'message' => 'Importación masiva completada con éxito.',
                'total_importados' => $result['total_importados'],
                'detalles_omisiones' => $result['detalles_omisiones']
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Ocurrió un error al procesar el archivo Excel: ' . $e->getMessage()], 500);
        }
    }
}
