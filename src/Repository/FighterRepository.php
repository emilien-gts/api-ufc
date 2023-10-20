<?php

namespace App\Repository;

use App\Entity\Fighter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fighter>
 *
 * @method Fighter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fighter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fighter[]    findAll()
 * @method Fighter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FighterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fighter::class);
    }
}
