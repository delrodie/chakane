<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/user')]
class AdminUserController extends AbstractController
{
    const Email = "delrodieamoikon@gmail.com";

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher, private Flasher $flasher
    )
    {
    }

    #[Route('/', name: 'app_admin_user_index', methods: ['GET','POST'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Verification de l'existence de l'adresse email
            $verif = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($verif){
                $this->flasher
                    ->create('sweetalert')
                    ->icon('error')
                    ->addError("Attention! Le compte '{$user->getEmail()}' existe déjà!");

                return $this->redirectToRoute('app_admin_user_index',[], Response::HTTP_SEE_OTHER);
            }
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

            $userRepository->save($user, true);

            $this->flasher->create('pnotify')->addSuccess("Le compte '{$user->getEmail()}' a été ajouté avec succès!");

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_user/index.html.twig', [
            'users' => $userRepository->findListWithout(self::Email),
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin_user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $userRepository->save($user, true);

            $this->flasher->create('pnotify')->addSuccess("L'utilisateur '{$user->getEmail()}' a été modifié avec succès!");

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'users' => $userRepository->findListWithout(self::Email)
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);

            $this->flasher->create('pnotify')->addSuccess("L'utilisateur '{$user->getEmail()}' a été supprimé avec succès!");
        }

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
