<?php

namespace App\EventListener;

use App\Entity\Famille;
use App\Repository\FamilleRepository;
use App\Services\AllRepository;
use App\Services\Utility;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Flasher\Prime\Flasher;

class DatabaseActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Utility $utility, private Flasher $flasher, private AllRepository $allRepository
    )
    {
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $repo = $args->getObjectManager();

        if ($entity instanceof Famille){
            $this->famille($args);
            $this->allRepository->cacheFamille('famille', null, true);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Famille){
            $this->famille($args);
            $this->allRepository->cacheFamille('famille', null, true);
            $this->allRepository->cacheFamille('famille', $entity->getSlug(), true);
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Famille){
            $this->allRepository->cacheFamille('famille', null, true);
        }
    }

    private function famille(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $repositoty = $args->getObjectManager();
        $this->utility->slug2($entity, 'famille');

        // Code de la famille
        $code = substr($entity->getSlug(), 0, 2);
        $entity->setCode(strtoupper($code));
        $repositoty->getRepository(Famille::class)->save($entity, true);
    }


}