<?php

namespace App\Services;

use App\Repository\SliderRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AllRepositoty
{
    public function __construct(
        private RequestStack $requestStack, private CacheInterface $cache, private SliderRepository $sliderRepository
    )
    {
    }

    public function cache(string $entityName, bool $delete=false, bool $backend=false)
    {
        if ($delete) $this->cache->delete($entityName);

        return $this->cache->get($entityName, function (ItemInterface $item) use($entityName, $backend){
            $item->expiresAfter(6048000);
            $repository = "{$entityName}Repository";
            if ($backend) $resultat = $this->$repository->findAll();
            else $resultat = $this->$repository->findBy(['statut' => true]);

            return $resultat;
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