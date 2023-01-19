<?php

namespace App\Controller\Backend;

use App\Entity\Slider;
use App\Form\SliderType;
use App\Repository\SliderRepository;
use App\Services\AllRepository;
use App\Services\GestionMedia;
use App\Services\Utility;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backend/slider')]
class BackendSliderController extends AbstractController
{
    const EntityName = 'slider';
    public function __construct(
        private GestionMedia $gestionMedia, private Utility $utility, private Flasher $flasher,
        private AllRepository $allRepository
    )
    {
    }

    #[Route('/', name: 'app_backend_slider_index', methods: ['GET','POST'])]
    public function index(Request $request, SliderRepository $sliderRepository): Response
    {
        $slider = new Slider();
        $form = $this->createForm(SliderType::class, $slider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->utility->slug($slider, 'slider'); // Generation du slug

            // Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile) {
                $media = $this->gestionMedia->upload($mediaFile, 'slide');
                $slider->setMedia($media);
            }

            $sliderRepository->save($slider, true);

            $this->flasher
                ->create('pnotify')
                ->addSuccess("Le slide {$slider->getTitre()} a été ajouté avec succès!");

            return $this->redirectToRoute('app_backend_slider_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backend_slider/index.html.twig', [
            'sliders' => $this->allRepository->cache(self::EntityName, false, true),
            'slider' => $slider,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_backend_slider_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SliderRepository $sliderRepository): Response
    {
        $slider = new Slider();
        $form = $this->createForm(SliderType::class, $slider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sliderRepository->save($slider, true);

            return $this->redirectToRoute('app_backend_slider_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_slider/new.html.twig', [
            'slider' => $slider,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_slider_show', methods: ['GET'])]
    public function show(Slider $slider): Response
    {
        return $this->redirectToRoute('app_backend_slider_index',[],Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_backend_slider_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Slider $slider, SliderRepository $sliderRepository): Response
    {
        $form = $this->createForm(SliderType::class, $slider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->utility->slug($slider, 'slider'); // Gestion des slug

            // Gestion des medias
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile){
                $media = $this->gestionMedia->upload($mediaFile, 'slider');
                if ($slider->getMedia())
                    $this->gestionMedia->removeUpload($slider->getMedia(), 'slider');

                $slider->setMedia($media);
            }

            $sliderRepository->save($slider, true);

            $this->flasher
                ->create('pnotify')
                ->addSuccess("Le slide '{$slider->getTitre()}' a été modifié avec succès!");

            return $this->redirectToRoute('app_backend_slider_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_slider/edit.html.twig', [
            'slider' => $slider,
            'form' => $form,
            'sliders' => $this->allRepositoty->cache(self::EntityName, false, true),
        ]);
    }

    #[Route('/{id}', name: 'app_backend_slider_delete', methods: ['POST'])]
    public function delete(Request $request, Slider $slider, SliderRepository $sliderRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$slider->getId(), $request->request->get('_token'))) {
            $sliderRepository->remove($slider, true);

            if ($slider->getMedia())
                $this->gestionMedia->removeUpload($slider->getMedia(), 'slider');

            $this->flasher
                ->create('pnotify')
                ->addSuccess("Le slide '{$slider->getTitre()}' a été supprimé avec succès!");
        }

        return $this->redirectToRoute('app_backend_slider_index', [], Response::HTTP_SEE_OTHER);
    }
}
