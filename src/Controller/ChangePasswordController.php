<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/change-password')]
class ChangePasswordController extends AbstractController
{
    #[Route('/', name: 'app_change_password')]
    public function index(Request $request, AuthenticationUtils $authenticationUtils, UserPasswordHasherInterface $passwordHasher, Flasher $flasher, UserRepository $userRepository): Response
    {
        // Vérification de la connexion de l'utilisateur
        $last_username = $authenticationUtils->getLastUsername();
        if (!$last_username){
            $flasher->addError("Attention veuillez vous connecter pour changer votre mot de passe.");

            return $this->redirectToRoute('app_login',[], Response::HTTP_SEE_OTHER);
        }

        // Récuperation des informations du formulaire
        $username = $request->request->get('_username');
        $password =$request->request->get('_password');

        // Modification du mot de passe si c'est le même utilisateur
        if ($username === $last_username && $request->request->get('_csrf_token')){
            $user = $userRepository->findOneBy(['email' => $username]);
            $userRepository->upgradePassword($user, $passwordHasher->hashPassword($user, $password));

            $flasher->create('pnotify')->addSuccess("Le mot de passe a été modifié avec succès! Veuillez vous reconnecter");

            return $this->redirectToRoute('app_logout');
        }

        return $this->render('security/change_password.html.twig',[
            'last_username' => $last_username,
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }
}
