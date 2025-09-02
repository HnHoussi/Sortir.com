<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Sortie;
use DateTimeImmutable;
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

        // Exclusion des sorties archivées et des sorties créées (non publiées)
        $qb->andWhere('st.status_label != :archived_status AND st.status_label != :created_status')
            ->setParameter('archived_status', 'Archivée')
            ->setParameter('created_status', 'Créée');

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


        // **Filtre par statut**
        if (!empty($filters['status'])) {
            $qb->andWhere('st.id = :statusId')
                ->setParameter('statusId', $filters['status']->getId());
        }

        if (!empty($filters['organizer'])) {
            $qb->andWhere('s.organizer = :user')
                ->setParameter('user', $user);
        }

        if (!empty($filters['user'])) {
            $qb->andWhere(':user MEMBER OF s.users')
                ->setParameter('user', $user);
        }

        // **Filtre pour 'not_user'**
        if (!empty($filters['not_user'])) {
            $qb->andWhere(':user NOT MEMBER OF s.users')
                ->setParameter('user', $user);
        }

        if (!empty($filters['place'])) {
            $qb->andWhere('p.id = :place_id')
                ->setParameter('place_id', $filters['place']->getId());
        }

        if (!empty($filters['past'])) {
            $qb->andWhere('s.startDatetime < :now')
                ->setParameter('now', new \DateTime());
        }

           return $qb->orderBy('s.startDatetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //Archivage des sorties
    public function findOldSortiesForArchiving(\DateTime $dateLimit): array
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
