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
            ->addSelect('p', 'c', 'o', 'st');

        // Exclusion des sorties archivées de l'affichage
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
                ->setParameter('campus', $filters['campus']); // PAS besoin de getId()
        }


        // **Filtre par statut**
        if (!empty($filters['status'])) {
            $qb->andWhere('st.id = :statusId')
                ->setParameter('statusId', $filters['status']->getId());
        }

        if (!empty($filters['organisator'])) {
            $qb->andWhere('s.organisator = :user')
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
            $qb->andWhere('s.start_datetime < :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->orderBy('s.start_datetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
