<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

//    /**
//     * @return Ticket[] Returns an array of Ticket objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Ticket
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    /**
     * @param $filters
     * @param $user
     * @param $roles
     * @return Ticket
     */
    public function findByFilters($filters, $user, $roles): array
    {
               $qb = $this->createQueryBuilder('t');

                // Ajouter des filtres statiques
                if (isset($filters['status'])) {
                    $qb->andWhere('t.status = :status')
                        ->setParameter('status', $filters['status']);
                }

                if (isset($filters['start']) && isset($filters['end'])) {
                    $qb->andWhere('t.created_at BETWEEN :start AND :end')
                        ->setParameter('start', $filters['start'])
                        ->setParameter('end', $filters['end']);
                }

                // Gestion des rôles
                if (in_array('ROLE_ADMIN', $roles)) {
                    // Pas de filtre supplémentaire, l'administrateur voit tout
                } elseif (in_array('ROLE_TECH_SUPPORT', $roles)) {
                    $qb->andWhere('t.assigned_to = :user')
                        ->setParameter('user', $user);
                } else {
                    $qb->andWhere('t.created_by = :user')
                        ->setParameter('user', $user);
                }

                $result = $qb->getQuery()->getResult();

        return $result;
    }
}
