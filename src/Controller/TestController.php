<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'test')]

    /**
     * le 1er paramètre est le nom de la route
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TestController.php',
        ]);
    }

    /**
     * @Route("/test/nouvelle_route", name="nouveau_test" )
     * 
     * /!\  il ne faut pas avoir de routes avec le même name
     */

    #[Route('/test/nouvelle_route', name: 'nouveau_test')]
    public function nouvelle_route(): Response
    {
        /*
            La méthode 'render' permet de générer l'affivhage d'un fichier vue qui se trouve dans le dossier 'template'
                Le 1er paramètre est le nom du fichier
                Le 2ème paramètre n'est pas obligatoire. Il doit être de type array et contiendra toute les variable que l'on veut transmettre à la vue
        
        */
        return $this->render("base.html.twig", ["prenom" => "Mounif", "nom" => "NOM"]);
    }

    #[Route('/test/tableau', name: 'test_tableau')]
    public function tableau(): Response
    {
        $tableau = ["un", 2, true];
        $tableau2 = ["nom" => "Cerion", "prenom" => "jean", "age" => 30];

        // Je veux transmettre la valeur de la variable $tableau2 à ma vue dans une variable nommée "personne"
        // Ensuite afficher, "Je m'appelle " suivis du prénom, nom et age
        return $this->render("test/tableau.html.twig", ["tableau" => $tableau, "personne" => $tableau2]);
    }

    #[Route('/test/objet')]
    public function objet(): Response
    {
        $objet = new \stdClass();
        $objet->nom = "Mentor";
        $objet->prenom = "Gérard";
        $objet->age = "54";

        return $this->render("test/tableau.base.twig", ["personne"=> $objet]);
        
    }


    /**
     * @Route("/test/salut/{prenom}")
     * 
     * Dans le chemin, les {} signifient que cette partie du chemin est variable.
     * Ca peut être n'importe quel chaîne de caractères. Le nom mis entre {} est le nom de la variable pass" en paramètre
     */
    public function prenom($prenom)
    {
        return $this->render("base.html.twig", ["prenom" => $prenom]);
    } 

    /*
        EXO : vous allez ajouter une route, "/test/liste/{nombre}"
            Le nombre passé en paramètre devra être envoyé à une vue qui étend base.html.twig.
            Cette vue va afficher la liste des nombres de 1 jusqu'au nombre dans le chemin dans une table HTML
            - Dans la 1er colonne, le nombre.
            - Dans la 2ème colonne, le nombre multiplié par 2.
    */

    /**
     * @Route("/test/liste/{nombre}")
     */
    public function nombre($nombre)
    {
        return $this->render("test/liste.html.twig", ["nombre" => $nombre]);
    } 
}
