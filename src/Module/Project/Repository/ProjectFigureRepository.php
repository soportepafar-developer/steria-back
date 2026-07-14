<?php

namespace App\Module\Project\Repository;

use App\Module\Project\Entity\ProjectFigure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectFigure>
 *
 * @method ProjectFigure|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectFigure|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectFigure[]    findAll()
 * @method ProjectFigure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectFigureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectFigure::class);
    }

    public function save(ProjectFigure $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(ProjectFigure $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
