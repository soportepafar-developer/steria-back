<?php

namespace App\Module\Historial\Repository;

use App\Module\Historial\Entity\Historial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Historial>
 *
 * @method Historial|null find($id, $lockMode = null, $lockVersion = null)
 * @method Historial|null findOneBy(array $criteria, array $orderBy = null)
 * @method Historial[]    findAll()
 * @method Historial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistorialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Historial::class);
    }

    public function findFilteredPaginated(
        ?string $accion = null,
        ?string $tipo = null,
        ?string $search = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $page = 1,
        int $limit = 10
    ): array {
        $qb = $this->createQueryBuilder('h')
            ->select('h', 'e.tipo AS elementoTipo')
            ->leftJoin('h.usuario', 'u')
            ->leftJoin('App\Module\Elemento\Entity\Elemento', 'e', 'WITH', 'h.elementoId = e.id')
            ->orderBy('h.createdAt', 'DESC');

        if ($accion) {
            $accionMap = [
                'editar' => 'actualizar',
                'actualizar' => 'actualizar',
                'crear' => 'crear',
                'eliminar' => 'desactivar',
                'desactivar' => 'desactivar',
                'borrar' => 'desactivar'
            ];
            $normalizedAccion = $accionMap[strtolower(trim($accion))] ?? strtolower(trim($accion));

            $qb->andWhere('h.accion = :accion')
               ->setParameter('accion', $normalizedAccion);
        }

        if ($tipo) {
            $tipoMap = [
                'antena' => 'antenas',
                'agente' => 'agentes',
                'oficina' => 'oficinas',
                'abonado' => 'abonados'
            ];
            $normalizedTipo = $tipoMap[strtolower(trim($tipo))] ?? strtolower(trim($tipo));

            $qb->andWhere('e.tipo = :tipo')
               ->setParameter('tipo', $normalizedTipo);
        }

        if ($search) {
            $qb->andWhere('h.identificadorElemento LIKE :search OR u.primerNombre LIKE :search OR u.primerApellido LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($fechaInicio) {
            $qb->andWhere('h.createdAt >= :fechaInicio')
               ->setParameter('fechaInicio', new \DateTime($fechaInicio . ' 00:00:00'));
        }

        if ($fechaFin) {
            $qb->andWhere('h.createdAt <= :fechaFin')
               ->setParameter('fechaFin', new \DateTime($fechaFin . ' 23:59:59'));
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        $paginator = new Paginator($qb, true);
        $totalItems = count($paginator);

        $data = [];
        foreach ($paginator as $item) {
            $data[] = [
                'historial' => $item[0],
                'elementoTipo' => $item['elementoTipo'] ?? null
            ];
        }

        return [
            'data' => $data,
            'total' => $totalItems,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($totalItems / $limit)
        ];
    }
}
