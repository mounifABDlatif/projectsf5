<?php

namespace App\Repository;

use App\Entity\Emprunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Emprunt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Emprunt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Emprunt[]    findAll()
 * @method Emprunt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }

    /*
        SELECT * 
        FROM `emprunt` e JOIN `livre` l ON e.livre_id = l.id_livre
        WHERE date_retour IS NULL
    */

    /**
     * @return Emprunt[] Returns an array of Emprunt objects
     */
    
    public function empruntsNonRendus()
    {
        return $this->createQueryBuilder('e')
            ->where('e.date_retour IS NULL')
            ->orderBy('e.date_emprunt', 'DESC')
            ->getQuery()
            ->getResult()

            // ->getQuery()
            // ->getResult() ces 2 requetes sont à garder car elles éxécutent la req
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Emprunt
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
