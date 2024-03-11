<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Event\EventInterface;

class ExpectedYieldController extends AppController
{    public function beforeFilter(\Cake\Event\EventInterface $event)
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

        $store_code = $this->request->getQuery('store_code');

        if ( !$store_code ) {

            $this->set([
                'status' => 'erro',
                'message' => 'Código da loja não informado!'
            ]);

        }

        $expected_yields = $this->ExpectedYield->find('all')
        ->where([
            'ExpectedYield.store_code' => $store_code
        ])->toArray();

        $this->set([
            'status' => 'ok',
            'data' => $expected_yields
        ]);

        $this->viewBuilder()->setOption('serialize', ['data', 'status']); 
    }
}
