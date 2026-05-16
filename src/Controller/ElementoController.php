<?php
namespace App\Controller;

use App\Service\ElementoManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/elementos")
 */
class ElementoController extends AbstractController
{
    private $manager;

    public function __construct(ElementoManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("", methods={"POST"})
     * @OA\Post(
     * path="/api/elementos",
     * summary="Crea un nuevo elemento",
     * @OA\RequestBody(
     * @OA\JsonContent(type="object", @OA\Property(property="tipo", type="string"))
     * ),
     * @OA\Response(response=201, description="Creado")
     * )
     */
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $elemento = $this->manager->createFromData($data);

        return $this->json([
            'status' => 'success',
            'id' => $elemento->getId()
        ], 201);
    }
}
