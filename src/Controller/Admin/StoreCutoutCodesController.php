<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;

class StoreCutoutCodesController extends AppController
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

        $this->set('title', 'Códigos de recortes');

        if ($this->request->is('post')) {
            // Get the search term from the form data
            $searchTerm = $this->request->getData('table_search');
    
            // Perform the search query based on the search term
            $query = $this->StoreCutoutCodes
                ->find()
                ->where([
                    'OR' => [
                        'StoreCutoutCodes.store_code LIKE' => '%' . $searchTerm . '%',
                        'StoreCutoutCodes.cutout_code LIKE' => '%' . $searchTerm . '%',
                        'StoreCutoutCodes.cutout_type LIKE' => '%' . $searchTerm . '%',
                    ]
                ]);
    
            $this->paginate = [
                'limit' => 20, // Set your desired limit per page
            ];
    
            // Paginate the query before fetching the results
            $storeCutoutCodes = $this->paginate($query);
    
            // Pass the search results to the view
            $this->set(compact('storeCutoutCodes', 'searchTerm'));
        } else {
            // If the form has not been submitted, fetch all the service subcategories as usual
            $this->paginate = [
                // Pagination settings here
            ];
            $storeCutoutCodes = $this->paginate($this->StoreCutoutCodes);
            $this->set(compact('storeCutoutCodes'));
        }
    }


    public function view($id = null)
    {
        $storeCutoutCode = $this->StoreCutoutCodes->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('storeCutoutCode'));
    }


    public function add()
    {
        $storeCutoutCode = $this->StoreCutoutCodes->newEmptyEntity();
        if ($this->request->is('post')) {
            $storeCutoutCode = $this->StoreCutoutCodes->patchEntity($storeCutoutCode, $this->request->getData());
            if ($this->StoreCutoutCodes->save($storeCutoutCode)) {
                $this->Flash->success(__('The {0} has been saved.', 'Store Cutout Code'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Store Cutout Code'));
        }
        $this->set(compact('storeCutoutCode'));
    }

    public function edit($id = null)
    {
        $storeCutoutCode = $this->StoreCutoutCodes->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $storeCutoutCode = $this->StoreCutoutCodes->patchEntity($storeCutoutCode, $this->request->getData());
            if ($this->StoreCutoutCodes->save($storeCutoutCode)) {
                $this->Flash->success(__('The {0} has been saved.', 'Store Cutout Code'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Store Cutout Code'));
        }
        $this->set(compact('storeCutoutCode'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $storeCutoutCode = $this->StoreCutoutCodes->get($id);
        if ($this->StoreCutoutCodes->delete($storeCutoutCode)) {
            $this->Flash->success(__('The {0} has been deleted.', 'Store Cutout Code'));
        } else {
            $this->Flash->error(__('The {0} could not be deleted. Please, try again.', 'Store Cutout Code'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
