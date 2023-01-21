<?php

namespace App\EventListener;

use App\Entity\Categorie;
use App\Entity\Famille;
use App\Entity\Genre;
use App\Entity\Produit;
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

        // Gestion de produit
        if ($entity instanceof Produit){
            $this->produit($args);
            $this->devise($args);
            $this->allRepository->cacheProduit('produit', $entity->getSlug(), true);
            $this->allRepository->cacheProduit('produit', null, true);
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

        if ($entity instanceof Produit){
            $this->slug($args);
            $this->devise($args);
            $this->allRepository->cacheProduit('produit', null, true);
            $this->allRepository->cacheProduit('produit', $entity->getSlug(), true);
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Famille){
            $this->allRepository->cacheFamille('famille', null, true);
        }

        if ($entity instanceof  Produit){
            $this->allRepository->cacheProduit('produit', null, true);
            $this->allRepository->cacheProduit('produit', $entity->getSlug(), true);
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

    private function produit(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Generation de la reference
        $id = (int) $entity->getId();
        if ($id < 10) $ref = "000{$id}";
        elseif ($id < 100) $ref = "00{$id}";
        elseif ($id < 1000) $ref = "0{$id}";
        else $ref = $id;

        $reference = $entity->getCategorie()[0]->getCode().$ref;

        // Assignation des valeurs
        $entity->setReference($reference);
        $entity->setSlug($this->slug($args).'-'.$reference);

        $args->getObjectManager()->getRepository(Produit::class)->save($entity, true);

    }

    private function devise(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        $cfaPrix = (int) $entity->getCfaPrix();
        $euroPrix = round(1 / 656 * $cfaPrix, 2);
        $usdPrix = round(1 / 605 * $cfaPrix, 2);

        $euroSolde = null; $usdSolde = null;
        if ($entity->getCfaSolde()){
            $cfaSolde = (int) $entity->getCfaSolde();
            $euroSolde = round(1 / 656 * $cfaSolde, 2);
            $usdSolde = round(1 / 605 * $cfaSolde, 2);
        }

        // Assignation
        $entity->setEuroPrix($euroPrix);
        $entity->setUsdPrix($usdPrix);
        $entity->setEuroSolde($euroSolde);
        $entity->setUsdSolde($usdSolde);

        $args->getObjectManager()->getRepository(Produit::class)->save($entity, true);
    }

    private function slug(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        return (new AsciiSlugger())->slug(strtolower($entity->getTitre()));
    }

}