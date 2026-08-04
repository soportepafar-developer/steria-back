<?php

namespace App\Module\Elemento\Repository;

use App\Module\Elemento\Entity\Elemento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Elemento>
 *
 * @method Elemento|null find($id, $lockMode = null, $lockVersion = null)
 * @method Elemento|null findOneBy(array $criteria, array $orderBy = null)
 * @method Elemento[]    findAll()
 * @method Elemento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElementoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Elemento::class);
    }

    /**
     * Busca elementos que se encuentren dentro del MultiPolygon WKT.
     * Utiliza consulta nativa para aprovechar ST_Contains de MySQL.
     */
    public function findByPolygons(string $wktPolygons): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                e.id, e.nombre, e.latitud, e.longitud, e.tipo, e.cantidad, e.tecnologia, e.actividad, e.codigo_dealer, e.clasificacion, e.segmentacion,
                es.nombre as estado, p.nombre as parroquia
            FROM elementos e
            LEFT JOIN estado es ON e.estado_id = es.id
            LEFT JOIN parroquias p ON e.parroquia_id = p.id
            WHERE e.activo = 1 
            AND e.geom IS NOT NULL
            AND ST_Contains(ST_GeomFromText(:wkt), e.geom)
        ";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['wkt' => $wktPolygons]);
        
        return $resultSet->fetchAllAssociative();
    }
}
