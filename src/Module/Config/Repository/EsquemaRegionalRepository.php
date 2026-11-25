<?php

namespace App\Module\Config\Repository;

use App\Module\Config\Entity\EsquemaRegional;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EsquemaRegional>
 *
 * @method EsquemaRegional|null find($id, $lockMode = null, $lockVersion = null)
 * @method EsquemaRegional|null findOneBy(array $criteria, array $orderBy = null)
 * @method EsquemaRegional[]    findAll()
 * @method EsquemaRegional[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EsquemaRegionalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EsquemaRegional::class);
    }

    public function findActivo(): ?EsquemaRegional
    {
        $activo = $this->findOneBy(['esActivo' => true]);
        if ($activo) {
            return $activo;
        }

        return $this->findOneBy([], ['id' => 'ASC']);
    }
}
