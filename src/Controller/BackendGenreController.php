<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Form\GenreType;
use App\Repository\GenreRepository;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backend/genre')]
class BackendGenreController extends AbstractController
{
    public function __construct(private Flasher $flasher)
    {
    }

    #[Route('/', name: 'app_backend_genre_index', methods: ['GET','POST'])]
    public function index(Request $request, GenreRepository $genreRepository): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Verification d'existence du genre
            if ($genreRepository->findOneBy(['titre' => $genre->getTitre()])){
                $this->flasher->create('sweetalert')->addSuccess("Le genre '{$genre->getTitre()}' a déjà été enregistré! ");
                return $this->redirectToRoute('app_backend_genre_index',[],Response::HTTP_SEE_OTHER);
            }

            $genreRepository->save($genre, true);

            $this->flasher->create('pnotify')->addSuccess("Le genre '{$genre->getTitre()}' a été enregistré avec succès!");

            return $this->redirectToRoute('app_backend_genre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backend_genre/index.html.twig', [
            'genres' => $genreRepository->findAll(),
            'genre' => $genre,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_backend_genre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, GenreRepository $genreRepository): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $genreRepository->save($genre, true);

            return $this->redirectToRoute('app_backend_genre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_genre/new.html.twig', [
            'genre' => $genre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backend_genre_show', methods: ['GET'])]
    public function show(Genre $genre): Response
    {
        return $this->render('backend_genre/show.html.twig', [
            'genre' => $genre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backend_genre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Genre $genre, GenreRepository $genreRepository): Response
    {
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $genreRepository->save($genre, true);

            $this->flasher->create('pnotify')->addSuccess("Le genre '{$genre->getTitre()}' a été modifié avec succès!");

            return $this->redirectToRoute('app_backend_genre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backend_genre/edit.html.twig', [
            'genre' => $genre,
            'form' => $form,
            'genres' => $genreRepository->findAll()
        ]);
    }

    #[Route('/{id}', name: 'app_backend_genre_delete', methods: ['POST'])]
    public function delete(Request $request, Genre $genre, GenreRepository $genreRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$genre->getId(), $request->request->get('_token'))) {
            $genreRepository->remove($genre, true);

            $this->flasher->create('pnotify')->addSuccess("Le genre '{$genre->getTitre()}' a été supprimé avec succès!");
        }

        return $this->redirectToRoute('app_backend_genre_index', [], Response::HTTP_SEE_OTHER);
    }
}
