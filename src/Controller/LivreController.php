<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\AbonneRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Repository\LivreRepository;

#[Route('/admin')]
class LivreController extends AbstractController
{
    #[Route('/livre', name: 'livre')]
    public function index(LivreRepository $lr): Response
    {

        return $this->render('livre/index.html.twig', [
            'livres' => $lr->findAll(), // findAll() retourne la liste de tout les livres
            'livres_empruntes' => $lr->livresEmpruntes()
        ]);
    }

    #[Route('/livre/mes-livres', name: 'livre_mes_livres')]
    public function mesLivres(): Response
    {
        $livres = [
            ["titre" => "Dune", "auteur" => "Frank Herbert"],
            ["titre" => "1984", "auteur" => "George Orwell"],
            ["titre" => "Le Seigneur des Anneaux", "auteur" => "J.R.R. Tolkien"]
        ];

        return $this->render('livre/mesLivres.html.twig', [
            'livres' => $livres,
        ]);
    }


    #[Route('/livre/ajouter_livre', name: 'livre_ajouter')]
    /**
     * Pour instancier un objet de la classe Request, on va utiliser l'injection de dépendance.
     * On définit un paramètre dans ube méthode d'un controleur de la clasee Request et dans cette méthode, on pourra utiliser l'objet, qui contiendra des propriétés avec toutes les valeurs des superglobales de PHP
     * ex: 
     * $request->query      : cette propriété est l'objet qui a les valeurs de $_GET
     * $request->request    : propriété qui a les valeurs de $_POST
     */
    public function ajouter(Request $request, EntityManager $em, CategorieRepository $cr)
    {
        // Exo : La route doit afficher un formulaire pour pouvoir ajouter un livre
        //       Ajouter un  lien dans le menu pour accéder à cette route

        if ($request->isMethod("POST")) {
            $titre = $request->request->get("titre"); // la méthode 'get()' permet de récupérer les valeurs des inputs du formulaire
            $auteur = $request->request->get("auteur"); // le paramètre passer à get() est le name de l'input

            $categorie_id = $request->request->get("categorie"); // le paramètre passer à get() est le name du select

            if ($titre && $auteur) { // si $titre et $auteur ne sont pas vide
                $nouveauLivre = new Livre;
                $nouveauLivre->setTitre($titre);
                $nouveauLivre->setAuteur($auteur);
                $nouveauLivre->setCategorie($cr->find($categorie_id)); // contrairement au commande précèdente, ici on intéroge la bdd : c'est pour ça quand utilise $cr 

                /*
                    On va utiliser l'obje $em de la classe EntityManager pour enregistrer en BDD. 
                    La méthode 'persist' permet de préparer une requet INSERT INTO.
                    Le paramètre DOIT ÊTRE un ojet d'une classe Entity.
                */

                $em->persist($nouveauLivre);
                /*
                    La méthode 'flush' exécute toutes les requetes en attente. La BDD est modifiée quand la méthode est lancée (et pas avant)
                */
                $em->flush();
                return $this->redirectToRoute("livre"); // Redirection vers la liste des livres
            }
        }

        return $this->render('livre/ajouterlivre.html.twig', ["categories" => $cr->findAll()]);
    }


    #[Route('/livre/modifier/{id}', name: 'livre_modifier')]
    public function modifier(EntityManager $em, Request $request, LivreRepository $lr, $id)
    {
        $livre = $lr->find($id); // find() retourne l'objet Livre dont l'id vaut $id en BDD

        /*
            'createForm' va créer un objet représentant le formulaire créé à partir de la classe LiverType
            Le 2ème paramètre est un objet Entity qui sera lié au formulaire
        */
        $form = $this->createForm(LivreType::class, $livre);

        

        /*
            La méthode 'handleRequest' permet à $form de gérer les informations venant de la requête HTTP
            ex : est-ce que le formulaire a été soumis ?...
         */
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { // si le formulaire a été soumis et s'il est valide

            /*
                Tous les objets Entity qui ont été instancié à partir de la BDD vont être enrégistrées en bdd quand on va utiliser $em->flush()
            */
            
            if( $fichier = $form->get("couverture")->getData() ) { // getData permet de récupérer les données du fichier
            
                // on récupère le nom du fichier qui a été téléversé
                $nomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                // on remplace les espaces par des _
                $nomFichier = str_replace(" ", "_", $nomFichier);

                // on ajoute un string unique au nom du fichier pour éviter les doublons
                $nomFichier .= uniqid() . "." . $fichier->guessExtension();

                // on copie le fichier uploadé dans un dossier du du dossier 'public' avec le nouveau nom de fichier
                $fichier->move($this->getParameter("dossier_images"), $nomFichier);

                // on modifie l'entité livre
                $livre->setCouverture($nomFichier);
        
            }
            $em->flush();
            return $this->redirectToRoute("livre");
        }

        return $this->render("livre/form.html.twig", ["formLivre" => $form->createView()]);
    }

    #[Route('/livre/supprimer/{id}', name: 'livre_supprimer')]
    public function supprimer(Request $request, EntityManager $em, Livre $livre)
    {
        /*
            Si le paramètre dans le chemin est une propriété d'une classe Entity, on peut récupérer directeMent l'objet dont la propriété vaut ce qui sera passé dans l'URL ($livre contiendra le livre dont l'id sera passé dans l'URL)
        */
        // dd($livre); // dump $ die : var_dump et l'execution du code est arrêté

        if ($request->isMethod("POST")) {
            $em->remove($livre); // la requete DELETE est en attente 

            $em->flush(); // toutes les requêtes sont éxécutées
            return $this->redirectToRoute("livre");
        }

        return $this->render("livre/supprimer.html.twig", ["livre" => $livre]);
    }

    #[Route('/{id}', name: 'livre_show', methods: ['GET'])]
    public function show(Livre $livre, AbonneRepository $ar): Response
    {
        /**
         * La fonction compact() de php re tourne un array associatif à partir des variables qui ont le même nom que le paramètres passes à compact
         * Par exemple, si j'ai  2 vara
         * 
         */


        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
            'abonne' => $ar
        ]);
    }

    #[Route('/{id}', name: 'livre_show', methods: ['GET'])]
    public function fiche(Livre $livre): Response
    {
        /**
         * La fonction compact() de php retourne un array associatif à partir des variables qui ont le même nom que le paramètres passes à compact
         * Par exemple, si j'ai 2 variables
         * $nom = 'Ateur';
         * $prenom = 'Nordine';
         *     $personne = compact('nom', 'prenom');
         *   est équivalent à
         *      $personne = ["nom" => "Ateur", "prenom" => "Nordine"];
         */


        return $this->render('livre/show.html.twig', compact("livre"));
    }


    #[Route('/livre/nouveau', name: 'livre_nouveau')]
    public function nouveau(EntityManager $em, Request $request)
    {
        $livre = new Livre(); 
        /*
            'createForm' va créer un objet représentant le formulaire créé à partir de la classe LiverType
            Le 2ème paramètre est un objet Entity qui sera lié au formulaire
        */
        $form = $this->createForm(LivreType::class, $livre);

        /*
            La méthode 'handleRequest' permet à $form de gérer les informations venant de la requête HTTP
            ex : est-ce que le formulaire a été soumis ?...
         */
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { // si le formulaire a été soumis et s'il est valide
            
            if( $fichier = $form->get("couverture")->getData() ) { // getData permet de récupérer les données du fichier
            
                // on récupère le nom du fichier qui a été téléversé
                $nomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                // on remplace les espaces par des _
                $nomFichier = str_replace(" ", "_", $nomFichier);

                // on ajoute un string unique au nom du fichier pour éviter les doublons
                $nomFichier .= uniqid() . "." . $fichier->guessExtension();

                // on copie le fichier uploadé dans un dossier du du dossier 'public' avec le nouveau nom de fichier
                $fichier->move($this->getParameter("dossier_images"), $nomFichier);

                // on modifie l'entité livre
                $livre->setCouverture($nomFichier);
        
            }
            $em->persist($livre);
            $em->flush();
            $this->addFlash("success", "Le livre a bien été emprunté");
            return $this->redirectToRoute("livre");
        }

        return $this->render("livre/form.html.twig", ["formLivre" => $form->createView()]);
    }
}
