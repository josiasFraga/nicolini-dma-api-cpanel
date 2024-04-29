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
        
        $status = 'ok';

        $mains = [222,208,203];

        $mercadorias = $this->Mercadorias->find('all')
        ->where([
            'Mercadorias.secao' => 17,
            'Mercadorias.grupo IN' => [258,259,261]
        ])
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
        ->limit(30000)
        ->toArray();

        foreach( $mercadorias as $key => $mercadoria ){
            $mercadorias[$key]['main'] = "N";

            if ( in_array((int)$mercadoria->cd_codigoint, $mains) ) {
                $mercadorias[$key]['main'] = "Y";
                
            }
        }

        
        $this->set([
            'status' => $status,
            'data' => $mercadorias
        ]);

        $this->viewBuilder()->setOption('serialize', ['status', 'data']);
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
