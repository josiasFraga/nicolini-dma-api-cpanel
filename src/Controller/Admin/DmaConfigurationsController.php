<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;

class DmaConfigurationsController extends AppController
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
        $dmaConfigurations = $this->paginate($this->DmaConfigurations);

        $this->set(compact('dmaConfigurations'));
    }

    public function edit($id = null)
    {
        $dmaConfiguration = $this->DmaConfigurations->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dmaConfiguration = $this->DmaConfigurations->patchEntity($dmaConfiguration, $this->request->getData());
            if ($this->DmaConfigurations->save($dmaConfiguration)) {
                $this->Flash->success(__('The {0} has been saved.', 'Dma Configuration'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Dma Configuration'));
        }
        $this->set(compact('dmaConfiguration'));
    }

}
