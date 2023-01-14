<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
class BackendController extends AbstractController
{
    #[Route('/', name: 'app_backend_dashboard')]
    public function index(): Response
    {
        return $this->render('backend/dasboard.html.twig', [
            'controller_name' => 'BackendController',
        ]);
    }
}
