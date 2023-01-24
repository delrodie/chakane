<?php

namespace App\Controller\Backend;

use App\Entity\Image;
use App\Entity\Produit;
use App\Form\ImageType;
use App\Form\ProduitType;
use App\Repository\ImageRepository;
use App\Repository\ProduitRepository;
use App\Services\GestionMedia;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backend/produit')]
class BackendProduitController extends AbstractController
{
    public function __construct(
        private GestionMedia $gestionMedia, private Flasher $flasher, private EntityManagerInterface $entityManager
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

    #[Route('/{id}/images/ajout', name: 'app_backend_produit_show', methods: ['GET','POST'])]
    public function show(Request $request, Produit $produit, ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Recuepration des medias set
            $files = $form->get('media')->getData();
            if (!$files){
                $this->flasher->create('sweetalert')->addError("Attention aucune image n'a été téléchargée!");

                return $this->redirectToRoute("app_backend_produit_show",['id' => $produit->getId()], Response::HTTP_SEE_OTHER);
            }

            // ENregistrement des images
            foreach ($files as $file){
                $image = new Image();
                $media = $this->gestionMedia->upload($file, 'produit');
                $image->setMedia($media);
                $image->setProduit($produit);

                $this->entityManager->persist($image);
            }

            $this->entityManager->flush();

            $this->flasher->create('pnotify')->addSuccess("Les images ont été associées au produit '{$produit->getTitre()}' avec succès!");

            return $this->redirectToRoute('app_backend_produit_show', ['id' => $produit->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backend_produit/show.html.twig', [
            'produit' => $produit,
            'image' => $image,
            'form' => $form,
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
