<?php

namespace App\Module\Config\Repository;

use App\Module\Config\Entity\EsquemaRegional;
use App\Module\Config\Entity\EsquemaRegionEstado;
use App\Module\Geo\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EsquemaRegionEstado>
 *
 * @method EsquemaRegionEstado|null find($id, $lockMode = null, $lockVersion = null)
 * @method EsquemaRegionEstado|null findOneBy(array $criteria, array $orderBy = null)
 * @method EsquemaRegionEstado[]    findAll()
 * @method EsquemaRegionEstado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EsquemaRegionEstadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EsquemaRegionEstado::class);
    }

    /**
     * Retorna un mapa [estado_id => Region] para el esquema indicado.
     *
     * @return array<int, Region>
     */
    public function findEstadoRegionMapByEsquema(EsquemaRegional $esquema): array
    {
        $rows = $this->createQueryBuilder('ere')
            ->select('ere', 'r', 'e')
            ->innerJoin('ere.region', 'r')
            ->innerJoin('ere.estado', 'e')
            ->where('ere.esquema = :esquema')
            ->setParameter('esquema', $esquema)
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $estado = $row->getEstado();
            $region = $row->getRegion();
            if ($estado && $region) {
                $map[$estado->getId()] = $region;
            }
        }

        return $map;
    }
}
