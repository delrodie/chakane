<?php

namespace App\Controller\Backend;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Services\GestionMedia;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backend/produit')]
class BackendProduitController extends AbstractController
{
    public function __construct(
        private GestionMedia $gestionMedia, private Flasher $flasher
    )
    {
    }

    #[Route('/', name: 'app_backend_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('backend_produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_backend_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'produit');
                $produit->setMedia($media);
            }

            $produitRepository->save($produit, true);

            $this->flasher->create('pnotify')->addSuccess("Le produit '{$produit->getTitre()}' a été ajouté avec succès!");

            return $this->redirectToRoute('app_backend_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('backend_produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backend_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'produit');
                if ($produit->getMedia())
                    $this->gestionMedia->removeUpload($produit->getMedia(), 'produit');

                $produit->setMedia($media);
            }

            $produitRepository->save($produit, true);

            $this->flasher->create('pnotify')->addSuccess("Le produit '{$produit->getTitre()}' a été modifié avec succès!");

            return $this->redirectToRoute('app_backend_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
            if ($produit->getMedia())
                $this->gestionMedia->removeUpload($produit->getMedia(), 'produit');

            $this->flasher->create('pnotify')->addSuccess("Le produit '{$produit->getTitre()} a été supprimé avec succès!");
        }

        return $this->redirectToRoute('app_backend_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
