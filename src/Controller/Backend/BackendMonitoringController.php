<?php

namespace App\Controller\Backend;

use App\Services\AllRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backend/monitoring')]
class BackendMonitoringController extends AbstractController
{
    #[Route('/', name: 'app_backend_monitoring')]
    public function index(Request $request, AllRepository $allRepository): Response
    {
        return $this->render('backend/monitoring.html.twig',[
            'logs' => $allRepository->logs(),
        ]);
    }
}
