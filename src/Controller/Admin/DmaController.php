<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;


class DmaController extends AppController
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

            // Attempt to convert search term to English date format if it matches Brazilian date format
            if (substr_count($searchTerm, "/") === 2) {
                $searchTerm = date('Y-m-d', strtotime(str_replace('/', '-', $searchTerm)));
            }
    
            // Perform the search query based on the search term
            $query = $this->Dma
                ->find()
                ->where([
                    'OR' => [
                        'Dma.store_code LIKE' => '%' . $searchTerm . '%',
                        'Dma.date_movement LIKE' => '%' . $searchTerm . '%',
                        'Dma.date_accounting LIKE' => '%' . $searchTerm . '%',
                        'Dma.user LIKE' => '%' . $searchTerm . '%',
                        'Dma.type LIKE' => '%' . $searchTerm . '%',
                        'Dma.cutout_type LIKE' => '%' . $searchTerm . '%',
                        'Dma.good_code LIKE' => '%' . $searchTerm . '%',
                        'Dma.quantity LIKE' => '%' . $searchTerm . '%',
                    ]
                ]);
    
            $this->paginate = [
                'limit' => 20, // Set your desired limit per page
            ];
    
            // Paginate the query before fetching the results
            $dma = $this->paginate($query);

            if (substr_count($searchTerm, "-") === 2) {
                $searchTerm = date('d/m/Y', strtotime($searchTerm));
            }
    
            // Pass the search results to the view
            $this->set(compact('dma', 'searchTerm'));
        } else {
            // If the form has not been submitted, fetch all the service subcategories as usual
            $this->paginate = [
                // Pagination settings here
            ];
            $dma = $this->paginate($this->Dma);
            $this->set(compact('dma'));
        }
    }

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
