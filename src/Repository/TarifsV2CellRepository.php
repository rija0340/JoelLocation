<?php

namespace App\Repository;

use App\Entity\TarifsV2Cell;
use App\Entity\Marque;
use App\Entity\Modele;
use App\Entity\PricingInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TarifsV2Cell|null find($id, $lockMode = null, $lockVersion = null)
 * @method TarifsV2Cell|null findOneBy(array $criteria, array $orderBy = null)
 * @method TarifsV2Cell[]    findAll()
 * @method TarifsV2Cell[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TarifsV2CellRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TarifsV2Cell::class);
    }

    /**
     * Find all cells for a specific vehicle
     * @return TarifsV2Cell[]
     */
    public function findByVehicle(Marque $marque, Modele $modele): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.marque = :marque')
            ->andWhere('c.modele = :modele')
            ->setParameter('marque', $marque)
            ->setParameter('modele', $modele)
            ->orderBy('c.month', 'ASC')
            ->addOrderBy('c.pricingInterval', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find cell by vehicle, month and interval
     */
    public function findOneByVehicleMonthInterval(
        Marque $marque, 
        Modele $modele, 
        string $month, 
        PricingInterval $interval
    ): ?TarifsV2Cell {
        return $this->createQueryBuilder('c')
            ->where('c.marque = :marque')
            ->andWhere('c.modele = :modele')
            ->andWhere('c.month = :month')
            ->andWhere('c.pricingInterval = :interval')
            ->setParameter('marque', $marque)
            ->setParameter('modele', $modele)
            ->setParameter('month', $month)
            ->setParameter('interval', $interval)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all cells for comparison view
     * @return TarifsV2Cell[]
     */
    public function findForComparison(string $month, PricingInterval $interval): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.month = :month')
            ->andWhere('c.pricingInterval = :interval')
            ->setParameter('month', $month)
            ->setParameter('interval', $interval)
            ->orderBy('c.marque', 'ASC')
            ->addOrderBy('c.modele', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all cells grouped by vehicle
     */
    public function findAllGroupedByVehicle(): array
    {
        $cells = $this->findAll();
        $grouped = [];
        
        foreach ($cells as $cell) {
            $key = $cell->getMarque()->getId() . '-' . $cell->getModele()->getId();
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'marque' => $cell->getMarque(),
                    'modele' => $cell->getModele(),
                    'cells' => []
                ];
            }
            $grouped[$key]['cells'][] = $cell;
        }
        
        return $grouped;
    }

    /**
     * Check if cell exists
     */
    public function cellExists(
        Marque $marque, 
        Modele $modele, 
        string $month, 
        PricingInterval $interval
    ): bool {
        $count = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.marque = :marque')
            ->andWhere('c.modele = :modele')
            ->andWhere('c.month = :month')
            ->andWhere('c.pricingInterval = :interval')
            ->setParameter('marque', $marque)
            ->setParameter('modele', $modele)
            ->setParameter('month', $month)
            ->setParameter('interval', $interval)
            ->getQuery()
            ->getSingleScalarResult();
        
        return $count > 0;
    }

    /**
     * Delete all cells for a vehicle
     */
    public function deleteByVehicle(Marque $marque, Modele $modele): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->where('c.marque = :marque')
            ->andWhere('c.modele = :modele')
            ->setParameter('marque', $marque)
            ->setParameter('modele', $modele)
            ->getQuery()
            ->execute();
    }
}
