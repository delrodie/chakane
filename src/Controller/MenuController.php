<?php

namespace App\Controller;

use App\Services\AllRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/menu')]
class MenuController extends AbstractController
{
    public function __construct(private AllRepository $allRepository)
    {
    }

    #[Route('/{genre}', name: 'app_menu_genre', methods: ['GET'])]
    public function index($genre): Response
    {
        //dd($genre);
        $genreEntity = $this->allRepository->findByGenre($genre);
        if (!$genreEntity)
            throw $this->createNotFoundException("Le genre n'existe pas");


        return $this->render('frontend/menu.html.twig', [
            'genre' => $genreEntity[0],
            'categories' => $this->allRepository->cacheMenu($genreEntity[0]->getTitre(), true)
        ]);
    }
}
