<?php

namespace App\Controller\Frontend;

use App\Services\AllRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class FrontendMarqueController extends AbstractController
{
    public function __construct(
        private AllRepository $allRepository
    )
    {
    }

    #[Route('/presentation', name: 'app_frontend_marque_presentation')]
    public function index(): Response
    {
        return $this->render('frontend/presentation.html.twig',[
            'presentation' => $this->allRepository->cachePresentation()
        ]);
    }
}
