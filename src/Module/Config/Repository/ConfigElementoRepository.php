<?php

namespace App\Module\Config\Repository;

use App\Module\Config\Entity\ConfigElemento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConfigElemento>
 *
 * @method ConfigElemento|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigElemento|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigElemento[]    findAll()
 * @method ConfigElemento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigElementoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfigElemento::class);
    }

    /**
     * @return ConfigElemento[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['orden' => 'ASC', 'id' => 'ASC']);
    }
}
