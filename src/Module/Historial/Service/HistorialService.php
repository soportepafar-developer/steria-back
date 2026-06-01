<?php

namespace App\Module\Historial\Service;

use App\Module\Historial\Entity\Historial;
use App\Module\User\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class HistorialService
{
    private $em;
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public function registrar(
        string $accion,
        string $tablaAfectada,
        int $elementoId,
        string $identificadorElemento,
        ?array $detalles = null
    ): Historial {
        $historial = new Historial();

        $user = $this->security->getUser();
        if ($user instanceof Usuario) {
            $historial->setUsuario($user);
        }

        $historial->setAccion($accion);
        $historial->setTablaAfectada($tablaAfectada);
        $historial->setElementoId($elementoId);
        $historial->setIdentificadorElemento($identificadorElemento);
        $historial->setDetalles($detalles);

        $this->em->persist($historial);
        $this->em->flush();

        return $historial;
    }

    /**
     * @return Historial[]
     */
    public function getListado(): array
    {
        return $this->em->getRepository(Historial::class)->findBy([], ['createdAt' => 'DESC']);
    }

    public function getListadoPaginado(
        ?string $accion = null,
        ?string $tipo = null,
        ?string $search = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $page = 1,
        int $limit = 10
    ): array {
        return $this->em->getRepository(Historial::class)->findFilteredPaginated(
            $accion,
            $tipo,
            $search,
            $fechaInicio,
            $fechaFin,
            $page,
            $limit
        );
    }
}
