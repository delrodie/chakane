<?php

namespace App\EventSubscriber;

use App\Controller\Backend\BackendMonitoringController;
use App\Controller\Backend\BackendSliderController;
use App\Services\AllRepository;
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
        private AllRepository $allRepository
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
        // Gestion du cache apr??s op??ration dans le syst??me
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
                $file =file_get_contents('logs.json'); // Recup??ration du fichier de logs
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
        $request = $this->requestStack->getCurrentRequest(); //dd($request);
        if (!$route) return false;
        else{
            if ($method === 'POST'){
                $action = match($route){
                    'app_admin_user_index' => "{$user} a enregistr?? l'utilisateur {$request->get('user')['email']}",
                    'app_admin_user_edit' => "{$user} a modifi?? l'utilisateur {$request->get('user')['email']}",
                    'app_admin_user_delete' => "{$user} a supprim?? un utilisateur",
                    'app_backend_slider_index' => "{$user} a enregistr?? le slide {$request->get('slider')['titre']}",
                    'app_backend_slider_edit' => "{$user} a modifi?? le slide {$request->get('slider')['titre']}",
                    'app_backend_slider_delete' => "{$user} a supprim?? un slide",
                    'app_backend_presentation_new' => "{$user} a enregistr?? la presentation de la marque {$request->get('presentation')['titre']}",
                    'app_backend_presentation_edit' => "{$user} a modifi?? la presentation de la marque {$request->get('presentation')['titre']}",
                    'app_backend_famille_index' => "{$user} a enregistr?? la famille {$request->get('famille')['titre']}",
                    'app_backend_famille_edit' => "{$user} a modifi?? la famille {$request->get('famille')['titre']}",
                    'app_backend_famille_delete' => "{$user} a supprim?? une famille ",
                    default => false,
                };
            }else{
                $action = match ($route){
                    'app_home' => "{$user} a afficher la page d'accueil",
                    'app_backend_dashboard' => "{$user} a affich?? le tableau du bord dans le backoffice",
                    'app_admin_user_index' => "{$user} a affich?? la liste des utilisateurs",
                    'app_backend_slider_index' => "{$user} a affich?? la liste des sliders",
                    'app_frontend_marque_presentation' => "{$user} a affich?? la pr??sentation de la marque",
                    'app_backend_famille_index' => "{$user} a affich?? la liste des familles",
                    default => false
                };
            }

            return $action;
        }
    }

    /**
     * R??initialisation du cache apr??s enregistrement dans le syst??me
     *
     * @param string $route
     * @return int|mixed|void
     */
    public function route_cache(string $route)
    {
        if (!$route) return ;
        $backend =  match($route){
            'app_backend_slider_index', 'app_backend_slider_edit', 'app_backend_slider_delete' => $this->allRepository->cache('slider', true, true),
            'app_backend_presentation_new', 'app_backend_presentation_edit', 'app_backend_presentation_delete' => $this->allRepository->cachePresentation(true),
            default => 0
        };
        $frontend =  match($route){
            'app_backend_slider_index', 'app_backend_slider_edit', 'app_backend_slider_delete' => $this->allRepository->cache('slider', true),
            default => 0
        };

        return true;

    }

}
