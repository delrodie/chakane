<?php

namespace App\EventSubscriber;

use App\Controller\Backend\BackendMonitoringController;
use App\Controller\Backend\BackendSliderController;
use App\Services\AllRepositoty;
use App\Services\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UtilitySubscriber implements EventSubscriberInterface
{
    const Email = 'delrodieamoikon@gmail.com';
    const Password = 'CHAKANE';

    public function __construct(
        private RequestStack $requestStack, private LoggerInterface $logger, private Security $security,
        private AllRepositoty $allRepositoty
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController(); //dd($event);

        if (is_array($controller)) $controller = $controller[0];

        if ($controller instanceof BackendMonitoringController){
            //dd($event);
        }
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->monitoring();
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        // Gestion du cache après opération dans le système
        $method = $event->getRequest()->getMethod();
        if ($method === 'POST') {
            $route = $event->getRequest()->get('_route');
            $this->route_cache($route);
        }
    }

    public function monitoring(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : 'Anonyme';
//        $route = ; //dd($request->getMethod());

        // Initialisation du fichier dans le repertoire /public
        $path = "{$request->server->get('DOCUMENT_ROOT')}/logs.json";

        $page = $this->route_page($user, $request->attributes->get('_route'), $request->getMethod());
        if (!empty($page)){
            // Si le fichier n'existe pas initialiser le
            if (!file_exists($path)){
                $data[0] = [
                    'user' => $user,
                    'ip' =>$request->getClientIp(),
                    'page' => $page,
                    'url' => $request->getUri(),
                    'createdAt' => date('Y-m-d H:i:s'),
                ];

            }else{
                $file =file_get_contents('logs.json'); // Recupération du fichier de logs
                $i = count(json_decode($file, true)); // Nombre d'occurence dans le fichier
                $save[$i++] = [
                    'user' => $user,
                    'ip' => $request->getClientIp(),
                    'page' => $page,
                    'url' =>$request->getUri(),
                    'createdAt' => date('Y-m-d H:i:s'),
                ];

                $data = array_merge($save, json_decode($file, true));
            }

            file_put_contents('logs.json', json_encode($data));
        }
    }


    public function route_page(string $user, $route, string $method=null): bool|string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$route) return false;
        else{
            if ($method === 'POST'){
                $action = match($route){
                    'app_admin_user_index' => "{$user} a enregistré l'utilisateur {$request->get('user')['email']}",
                    'app_admin_user_edit' => "{$user} a modifié l'utilisateur {$request->get('user')['email']}",
                    'app_admin_user_delete' => "{$user} a supprimé un utilisateur",
                    default => false,
                };
            }else{
                $action = match ($route){
                    'app_home' => "{$user} a afficher la page d'accueil",
                    'app_backend_dashboard' => "{$user} a affiché le tableau du bord dans le backoffice",
                    'app_admin_user_index' => "{$user} a affiché la liste des utilisateurs",
                    default => false
                };
            }

            return $action;
        }
    }

    /**
     * Réinitialisation du cache après enregistrement dans le système
     *
     * @param string $route
     * @return int|mixed|void
     */
    public function route_cache(string $route)
    {
        if (!$route) return ;
        return match($route){
            'app_backend_slider_index', 'app_backend_slider_edit', 'app_backend_slider_delete' => $this->allRepositoty->cache('slider', true, true),
            default => 0
        };
    }

}
