<?php

namespace App\Services;

use App\Repository\CategorieRepository;
use App\Repository\FamilleRepository;
use App\Repository\PresentationRepository;
use App\Repository\SliderRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AllRepository
{
    public function __construct(
        private RequestStack $requestStack, private CacheInterface $cache, private SliderRepository $sliderRepository,
        private PresentationRepository $presentationRepository, private FamilleRepository $familleRepository,
        private CategorieRepository $categorieRepository
    )
    {
    }

    public function cache(string $entityName, bool $delete=false, bool $backend=false)
    {
        if ($backend) $cacheName = $entityName.$backend;
        else $cacheName = $entityName;

        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use($entityName, $backend){
            $item->expiresAfter(604800);
            $repository = "{$entityName}Repository";
            if ($backend) return  $this->$repository->findAll();
            else return $this->$repository->findBy(['statut' => true]);
        });
    }

    public function cachePresentation(bool $delete=false)
    {
        $cacheName = "Presentation";
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item){
            $item->expiresAfter(604800);
            return $this->presentationRepository->findOneBy([],['id'=>'DESC']);
        });
    }

    public function cacheFamille(string $entityName, string $slug=null, bool $delete=false)
    {
        $cacheName = $entityName.$slug;
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use($slug){
            $item->expiresAfter(604800);
            if ($slug) return $this->familleRepository->findOneBy(['slug' => $slug]);
            else return $this->familleRepository->findBy(['statut' => true]);
        });
    }

    public function cacheCategorie(string $entityName, string $slug=null, bool $delete=false)
    {
        $cacheName = $entityName.$slug;
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($slug){
            $item->expiresAfter(604800);
            if (!$slug) return $this->categorieRepository->findAllWithJoin();
            else return $this->categorieRepository->findAllWithJoin($slug);
        });
    }

    /**
     * Liste des logs enregistrés
     *
     * @return array
     */
    public function logs()
    {
        $path =$this->requestStack->getCurrentRequest()->server->get('DOCUMENT_ROOT').'/logs.json';
        if (!file_exists($path)) return [];

        $datas =json_decode(file_get_contents('logs.json'), true);
        asort($datas);

        $list=[]; $i=0;
        foreach ($datas as $data){
            $list[$i++]=[
                'id' => $i,
                'user' => $data['user'],
                'ip' => $data['ip'],
                'page' => $data['page'],
                'url' => '<a href="'.$data['url'].'" target="_blank">'.$data["page"].'</a>',
                'created_at' => $data['createdAt'],
            ];
        };

        return $list;
        
    }
}