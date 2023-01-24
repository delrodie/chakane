<?php

namespace App\Controller\Frontend;

use App\Entity\Categorie;
use App\Services\AllRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('shop')]
class FrontendFamilleController extends AbstractController
{
    public function __construct(Private AllRepository $allRepository)
    {
    }

    #[Route('/{famille}', name: 'app_frontend_famille_index')]
    public function famille($famille): Response
    {
        $familleEntity = $this->entityFamille($famille);

        return $this->render('frontend/famille.html.twig',[
            'famille' => $familleEntity,
            'produits' => $this->allRepository->cacheProduitByFamilleAndGenre($familleEntity->getSlug(), true),
            'genre' => null,
            'categorie' => null
        ]);
    }

    #[Route('/{famille}/{genre}', name: 'app_frontend_famille_genre')]
    public function genre($famille, $genre): Response
    {
        $familleEntity = $this->entityFamille($famille);
        $genreEntity = $this->allRepository->cacheGetGenre($genre);

        return $this->render('frontend/famille.html.twig',[
            'famille' => $familleEntity,
            'produits' => $this->allRepository->cacheProduitByFamilleAndGenre($familleEntity->getSlug(), $genreEntity[0]->getTitre(), true),
            'genre' => $genreEntity[0],
            'categorie' => null
        ]);

    }

    #[Route('/{famille}/{genre}/{slug}', name: 'app_frontend_famille_categorie')]
    public function categorie($famille, $genre, $slug)
    {
        $categorie = $this->allRepository->cacheCategorie('categorie', $slug, true);
        return $this->render('frontend/famille.html.twig',[
            'famille' => $this->allRepository->cacheFamille('famille', $famille, true),
            'genre' => $this->allRepository->cacheGenre('genre', $genre, true),
            'categorie' => $categorie[0],
            'produits' => $this->allRepository->cacheProduitByCategorie($slug, true)
        ]);
    }

    protected function entityFamille($famille)
    {
        $entity = $this->allRepository->cacheGetFamille($famille, true);
        if (!$entity)
            throw $this->createNotFoundException("Aucune famille trouvée");

        return $entity[0];
    }

}
