<?php

namespace App\Module\Project\Controller;

use App\Module\Project\Repository\ProjectRepository;
use App\Module\Project\Repository\ProjectFigureRepository;
use App\Module\Project\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/project")
 * @OA\Tag(name="Projects")
 */
class ProjectController extends AbstractController
{
    private $projectRepository;
    private $projectFigureRepository;
    private $projectService;

    public function __construct(
        ProjectRepository $projectRepository,
        ProjectFigureRepository $projectFigureRepository,
        ProjectService $projectService
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectFigureRepository = $projectFigureRepository;
        $this->projectService = $projectService;
    }

    /**
     * @Route("", name="project_list", methods={"GET"})
     */
    public function listProjects(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Usuario no autenticado.'], 401);
        }

        $projects = $this->projectRepository->findBy(['usuarioId' => $user->getId()]);
        $data = [];
        foreach ($projects as $p) {
            $data[] = [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                'descripcion' => $p->getDescripcion()
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("", name="project_create", methods={"POST"})
     */
    public function createProject(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Usuario no autenticado.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Cuerpo de petición inválido.'], 400);
        }

        try {
            $project = $this->projectService->createProject($data, $user->getId());
            return $this->json([
                'status' => 'success',
                'message' => 'Proyecto creado exitosamente.',
                'project' => [
                    'id' => $project->getId(),
                    'nombre' => $project->getNombre(),
                    'descripcion' => $project->getDescripcion()
                ]
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/{id}", name="project_delete", methods={"DELETE"})
     */
    public function deleteProject(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Usuario no autenticado.'], 401);
        }

        try {
            $this->projectService->deleteProject($id, $user->getId());
            return $this->json([
                'status' => 'success',
                'message' => 'Proyecto eliminado exitosamente.'
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/{id}/figuras", name="project_figures_list", methods={"GET"})
     */
    public function listFigures(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Usuario no autenticado.'], 401);
        }

        // Verificar que el proyecto pertenece al usuario
        $project = $this->projectRepository->findOneBy(['id' => $id, 'usuarioId' => $user->getId()]);
        if (!$project) {
            return $this->json(['error' => 'Proyecto no encontrado o no tiene permisos.'], 404);
        }

        $figures = $this->projectFigureRepository->findBy(['projectId' => $id]);
        $data = [];
        foreach ($figures as $f) {
            $data[] = [
                'id' => $f->getId(),
                'proyecto_id' => $f->getProjectId(),
                'nombre' => $f->getNombre(),
                'tipo' => $f->getTipo(),
                'coordenadas' => json_decode($f->getCoordenadas(), true) ?? $f->getCoordenadas(),
                'radio' => $f->getRadio(),
                'color' => $f->getColor()
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/figuras", name="project_figure_create", methods={"POST"})
     */
    public function createFigure(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Usuario no autenticado.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Cuerpo de petición inválido.'], 400);
        }

        try {
            $figure = $this->projectService->createFigure($data, $id, $user->getId());
            return $this->json([
                'status' => 'success',
                'message' => 'Figura guardada exitosamente.',
                'figura' => [
                    'id' => $figure->getId(),
                    'proyecto_id' => $figure->getProjectId(),
                    'nombre' => $figure->getNombre(),
                    'tipo' => $figure->getTipo(),
                    'coordenadas' => json_decode($figure->getCoordenadas(), true) ?? $figure->getCoordenadas(),
                    'radio' => $figure->getRadio(),
                    'color' => $figure->getColor()
                ]
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/figuras/{id}", name="project_figure_delete", methods={"DELETE"})
     */
    public function deleteFigure(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Usuario no autenticado.'], 401);
        }

        try {
            $this->projectService->deleteFigure($id, $user->getId());
            return $this->json([
                'status' => 'success',
                'message' => 'Figura eliminada exitosamente.'
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
