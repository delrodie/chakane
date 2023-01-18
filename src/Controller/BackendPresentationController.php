<?php

namespace App\Controller;

use App\Entity\Presentation;
use App\Form\PresentationType;
use App\Repository\PresentationRepository;
use App\Services\GestionMedia;
use App\Services\Utility;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backend/presentation')]
class BackendPresentationController extends AbstractController
{
    public function __construct(
        private Utility $utility, private GestionMedia $gestionMedia, private Flasher $flasher
    )
    {
    }

    #[Route('/', name: 'app_backend_presentation_index', methods: ['GET'])]
    public function index(PresentationRepository $presentationRepository): Response
    {
        $presentation = $presentationRepository->findOneBy([],['id' => 'DESC']);
        if (!$presentation)
            return $this->redirectToRoute('app_backend_presentation_new');
        else
            return $this->redirectToRoute('app_backend_presentation_show',['id' => $presentation->getId()], Response::HTTP_SEE_OTHER);

    }

    #[Route('/new', name: 'app_backend_presentation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PresentationRepository $presentationRepository): Response
    {
        $presentation = new Presentation();
        $form = $this->createForm(PresentationType::class, $presentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $exist = $presentationRepository->findOneBy([],['id'=>'DESC']);
            if ($exist) return $this->redirectToRoute('app_backend_presentation_index');

            $this->utility->slug($presentation, 'presentation', true); // Gestion des slugs

            // Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'presentation');
                $presentation->setMedia($media);
            }

            $presentationRepository->save($presentation, true);

            $this->flasher->create('pnotify')->addSuccess("La présentation de la marque a été enregistrée avec succès!");

            return $this->redirectToRoute('app_backend_presentation_show', [
                'id' => $presentation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_presentation/new.html.twig', [
            'presentation' => $presentation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_presentation_show', methods: ['GET'])]
    public function show(Presentation $presentation): Response
    {
        return $this->render('backend_presentation/show.html.twig', [
            'presentation' => $presentation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backend_presentation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Presentation $presentation, PresentationRepository $presentationRepository): Response
    {
        $form = $this->createForm(PresentationType::class, $presentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->utility->slug($presentation, 'presentation', true); // Gestion des slugs
            // Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'presentation');
                if ($presentation->getMedia())
                    $this->gestionMedia->removeUpload($presentation->getMedia(), 'presentation');

                $presentation->setMedia($media);
            }

            $presentationRepository->save($presentation, true);

            $this->flasher->create('pnotify')->addSuccess("La presentation de la marque a été modifiée avec succès!");

            return $this->redirectToRoute('app_backend_presentation_show', [
                'id' => $presentation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_presentation/edit.html.twig', [
            'presentation' => $presentation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_presentation_delete', methods: ['POST'])]
    public function delete(Request $request, Presentation $presentation, PresentationRepository $presentationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$presentation->getId(), $request->request->get('_token'))) {
            $presentationRepository->remove($presentation, true);

            if ($presentation->getMedia())
                $this->gestionMedia->removeUpload($presentation->getMedia(), 'presentation');

            $this->flasher->create('pnotify')->addSuccess("La présentation de la marque a été supprimée avec succès!");
        }

        return $this->redirectToRoute('app_backend_presentation_index', [], Response::HTTP_SEE_OTHER);
    }
}
