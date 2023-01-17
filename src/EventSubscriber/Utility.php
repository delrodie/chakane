<?php

namespace App\EventSubscriber;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Utility
{
    public function __construct()
    {
    }

    public function slug(object $entity, string $entityName, bool $resume=null)
    {
        $repository = "{$entityName}Repository";

        // Generation du slug
        $slugify = new AsciiSlugger();
        //$slug = $slugify->slug(str)
    }
}