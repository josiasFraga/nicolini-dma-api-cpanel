<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;

class BakeryMapSellsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authorization.Authorization');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authorization->skipAuthorization();

        if (!$this->Authentication->getIdentity()) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        return true;
    }

    public function index()
    {
        $this->loadModel('DmaBakeryMapSells');
        $this->set('title', 'Produtos para Map de Vendas');

        $searchTerm = '';
        $query = $this->DmaBakeryMapSells
            ->find()
            ->contain(['Mercadorias'])
            ->order([
                'DmaBakeryMapSells.good_code' => 'ASC',
                'DmaBakeryMapSells.type' => 'ASC',
            ]);

        if ($this->request->is('post')) {
            $searchTerm = trim((string)$this->request->getData('table_search'));

            if ($searchTerm !== '') {
                $query
                    ->leftJoinWith('Mercadorias')
                    ->where([
                        'OR' => [
                            'DmaBakeryMapSells.good_code LIKE' => '%' . $searchTerm . '%',
                            'DmaBakeryMapSells.type LIKE' => '%' . $searchTerm . '%',
                            'Mercadorias.tx_descricao LIKE' => '%' . $searchTerm . '%',
                        ],
                    ])
                    ->distinct(['DmaBakeryMapSells.id']);
            }
        }

        $this->paginate = [
            'limit' => 20,
        ];

        $mapSells = $this->paginate($query);
        $this->set(compact('mapSells', 'searchTerm'));
    }

    public function add()
    {
        $this->loadModel('DmaBakeryMapSells');

        $mapSell = $this->DmaBakeryMapSells->newEmptyEntity();
        if ($this->request->is('post')) {
            $dados = $this->request->getData();
            $dados['good_code'] = str_pad((string)($dados['good_code'] ?? ''), 7, '0', STR_PAD_LEFT);

            $mapSell = $this->DmaBakeryMapSells->patchEntity($mapSell, $dados);
            if ($this->DmaBakeryMapSells->save($mapSell)) {
                $this->Flash->success(__('O produto para map de vendas foi salvo com sucesso.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Não foi possível salvar o produto para map de vendas. Verifique os dados e tente novamente.'));
        }

        $typeOptions = $this->getTypeOptions();
        $this->set(compact('mapSell', 'typeOptions'));
    }

    public function edit($id = null)
    {
        $this->loadModel('DmaBakeryMapSells');

        $mapSell = $this->DmaBakeryMapSells->get($id, [
            'contain' => ['Mercadorias'],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $dados = $this->request->getData();
            $dados['good_code'] = str_pad((string)($dados['good_code'] ?? ''), 7, '0', STR_PAD_LEFT);

            $mapSell = $this->DmaBakeryMapSells->patchEntity($mapSell, $dados);
            if ($this->DmaBakeryMapSells->save($mapSell)) {
                $this->Flash->success(__('O produto para map de vendas foi salvo com sucesso.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Não foi possível salvar o produto para map de vendas. Verifique os dados e tente novamente.'));
        }

        $typeOptions = $this->getTypeOptions();
        $this->set(compact('mapSell', 'typeOptions'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->loadModel('DmaBakeryMapSells');

        $mapSell = $this->DmaBakeryMapSells->get($id);
        if ($this->DmaBakeryMapSells->delete($mapSell)) {
            $this->Flash->success(__('O produto para map de vendas foi excluído com sucesso.'));
        } else {
            $this->Flash->error(__('Não foi possível excluir o produto para map de vendas.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    private function getTypeOptions(): array
    {
        return [
            'Primeira' => 'Primeira',
            'Segunda' => 'Segunda',
            'Osso e Pelanca' => 'Osso e Pelanca',
        ];
    }
}