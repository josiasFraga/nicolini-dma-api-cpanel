<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Dma Controller
 *
 * @property \App\Model\Table\DmaTable $Dma
 * @method \App\Model\Entity\Dma[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class DmaController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $dma = $this->paginate($this->Dma);

        $this->set(compact('dma'));
    }

    /**
     * View method
     *
     * @param string|null $id Dma id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $dma = $this->Dma->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('dma'));
    }


    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $dma = $this->Dma->newEmptyEntity();
        if ($this->request->is('post')) {
            $dma = $this->Dma->patchEntity($dma, $this->request->getData());
            if ($this->Dma->save($dma)) {
                $this->Flash->success(__('The {0} has been saved.', 'Dma'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Dma'));
        }
        $this->set(compact('dma'));
    }


    /**
     * Edit method
     *
     * @param string|null $id Dma id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $dma = $this->Dma->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dma = $this->Dma->patchEntity($dma, $this->request->getData());
            if ($this->Dma->save($dma)) {
                $this->Flash->success(__('The {0} has been saved.', 'Dma'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Dma'));
        }
        $this->set(compact('dma'));
    }


    /**
     * Delete method
     *
     * @param string|null $id Dma id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $dma = $this->Dma->get($id);
        if ($this->Dma->delete($dma)) {
            $this->Flash->success(__('The {0} has been deleted.', 'Dma'));
        } else {
            $this->Flash->error(__('The {0} could not be deleted. Please, try again.', 'Dma'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
