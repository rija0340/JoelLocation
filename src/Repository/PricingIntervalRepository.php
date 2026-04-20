<?php

namespace App\Repository;

use App\Entity\PricingInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PricingInterval|null find($id, $lockMode = null, $lockVersion = null)
 * @method PricingInterval|null findOneBy(array $criteria, array $orderBy = null)
 * @method PricingInterval[]    findAll()
 * @method PricingInterval[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PricingIntervalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PricingInterval::class);
    }

    /**
     * Find all intervals ordered by sort order
     * @return PricingInterval[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('pi')
            ->orderBy('pi.sortOrder', 'ASC')
            ->addOrderBy('pi.minDays', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get next sort order
     */
    public function getNextSortOrder(): int
    {
        $result = $this->createQueryBuilder('pi')
            ->select('MAX(pi.sortOrder)')
            ->getQuery()
            ->getSingleScalarResult();
        
        return $result ? (int) $result + 1 : 1;
    }

    /**
     * Initialize default intervals if none exist
     */
    public function initializeDefaults(): void
    {
        $existing = $this->findAll();
        if (count($existing) > 0) {
            return;
        }

        $defaults = [
            ['min' => 1, 'max' => 2, 'label' => '1-2 jours', 'order' => 1],
            ['min' => 3, 'max' => 6, 'label' => '3-6 jours', 'order' => 2],
            ['min' => 7, 'max' => 14, 'label' => '7-14 jours', 'order' => 3],
            ['min' => 15, 'max' => 30, 'label' => '15-30 jours', 'order' => 4],
            ['min' => 31, 'max' => null, 'label' => '31+ jours', 'order' => 5],
        ];

        foreach ($defaults as $data) {
            $interval = new PricingInterval();
            $interval->setMinDays($data['min']);
            $interval->setMaxDays($data['max']);
            $interval->setLabel($data['label']);
            $interval->setSortOrder($data['order']);
            $this->_em->persist($interval);
        }

        $this->_em->flush();
    }
}
