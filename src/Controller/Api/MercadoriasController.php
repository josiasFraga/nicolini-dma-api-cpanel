<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Event\EventInterface;


class MercadoriasController extends AppController
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

        $store_code = $this->request->getQuery('store_code');
        $app_product_code = $this->request->getQuery('app_product_code');

        if ( !$store_code ) {

            $this->set([
                'status' => 'erro',
                'message' => 'Código da loja não informado!'
            ]);

        }

        if ( empty($app_product_code) ) {
            $app_product_code = 1;
        }
        
        $status = 'ok';

        $mercadorias = $this->getProducts($app_product_code, $store_code);

        
        $this->set([
            'status' => $status,
            'data' => $mercadorias
        ]);

        $this->viewBuilder()->setOption('serialize', ['status', 'data']);
    }

    private function getProducts($app_product_code, $store_code) {

        $conditions = [];

        if ( $app_product_code == 1 ) {// Açougue

            $this->loadModel('ExpectedYield');
    
            $mains = $this->ExpectedYield->find('list', [
                'keyField' => 'id',
                'valueField' => 'good_code'
            ])
            ->where(['main' => 'Y'])
            ->toArray();

            $conditions = [
                'Mercadorias.secao' => 17,
                'Mercadorias.grupo IN' => [258,259,261],
                'MercadoriasLojas.loja' => $store_code,
                'geraconsumo' => '0'
            ];

            $additional_conditions = [
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
            ];

        } else if ( $app_product_code == 2 ) {//Horti

            $conditions = [
                'Mercadorias.secao IN' => [27, 41, 43, 44, 45],
                'Mercadorias.geraconsumo' => '0',
                'MercadoriasLojas.loja' => $store_code,
                'MercadoriasLojas.ltmix !=' => 'S'
            ];

            $additional_conditions = [];

            $this->loadModel('DmaProduceSectionMainGoods');
    
            $mains = array_values($this->DmaProduceSectionMainGoods->find('list', [
                'keyField' => 'id',
                'valueField' => 'good_code'
            ])
            ->toArray());

        } else if ( $app_product_code == 3 ) {//Padaria

            $conditions = [
                'Mercadorias.secao IN' => [21],
                'MercadoriasLojas.loja' => $store_code,
            ];

            $additional_conditions = [];

            $this->loadModel('DmaBakeryMainGoods');
    
            $mains = array_values($this->DmaBakeryMainGoods->find('list', [
                'keyField' => 'id',
                'valueField' => 'good_code'
            ])
            ->toArray());

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
            'opcusto'
        ])
        ->group('cd_codigoint')
        ->leftJoin(
            ['MercadoriasLojas' => 'wms_mercadorias_lojas'],
            ['Mercadorias.cd_codigoint = MercadoriasLojas.codigoint']
        )
        //->andWhere($additional_conditions)
        //->limit(1000)
        ->toArray();

        if ( !empty($mains) ) {
            foreach( $mercadorias as $key => $mercadoria ){
                $mercadorias[$key]['main'] = "N";
    
                if ( in_array((int)$mercadoria->cd_codigoint, $mains) ) {
                    $mercadorias[$key]['main'] = "Y";
                }
            }
        }

        return $mercadorias;

    }

    /**
     * View method
     *
     * @param string|null $id Mercadoria id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $mercadoria = $this->Mercadorias->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('mercadoria'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $mercadoria = $this->Mercadorias->newEmptyEntity();
        if ($this->request->is('post')) {
            $mercadoria = $this->Mercadorias->patchEntity($mercadoria, $this->request->getData());
            if ($this->Mercadorias->save($mercadoria)) {
                $this->Flash->success(__('The mercadoria has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mercadoria could not be saved. Please, try again.'));
        }
        $this->set(compact('mercadoria'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Mercadoria id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $mercadoria = $this->Mercadorias->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $mercadoria = $this->Mercadorias->patchEntity($mercadoria, $this->request->getData());
            if ($this->Mercadorias->save($mercadoria)) {
                $this->Flash->success(__('The mercadoria has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mercadoria could not be saved. Please, try again.'));
        }
        $this->set(compact('mercadoria'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Mercadoria id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $mercadoria = $this->Mercadorias->get($id);
        if ($this->Mercadorias->delete($mercadoria)) {
            $this->Flash->success(__('The mercadoria has been deleted.'));
        } else {
            $this->Flash->error(__('The mercadoria could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
