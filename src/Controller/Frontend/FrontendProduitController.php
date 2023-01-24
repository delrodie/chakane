<?php

namespace App\Controller\Frontend;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Services\AllRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article')]
class FrontendProduitController extends AbstractController
{
    public function __construct(
        private AllRepository $allRepository
    )
    {
    }

    #[Route('/{slug}', name: "app_frontend_produit")]
    public function index($slug)
    {
        $reposi = $this->allRepository->cacheProduitBySlug($slug, true);
        if (!$reposi)
            throw $this->createNotFoundException("Ce produit n'existe pas");

        $produit = $reposi[0];
        if (!$produit)
            throw $this->createNotFoundException("Aucun produit trouvé");

        $categorie = $produit->getCategorie()[0];

        return $this->render('frontend/article.html.twig',[
            'produit' => $produit,
            'categorie' => $categorie,
            'genre' => $categorie->getGenre()[0],
            'similaires' => $this->allRepository->cacheProduitByCategorie($categorie->getSlug(), true)
        ]);
    }
}
