<?php

namespace App\Controller\Backend;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use App\Services\GestionMedia;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backend/categorie')]
class BackendCategorieController extends AbstractController
{
    public function __construct(
        private GestionMedia $gestionMedia, private Flasher $flasher
    )
    {
    }

    #[Route('/', name: 'app_backend_categorie_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('backend_categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_backend_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategorieRepository $categorieRepository): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'categorie');
                $categorie->setMedia($media);
            }

            $categorieRepository->save($categorie, true);

            $this->flasher->create('pnotify')->addSuccess("La catégorie '{$categorie->getTitre()}' a été ajoutée avec succès!");

            return $this->redirectToRoute('app_backend_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): Response
    {
        return $this->render('backend_categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backend_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, CategorieRepository $categorieRepository): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'categorie');
                if ($categorie->getMedia())
                    $this->gestionMedia->removeUpload($categorie->getMedia(), 'categorie');

                $categorie->setMedia($media);
            }

            $categorieRepository->save($categorie, true);

            $this->flasher->create('pnotify')->addSuccess("La catégorie '{$categorie->getTitre()}' a été modifiée avec succès!");

            return $this->redirectToRoute('app_backend_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, CategorieRepository $categorieRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            $categorieRepository->remove($categorie, true);

            if ($categorie->getMedia())
                $this->gestionMedia->removeUpload($categorie->getMedia(), 'categorie');

            $this->flasher->create('pnotify')->addSuccess("La catégorie '{$categorie->getTitre()}' a été supprimée avec succès!");
        }

        return $this->redirectToRoute('app_backend_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
