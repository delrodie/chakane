<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use Flasher\Prime\Flasher;
use Flasher\Prime\FlasherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthenticateSubscriber implements EventSubscriberInterface
{
    const Email = 'delrodieamoikon@gmail.com';
    const Password = '$2y$13$IJ9cYBIoe1QzSVX9ERPGPeRIS4WQ5nPSJ3rIIPuz2mFk3hKd/o6FC';

    public function __construct(
        private RequestStack $requestStack, private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository, private Flasher $flasher, private LoggerInterface $logger
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'security.authentication.success' => 'onSecurityAuthenticationSuccess',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
            KernelEvents::REQUEST => 'onKernelRequest',
            'Symfony\Component\Security\Http\Event\LogoutEvent' => 'onSecurityLogout'
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $route = $event->getRequest()->attributes->get('_route');
        if ($route === 'app_login') {
            //dd($this->passwordHasher->hashPassword(new User(), 'CHACKANE'));
            $verif = $this->userRepository->findOneBy(['email' => self::Email]);
            if (!$verif){
                $user = new User();
                $user->setEmail(self::Email);
                $user->setPassword(self::Password);
                $user->setRoles(['ROLE_SUPER_ADMIN']);
                $this->userRepository->save($user, true);

                $this->flasher->create('pnotify')->addSuccess("Bravo, l'utilisateur CHACKANE vient d'être ajouté avec succès!");

            }
        }
    }

    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $securityToken =$event->getAuthenticationToken();
        $userEmail = $this->getUserEmail($securityToken);

        // Logs de l'utilsateur dans le système

        //Mise a jour de l'entité USER
        $userEntity =  $this->userRepository->findOneBy(['email' => $userEmail]);
        if ($userEntity){
            $userEntity->setConnexion((int) $userEntity->getConnexion() + 1);
            $userEntity->setLastConnectedAt(new \DateTime());

            $this->userRepository->save($userEntity, true);
        }
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        [
            'route_name' =>$routeName,
            'user_IP' => $userIP
        ] = $this->getRouteNameAndUserIP();

        $userEmail = $this->getUserEmail($event->getAuthenticationToken());

        $this->logger->info("L'utilisateur '{$userEmail}' vient de se connecter via l'adresse IP: '{$userIP}' :-)");
    }

    public function onSecurityLogout(LogoutEvent $event): void
    {
        if (!$event->getResponse() || !$event->getToken()) return;

        $userEmail =$this->getUserEmail($event->getToken());
        ['user_IP' => $userIP] =$this->getRouteNameAndUserIP();
        $date = date('Y-m-d H:i:s');

        $this->logger->info("{$userEmail} s'est deconnecté depuis sur l'adresse IP {$userIP} à {$date}");
    }

    private function getUserEmail(TokenInterface $securityToken): string
    {
        return $securityToken->getUserIdentifier();
    }

    private function getRouteNameAndUserIP(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if(!$request) {
            $response = ['route_name' => 'Inconnue', 'user_IP' => 'Inconnue'];
        }
        else {
            $response = [
                'route_name' => $request->attributes->get('_route'),
                'user_IP' => $request->getClientIp() ?? 'Inconnue',
            ];
        }

        return $response;
    }
}
