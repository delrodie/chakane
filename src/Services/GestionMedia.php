<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class GestionMedia
{
    private $mediaSlide;
    private $mediaMarque;
    private $mediaFamille;
    private $mediaCategorie;

    public function __construct(
        $slideDirectory, $presentationDirectory, $familleDirectory, $categorieDirectory
    )
    {
        $this->mediaSlide = $slideDirectory;
        $this->mediaMarque = $presentationDirectory;
        $this->mediaFamille = $familleDirectory;
        $this->mediaCategorie = $categorieDirectory;
    }


    /**
     * @param UploadedFile $file
     * @param $media
     * @return string
     */
    public function upload(UploadedFile $file, $media = null): string
    {
        // Initialisation du slug
        $slugify = new AsciiSlugger();

        $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugify->slug($originalFileName);
        $newFilename = $safeFilename.'-'.Time().'.'.$file->guessExtension();

        // Deplacement du fichier dans le repertoire dedié
        try {
            if ($media === 'slide') $file->move($this->mediaSlide, $newFilename);
            elseif ($media === 'presentation') $file->move($this->mediaMarque, $newFilename);
            elseif ($media === 'famille') $file->move($this->mediaFamille, $newFilename);
            elseif ($media === 'categorie') $file->move($this->mediaCategorie, $newFilename);
            else $file->move($this->mediaSlide, $newFilename);
        }catch (FileException $e){

        }

        return $newFilename;
    }

    /**
     * Suppression de l'ancien media sur le server
     *
     * @param $ancienMedia
     * @param null $media
     * @return bool
     */
    public function removeUpload($ancienMedia, $media = null): bool
    {
        if ($media === 'slide') unlink($this->mediaSlide.'/'.$ancienMedia);
        elseif ($media === 'presentation') unlink($this->mediaMarque.'/'.$ancienMedia);
        elseif ($media === 'famille') unlink($this->mediaFamille.'/'.$ancienMedia);
        elseif ($media === 'categorie') unlink($this->mediaCategorie.'/'.$ancienMedia);
        else return false;

        return true;
    }
}