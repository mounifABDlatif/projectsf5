<?php

namespace App\Controller;

use App\Repository\AbonneRepository;
use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RechercheController extends AbstractController
{
    #[Route('/recherche', name: 'recherche_index')]
    public function index(LivreRepository $lr, AbonneRepository $ar, Request $rq): Response
    {
        $mot = $rq->query->get("search");
        $livres = $lr->recherche($mot);

        $livres_empruntes = $lr->livresEmpruntes();

        $abonnes = $ar->recherche($mot);

        return $this->render('recherche/index.html.twig', compact("livres", "mot", "livres_empruntes", "abonnes"));
        /* 
        ou
        
        return $this->render('recherche/index.html.twig', [

            "livres" => $livres,
            "mot" => $mot,
            "livres_empruntes" => $livres_empruntes        ];*/
    }
}
