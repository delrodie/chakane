<?php

namespace App\Controller;

use App\Repository\SliderRepository;
use App\Services\AllRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private AllRepository $allRepository, private SliderRepository $sliderRepository
    )
    {

    }
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('frontend/home.html.twig',[
            'slides' => $this->allRepository->cache('slider')
        ]);
    }

    #[Route('/sitemap.xml', name: 'app_sitemap_xml', defaults: ["_format" => 'xml'])]
    public function sitemap(Request $request): Response
    {
        $hostname = $request->getSchemeAndHttpHost(); // Recuperation du nom de l'hôte depuis l'url
        $urls = []; // Initialisation du tableau pour lister les urls

        $urls[] =['loc' => $this->generateUrl('app_home')]; // Generation des urls statics

        // Presentation
        $presentation = $this->allRepository->cachePresentation();
        if ($presentation){
            $image = [
                'loc' => "/uploads/marque/{$presentation->getMedia()}",
                'title' => $presentation->getTitre()
            ];
            $urls[] = [
                'loc' => $this->generateUrl('app_frontend_marque_presentation'),
                'lastmod' => $presentation->getUpdatedAt()->format('Y-m-d') ?? $presentation->getCreatedAt()->format('Y-m-d'),
                'image' => $image
            ];
        }

        // Urls dynamiques
        /*
         * $presentations = $frPresentationRepository->findAll();
         *
         * foreach ($presentations as $presentation){
                $images = [
                    'loc' => '/uploads/presentation/'.$presentation->getMedia(),
                    'title' => $presentation->getTitre()
                ];
                $urls[] = [
                    'loc' => $this->generateUrl('app_frontend_presentation',['_locale'=>$loc, 'slug'=>$presentation->getSlug()]),
                    'lastmod' => $presentation->getCreatedAt()->format('Y-m-d'),
                    'image' => $images
                ];
            }
         */

        $response =new Response(
            $this->renderView('frontend/sitemap.html.twig',[
                'urls' => $urls,
                'hostname' => $hostname
            ]), 200
        ); //dd($response);

        $response->headers->add(['Content-Type' => 'text/xml']);

        return $response;
    }
}
