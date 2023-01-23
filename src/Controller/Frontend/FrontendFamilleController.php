<?php

namespace App\Controller\Frontend;

use App\Services\AllRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontendFamilleController extends AbstractController
{
    public function __construct(Private AllRepository $allRepository)
    {
    }

    public function famille()
    {
        
    }

}
