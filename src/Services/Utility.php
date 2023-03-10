<?php

namespace App\Services;

use App\Repository\FamilleRepository;
use App\Repository\PresentationRepository;
use App\Repository\SliderRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;

class Utility
{
    public function __construct(
        private SliderRepository $sliderRepository, private PresentationRepository $presentationRepository,
        private FamilleRepository $familleRepository
    )
    {
    }

    /**
     * Generation du slug et/ou du resume de l'entité
     *
     * @param object $entity
     * @param string $entityName
     * @param bool|null $resume
     * @return false|object
     */
    public function slug(object $entity, string $entityName, bool $resume=null)
    {
        $repository = "{$entityName}Repository";

        // Generation du slug
        $slugify = new AsciiSlugger();
        $slug = $slugify->slug(strtolower($entity->getTitre()));

        // Verification de la non existence de ce slug dansla base de données
        // Sinon assigner le slug formatté
        $verif =$this->$repository->findOneBy(['slug' => $slug]);
        //if ($verif) return false;
        $entity->setSlug($slug); //dd($slug);

        // Generation du résumé
        if ($resume){
            $contenu =substr(strip_tags($entity->getContenu()), 0, 155);
            $entity->setResume($contenu);
        }

        return $entity;
    }

    public function slug2(object $entity, string $entityName, bool $resume=false)
    {
        $repository = "{$entityName}Repository";

        // Generation du slug
        $slugify = new AsciiSlugger();
        $slug = $slugify->slug(strtolower($entity->getTitre()));
        $entity->setSlug($slug);

        if ($resume){
            $contenu = substr(strip_tags($entity->getDescription()), 0, 155);
            $entity->setResume($contenu);
        }

        return $entity;
    }
}