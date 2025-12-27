<?php

namespace App\Repository;

use App\Entity\ContractSignature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContractSignature>
 *
 * @method ContractSignature|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContractSignature|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContractSignature[]    findAll()
 * @method ContractSignature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractSignatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractSignature::class);
    }

    public function save(ContractSignature $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContractSignature $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}