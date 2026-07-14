<?php

namespace App\Module\Project\Service;

use App\Module\Project\Entity\Project;
use App\Module\Project\Entity\ProjectFigure;
use App\Module\Project\Repository\ProjectRepository;
use App\Module\Project\Repository\ProjectFigureRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService
{
    private $em;
    private $projectRepository;
    private $projectFigureRepository;

    public function __construct(
        EntityManagerInterface $em,
        ProjectRepository $projectRepository,
        ProjectFigureRepository $projectFigureRepository
    ) {
        $this->em = $em;
        $this->projectRepository = $projectRepository;
        $this->projectFigureRepository = $projectFigureRepository;
    }

    public function createProject(array $data, int $usuarioId): Project
    {
        $nombre = $data['nombre'] ?? null;
        $descripcion = $data['descripcion'] ?? null;

        if (!$nombre) {
            throw new \InvalidArgumentException('El nombre del proyecto es obligatorio.');
        }

        $project = new Project();
        $project->setNombre($nombre);
        $project->setDescripcion($descripcion);
        $project->setUsuarioId($usuarioId);

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function deleteProject(int $id, int $usuarioId): void
    {
        $project = $this->projectRepository->findOneBy(['id' => $id, 'usuarioId' => $usuarioId]);
        if (!$project) {
            throw new \InvalidArgumentException('Proyecto no encontrado o no tiene permisos.');
        }

        $this->em->remove($project);
        $this->em->flush();
    }

    public function createFigure(array $data, int $projectId, int $usuarioId): ProjectFigure
    {
        // Verificar que el proyecto existe y pertenece al usuario
        $project = $this->projectRepository->findOneBy(['id' => $projectId, 'usuarioId' => $usuarioId]);
        if (!$project) {
            throw new \InvalidArgumentException('Proyecto no encontrado o no tiene permisos.');
        }

        $nombre = $data['nombre'] ?? null;
        $tipo = $data['tipo'] ?? null;
        $coordenadas = $data['coordenadas'] ?? null;
        $radio = $data['radio'] ?? null;
        $color = $data['color'] ?? '#4f46e5';

        if (!$nombre || !$tipo || !$coordenadas) {
            throw new \InvalidArgumentException('Faltan campos obligatorios: nombre, tipo, coordenadas.');
        }

        // Si coordenadas viene como array, lo guardamos como JSON string
        if (is_array($coordenadas)) {
            $coordenadas = json_encode($coordenadas);
        }

        $figure = new ProjectFigure();
        $figure->setProjectId($projectId);
        $figure->setNombre($nombre);
        $figure->setTipo($tipo);
        $figure->setCoordenadas($coordenadas);
        $figure->setRadio($radio ? (float)$radio : null);
        $figure->setColor($color);

        $this->em->persist($figure);
        $this->em->flush();

        return $figure;
    }

    public function deleteFigure(int $id, int $usuarioId): void
    {
        $figure = $this->projectFigureRepository->find($id);
        if (!$figure) {
            throw new \InvalidArgumentException('Figura no encontrada.');
        }

        // Verificar que pertenece al proyecto del usuario
        $project = $this->projectRepository->findOneBy(['id' => $figure->getProjectId(), 'usuarioId' => $usuarioId]);
        if (!$project) {
            throw new \InvalidArgumentException('No tiene permisos para eliminar esta figura.');
        }

        $this->em->remove($figure);
        $this->em->flush();
    }
}
