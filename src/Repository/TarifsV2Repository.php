<?php

namespace App\Repository;

use App\Entity\TarifsV2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TarifsV2|null find($id, $lockMode = null, $lockVersion = null)
 * @method TarifsV2|null findOneBy(array $criteria, array $orderBy = null)
 * @method TarifsV2[]    findAll()
 * @method TarifsV2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TarifsV2Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TarifsV2::class);
    }

    /**
     * Find tarifs for a specific vehicle model and month
     */
    public function findOneByMarqueModeleMois($marque, $modele, $mois): ?TarifsV2
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.marque = :marque')
            ->andWhere('t.modele = :modele')
            ->andWhere('t.mois = :mois')
            ->setParameter('marque', $marque)
            ->setParameter('modele', $modele)
            ->setParameter('mois', $mois)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
