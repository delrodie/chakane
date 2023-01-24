<?php

namespace App\Controller\Frontend;

use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class FrontendPanierController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack
    )
    {
    }

    #[Route('/', name: 'app_frontend_panier')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/FrontendPanierController.php',
        ]);
    }

    #[Route('/{slug}', name: 'app_frontend_panier_ajout', methods: ['GET'])]
    public function add(Produit $produit)
    {
        //dd($produit);
        return $this->redirectToRoute('app_home');
    }
}
