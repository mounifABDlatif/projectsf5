<?php

namespace App\Repository;

use App\Entity\Abonne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method Abonne|null find($id, $lockMode = null, $lockVersion = null)
 * @method Abonne|null findOneBy(array $criteria, array $orderBy = null)
 * @method Abonne[]    findAll()
 * @method Abonne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbonneRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Abonne::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Abonne) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @return Livre[] Returns an array of Livre objects
     */
    
    public function recherche($mot)
    {
        /*
            SELECT * FROM livre WHERE titre LIKE "%xxx%" ORDER BY l.titre, l.auteur
        */
        return $this->createQueryBuilder('a') // le paramèttre 'l' représente la table livre (comme un alias dans une requette sql)
            ->where('a.pseudo LIKE :mot')
            ->orWhere("a.nom LIKE :mot") 
            ->orWhere("a.prenom LIKE :mot") // il faut faire attention à l'enchainement des "or" et des "and" car il y a un odre de priorité : le and est pris en compte avant les "or"
            ->setParameter('mot', "%$mot%") // mot correspond à $mot. On aurait pu garder val et $valeur c'est pareil à condition que la variable se trouve en paramètre de la fonction
            ->orderBy('a.pseudo', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Abonne[] Returns an array of Abonne objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Abonne
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
