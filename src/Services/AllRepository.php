<?php

namespace App\Services;

use App\Repository\CategorieRepository;
use App\Repository\FamilleRepository;
use App\Repository\GenreRepository;
use App\Repository\PresentationRepository;
use App\Repository\ProduitRepository;
use App\Repository\SliderRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AllRepository
{
    public function __construct(
        private RequestStack $requestStack, private CacheInterface $cache, private SliderRepository $sliderRepository,
        private PresentationRepository $presentationRepository, private FamilleRepository $familleRepository,
        private CategorieRepository $categorieRepository, private ProduitRepository $produitRepository,
        private GenreRepository $genreRepository
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

    public function cacheGenre(string $entityName, string $slug=null, bool $delete=false)
    {
        $cacheName = $entityName.$slug;
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item)use($slug){
            $item->expiresAfter(604800);
            if ($slug) return $this->genreRepository->findOneBy(['slug' => $slug]);
            else return $this->genreRepository->findAll();
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

    public function cacheProduit(string $entityName, string $slug=null, bool $delete=false)
    {
        $cacheName = $entityName.$slug;
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($slug){
            $item->expiresAfter(604800);
            if (!$slug) return $this->produitRepository->findAll();
            else return $this->produitRepository->findOneBy(['slug' => $slug]);
        });
    }

    public function cacheProduitBySlug(string $slug, bool $delete=false)
    {
        $cacheName = "article{$slug}";
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use($slug){
            $item->expiresAfter(604800);
            return $this->produitRepository->findBySlug($slug);
        });
    }

    public function cacheProduitByFamilleAndGenre(string $famille, string $genre=null, bool $delete=false)
    {
        $cacheName = "produit-{$famille}{$genre}";
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use($famille, $genre){
            $item->expiresAfter(604800);
            return $this->produitRepository->findByFamille($famille, $genre);
        });
    }

    public function cacheProduitByCategorie(string $categorie, bool $delete=false)
    {
        $cacheName ="produitbycategorie{$categorie}";
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use($categorie){
            $item->expiresAfter(604800);
            return $this->produitRepository->findByCategorie($categorie);
        });
    }

    public function cacheProduitByPromotion(bool $delete=null)
    {
        $cacheName = "produitPromotion";
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item){
            $item->expiresAfter(604800);
            return $this->produitRepository->findBy(['promotion' => true], ['niveau' => "DESC"]);
        });
    }

    // Verification de l'existence dans la base de données
    public function cacheGetFamille($string, bool $delete=false)
    {
        $cacheName = "getFamille{$string}";
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use($string){
            $item->expiresAfter(604800);
            return $this->familleRepository->findByStr($string);
        });
    }

    // Verification de l'existence du genre dans la base de données
    public function cacheGetGenre($string, bool $delete=false)
    {
        $cacheName= "getGenre{$string}";
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($string){
            $item->expiresAfter(604800);
            return $this->genreRepository->findByStr($string);
        });
    }

    public function cacheMenu(string $genre, bool $delete=false)
    {
        $cacheName = "menu{$genre}";
        if ($delete) $this->cache->delete($cacheName);

        return $this->cache->get($cacheName, function (ItemInterface $item) use($genre){
            $item->expiresAfter(604800);
            return $this->categorieRepository->findByGenreAndFamille($genre, 'vetement');
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