<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Sortie;
use DateTime;
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
            ->leftJoin('s.organizer', 'o')
            ->leftJoin('s.status', 'st')
            ->addSelect('p', 'c', 'o', 'st');

        // Exclusion des sorties archivées
        $qb->andWhere('st.status_label != :archived_status')
            ->setParameter('archived_status', 'Archivée');

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

        if (!empty($filters['statut'])) {
            $qb->andWhere('st.id = :status_id')
                ->setParameter('status_id', $filters['statut']->getId());
        }

        if (!empty($filters['organizer'])) {
            $qb->andWhere('s.organizer = :user')
                ->setParameter('user', $user);
        }

        if (!empty($filters['user'])) {
            $qb->andWhere(':user MEMBER OF s.users')
                ->setParameter('user', $user);
        }

        if (!empty($filters['place'])) {
            $qb->andWhere('p.id = :place_id')
                ->setParameter('place_id', $filters['place']->getId());
        }

        return $qb->orderBy('s.startDatetime', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function findOldSortiesForArchiving(DateTime $dateLimit): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.status', 'st')
            ->andWhere('st.status_label IN (:statusCodes)')
            ->andWhere('s.startDatetime < :dateLimit')
            ->setParameter('statusCodes', ['Terminée', 'Annulée'])
            ->setParameter('dateLimit', $dateLimit)
            ->getQuery()
            ->getResult();
    }

}
