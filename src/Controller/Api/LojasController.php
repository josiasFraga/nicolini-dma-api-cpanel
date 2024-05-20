<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Event\EventInterface;


class LojasController extends AppController
{

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // Isso permite ações não autenticadas a serem acessadas sem autenticação
        $this->Authentication->addUnauthenticatedActions($this->getUnauthenticatedActions());
    
        // Isso isenta todas as actions deste controlador das verificações de autorização
        $this->Authorization->skipAuthorization();
    }

    protected function getUnauthenticatedActions() {
        return ['index'];
    }

    public function index()
    {
        $this->Authorization->skipAuthorization();
        $this->loadModel('ApoUsuarioloja');

        $lojas = $this->ApoUsuarioloja
        ->find('all')
        ->where([
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