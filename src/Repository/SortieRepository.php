<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    public function findFilteredFromForm(array $filters, User $user): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.place', 'p')
            ->leftJoin('p.city', 'c')
            ->leftJoin('s.organisator', 'o')
            ->leftJoin('s.status', 'st')
//            ->leftJoin('s.inscriptions', 'i')
//            ->leftJoin('i.user', 'ip')
            ->addSelect('p', 'c', 'o', 'st');

        if (!empty($filters['name'])) {
            $qb->andWhere('s.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['city'])) {
            $qb->andWhere('c.id = :city')
                ->setParameter('city', $filters['city']->getId());
        }

        if (!empty($filters['campus'])) {
            $qb->andWhere('o.campus = :campus')
                ->setParameter('campus', $filters['campus']->getId());
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('st.id = :status')
                ->setParameter('status', $filters['status']->getId());
        }

        if (!empty($filters['organisator'])) {
            $qb->andWhere('s.organisator = :user')
                ->setParameter('user', $user);
        }

        if (!empty($filters['user'])) {
            $qb->andWhere(':user MEMBER OF s.users')
                ->setParameter('user', $user);
        }

        return $qb->orderBy('s.start_datetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
