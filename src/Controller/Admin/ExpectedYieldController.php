<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * ExpectedYield Controller
 *
 * @property \App\Model\Table\ExpectedYieldTable $ExpectedYield
 * @method \App\Model\Entity\ExpectedYield[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ExpectedYieldController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $expectedYield = $this->paginate($this->ExpectedYield);

        $this->set(compact('expectedYield'));
    }

    /**
     * View method
     *
     * @param string|null $id Expected Yield id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $expectedYield = $this->ExpectedYield->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('expectedYield'));
    }


    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $expectedYield = $this->ExpectedYield->newEmptyEntity();
        if ($this->request->is('post')) {
            $expectedYield = $this->ExpectedYield->patchEntity($expectedYield, $this->request->getData());
            if ($this->ExpectedYield->save($expectedYield)) {
                $this->Flash->success(__('The {0} has been saved.', 'Expected Yield'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Expected Yield'));
        }
        $this->set(compact('expectedYield'));
    }


    /**
     * Edit method
     *
     * @param string|null $id Expected Yield id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $expectedYield = $this->ExpectedYield->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $expectedYield = $this->ExpectedYield->patchEntity($expectedYield, $this->request->getData());
            if ($this->ExpectedYield->save($expectedYield)) {
                $this->Flash->success(__('The {0} has been saved.', 'Expected Yield'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Expected Yield'));
        }
        $this->set(compact('expectedYield'));
    }


    /**
     * Delete method
     *
     * @param string|null $id Expected Yield id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $expectedYield = $this->ExpectedYield->get($id);
        if ($this->ExpectedYield->delete($expectedYield)) {
            $this->Flash->success(__('The {0} has been deleted.', 'Expected Yield'));
        } else {
            $this->Flash->error(__('The {0} could not be deleted. Please, try again.', 'Expected Yield'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
