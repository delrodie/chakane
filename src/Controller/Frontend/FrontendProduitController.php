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

    public function index()
    {
        
    }
}
