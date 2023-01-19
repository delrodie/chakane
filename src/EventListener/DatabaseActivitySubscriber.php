<?php

namespace App\EventListener;

use App\Entity\Categorie;
use App\Entity\Famille;
use App\Entity\Genre;
use App\Repository\FamilleRepository;
use App\Services\AllRepository;
use App\Services\Utility;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Flasher\Prime\Flasher;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class DatabaseActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Utility $utility, private Flasher $flasher, private AllRepository $allRepository,
        private UrlGeneratorInterface $urlGenerator
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

        // Gestion de la famille
        if ($entity instanceof Famille){
            $this->famille($args);
            $this->allRepository->cacheFamille('famille', null, true);
        }

        // Gestion de la categorie
        if ($entity instanceof Categorie){
            $this->categorie($args);
        }

        // Gestion du genre
        if ($entity instanceof Genre){
            $this->genre($args);
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

        if ($entity instanceof Categorie){
            $this->categorie($args);
        }

        if ($entity instanceof Genre){
            $this->genre($args);
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

    private function categorie(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $repository = $args->getObjectManager();

        // Generation de code de la catégorie
        $id = $entity->getId();
        if ($id < 10) $ref = "0{$id}";
        else $ref = $id;

        $code = $entity->getFamille()->getCode().$ref;

        $entity->setCode($code);
        $entity->setSlug($this->slug($args));

        $repository->getRepository(Categorie::class)->save($entity, true);
    }

    private function genre(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $entity->setSlug($this->slug($args));

        $args->getObjectManager()->getRepository(Genre::class)->save($entity, true);
    }

    private function slug(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        return (new AsciiSlugger())->slug(strtolower($entity->getTitre()));
    }

}