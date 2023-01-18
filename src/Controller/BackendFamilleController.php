<?php

namespace App\Controller;

use App\Entity\Famille;
use App\Form\FamilleType;
use App\Repository\FamilleRepository;
use App\Services\GestionMedia;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backend/famille')]
class BackendFamilleController extends AbstractController
{
    public function __construct(private GestionMedia $gestionMedia, private Flasher $flasher)
    {
    }

    #[Route('/', name: 'app_backend_famille_index', methods: ['GET', 'POST'])]
    public function index(Request $request, FamilleRepository $familleRepository): Response
    {
        $famille = new Famille();
        $form = $this->createForm(FamilleType::class, $famille);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'famille');
                $famille->setMedia($media);
            }
            $familleRepository->save($famille, true);

            $this->flasher->create('pnotify')->addSuccess("La famille '{$famille->getTitre()}' a été ajouté avec succès!");

            return $this->redirectToRoute('app_backend_famille_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backend_famille/index.html.twig', [
            'familles' => $familleRepository->findAll(),
            'famille' => $famille,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_backend_famille_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FamilleRepository $familleRepository): Response
    {
        $famille = new Famille();
        $form = $this->createForm(FamilleType::class, $famille);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // GEstion ds medias

            $familleRepository->save($famille, true);

            return $this->redirectToRoute('app_backend_famille_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_famille/new.html.twig', [
            'famille' => $famille,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_famille_show', methods: ['GET'])]
    public function show(Famille $famille): Response
    {
        return $this->render('backend_famille/show.html.twig', [
            'famille' => $famille,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backend_famille_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Famille $famille, FamilleRepository $familleRepository): Response
    {
        $form = $this->createForm(FamilleType::class, $famille);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'famille');
                if ($famille->getMedia())
                    $this->gestionMedia->removeUpload($famille->getMedia(), 'famille');

                $famille->setMedia($media);
            }

            $familleRepository->save($famille, true);

            $this->flasher->create('pnotify')->addSuccess("La famille '{$famille->getTitre()}' a été modifié avec succès!");

            return $this->redirectToRoute('app_backend_famille_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_famille/edit.html.twig', [
            'famille' => $famille,
            'form' => $form,
            'familles' => $familleRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_backend_famille_delete', methods: ['POST'])]
    public function delete(Request $request, Famille $famille, FamilleRepository $familleRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$famille->getId(), $request->request->get('_token'))) {
            $familleRepository->remove($famille, true);

            if ($famille->getMedia())
                $this->gestionMedia->removeUpload($famille->getMedia(), 'famille');

            $this->flasher->create('pnotify')->addSuccess("La famille '{$famille->getTitre()}' a été supprimée avec succès!");
        }

        return $this->redirectToRoute('app_backend_famille_index', [], Response::HTTP_SEE_OTHER);
    }
}
