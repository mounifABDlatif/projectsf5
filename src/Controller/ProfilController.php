<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Repository\AbonneRepository;
use App\Repository\EmpruntRepository;
use App\Repository\LivreRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// #[IsGranted('ROLE_LECTEUR')]
class ProfilController extends AbstractController
{
    #[Route('/profil/', name: 'profil')]
    public function index(): Response
    {
        /*
            Pour avoir les infos de l'utilisateur connecté :
            dans le twig       : app.user
            dans le controleur : $this->getUser() 
        */
        // $abonneConnecte = $this->getUser();

        return $this->render('profil/monProfil.html.twig');
    }

    #[Route('/profil/emprunter/{id}', name: 'profil_emprunter')]
    public function emprunter(EntityManagerInterface $em, LivreRepository $lr, Livre $livre)
    {

        // Ici on évite le risque qu'un utilisateur éffectue un emprunt via la barre de navigation
        // il est auomatiquement redirigé vers la page d'accueil
        $livresEmpruntes = $lr->livresEmpruntes();
        if ( in_array($livre, $livresEmpruntes) ) {

            $this->addFlash("danger", 'Le livre <strong>' . $livre->getTitre() . '</strong> n\'est pas disponible !');
            return $this->redirectToRoute("accueil");
        }



        // Exo : l'utilisateur emprunte aujourd'hui le livre sur lequel il a cliqué 
        $emprunt = new Emprunt();
        $emprunt->setDateEmprunt(new DateTime()); // new DateTime() créée un objet DateTime avec la date du jour. Pas besoin de mettre DateTime("now")

        $emprunt->setLivre($livre); // $livre a été récupéré de la bdd avec l'id qui est passé dans le chemin
        $emprunt->setAbonne($this->getUser()); // voir le commentair dans la fonction d'au-dessus

        $em->persist($emprunt);   // comme $emprunt est u nouvel emprunt à insérer dans la bdd, il faut utiliser $em->persist
        $em->flush();             // enregistrer en bdd

        return $this->redirectToRoute("profil");
    }
}
