<?php

namespace App\Controller\Backend;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use App\Services\GestionMedia;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backend/image')]
class BackendImageController extends AbstractController
{
    public function __construct(private GestionMedia $gestionMedia, private Flasher $flasher)
    {
    }

    #[Route('/', name: 'app_backend_image_index', methods: ['GET'])]
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('backend_image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_backend_image_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageRepository->save($image, true);

            return $this->redirectToRoute('app_backend_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_image/new.html.twig', [
            'image' => $image,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_image_show', methods: ['GET'])]
    public function show(Image $image): Response
    {
        return $this->render('backend_image/show.html.twig', [
            'image' => $image,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backend_image_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Image $image, ImageRepository $imageRepository): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'produit');
                if ($image->getMedia())
                    $this->gestionMedia->removeUpload($image->getMedia(), 'produit');

                $image->setMedia($media);
            }
            $imageRepository->save($image, true);

            $this->flasher->create('pnotify')->addSuccess("L'image a été modifiée avec succès!");

            return $this->redirectToRoute('app_backend_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_image/edit.html.twig', [
            'image' => $image,
            'form' => $form,
            'images' => $imageRepository->findBy(['produit' => $image->getProduit()])
        ]);
    }

    #[Route('/{id}', name: 'app_backend_image_delete', methods: ['POST'])]
    public function delete(Request $request, Image $image, ImageRepository $imageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $imageRepository->remove($image, true);

            if ($image->getMedia())
                $this->gestionMedia->removeUpload($image->getMedia(), 'produit');

            $this->flasher->create('pnotify')->addSuccess("L'image a été supprimée avec succès!");
        }

        return $this->redirectToRoute('app_backend_image_index', [], Response::HTTP_SEE_OTHER);
    }
}
