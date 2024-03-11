<?php
declare(strict_types=1);

namespace App\Controller;

class LojasController extends AppController
{
    
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
    }

    public function index()
    {
        $jwtPayload = $this->request->getAttribute('jwtPayload');
        $this->loadModel('ApoUsuarioloja');

        $lojas = $this->ApoUsuarioloja
        ->find('all')
        ->where([
            'ApoUsuarioloja.Login' => $jwtPayload->sub,
            'not' => [
                'ApoUsuarioloja.Loja' => ''
            ]
        ])
        ->select([
            'ApoUsuarioloja.Loja'
        ])
        ->order([
            'ApoUsuarioloja.Loja'
        ])
        ->group([
            'ApoUsuarioloja.Loja'
        ])
        ->toArray();
    
        
        $this->set([
            'data' => $lojas,
            'status' => 'ok',
        ]);

        $this->viewBuilder()->setOption('serialize', ['data', 'status']);
    }


}