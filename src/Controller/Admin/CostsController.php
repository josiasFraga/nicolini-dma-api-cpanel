<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;


class CostsController extends AppController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authorization.Authorization');
        //$this->Authorization->authorize(new PromocaoPolicy());
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        //$this->Authentication->addUnauthenticatedActions(['login']); // Ação de login não requer autenticação
        $this->Authorization->skipAuthorization();

        if (!$this->Authentication->getIdentity()) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        return true;
    }

    public function index()
    {
        $this->loadModel('Mercadorias');

        $conditions = [
            'Mercadorias.secao' => 17,
            'Mercadorias.grupo IN' => [258,259,261],
            'geraconsumo' => '0'
        ];

        $searchTerm = "";
    

        if ($this->request->is('post')) {
            // Get the search term from the form data
            $searchTerm = $this->request->getData('table_search');

            $conditions['OR'] = [
                'cd_codigoint' => $searchTerm,
                'tx_descricao' => $searchTerm,
                'MercadoriasLojas.loja' => $searchTerm,
   
            ];      
        }

        $mercadorias = $this->Mercadorias->find('all')
        ->where($conditions)
        ->select([
            'cd_codigoean',
            'cd_codigoint',
            'tx_descricao',
            'cd_unidade',
            'bl_controle_validade',
            'qt_dias_validade',
            'customed',
            'custotab',
            'opcusto',
            'MercadoriasLojas.loja'
        ])
        ->group(['MercadoriasLojas.loja','cd_codigoint'])
        ->leftJoin(
            ['MercadoriasLojas' => 'wms_mercadorias_lojas'],
            ['Mercadorias.cd_codigoint = MercadoriasLojas.codigoint']
        )
        ->andWhere([
            'OR' => [
                'MercadoriasLojas.ltmix' => 'A',
                'AND' => [
                    'OR' => [
                        'MercadoriasLojas.ltmix' => 'R',
                        'MercadoriasLojas.ltmix' => 'S'
                    ],
                    'MercadoriasLojas.estatual >' => 0
                ]
            ]
        ])
        ->toArray();

        $this->set(compact('mercadorias', 'searchTerm'));

    }

   
}
