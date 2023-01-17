<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;

class AllRepositoty
{
    public function __construct(
        private RequestStack $requestStack
    )
    {
    }

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