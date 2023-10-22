<?php

namespace App\Repository\Fight;

use App\Entity\Fight\FighterFight;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FighterFight>
 *
 * @method FighterFight|null find($id, $lockMode = null, $lockVersion = null)
 * @method FighterFight|null findOneBy(array $criteria, array $orderBy = null)
 * @method FighterFight[]    findAll()
 * @method FighterFight[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FighterFightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FighterFight::class);
    }

    //    /**
    //     * @return FighterFight[] Returns an array of FighterFight objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FighterFight
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
