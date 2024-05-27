<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;

class ExpectedYieldController extends AppController
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
            $query = $this->ExpectedYield
                ->find()
                ->where([
                    'OR' => [
                        'ExpectedYield.store_code LIKE' => '%' . $searchTerm . '%',
                        'ExpectedYield.good_code LIKE' => '%' . $searchTerm . '%',
                        'ExpectedYield.description LIKE' => '%' . $searchTerm . '%',
                    ]
                ]);
    
            $this->paginate = [
                'limit' => 20, // Set your desired limit per page
            ];
    
            // Paginate the query before fetching the results
            $expectedYield = $this->paginate($query);
    
            // Pass the search results to the view
            $this->set(compact('expectedYield', 'searchTerm'));
        } else {
            // If the form has not been submitted, fetch all the service subcategories as usual
            $this->paginate = [
                // Pagination settings here
            ];
            $expectedYield = $this->paginate($this->ExpectedYield);
            $this->set(compact('expectedYield'));
        }

    }



    public function add()
    {
        $expectedYield = $this->ExpectedYield->newEmptyEntity();
        if ($this->request->is('post')) {
            $dados = $this->request->getData();
            $fieldsToFormat = ['prime', 'second', 'bones_skin', 'bones_discard'];

            $soma = 0;
    
            foreach ($fieldsToFormat as $field) {
                if (isset($dados[$field])) {
                    $value = $dados[$field];
                    // Caso haja "." e "," nos valores, remove o "." e substitui a "," por "."
                    if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
                        $value = str_replace('.', '', $value);
                        $value = str_replace(',', '.', $value);
                    } elseif (strpos($value, ',') !== false) {
                        // Caso haja somente ",", substitui por "."
                        $value = str_replace(',', '.', $value);
                    }
                    // Atualiza o valor ajustado
                    $dados[$field] = $value;
                    $soma += $value;
                }
            }

            if ( $soma > 100 ) {
                $this->Flash->error(__('O somatório das expectativas não pode passar de 100. Total='.$soma, 'Expected Yield'));
            } else {

                $expectedYield = $this->ExpectedYield->patchEntity($expectedYield, $dados);// Campos para formatar
    
                if ($this->ExpectedYield->save($expectedYield)) {
                    $this->Flash->success(__('The {0} has been saved.', 'Expected Yield'));
    
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Expected Yield'));

            }
        }
        $this->set(compact('expectedYield'));
    }



    public function edit($id = null)
    {
        $expectedYield = $this->ExpectedYield->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dados = $this->request->getData();
            $fieldsToFormat = ['prime', 'second', 'bones_skin', 'bones_discard'];
            $soma = 0;
            foreach ($fieldsToFormat as $field) {
                if (isset($dados[$field])) {
                    $value = $dados[$field];
                    // Caso haja "." e "," nos valores, remove o "." e substitui a "," por "."
                    if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
                        $value = str_replace('.', '', $value);
                        $value = str_replace(',', '.', $value);
                    } elseif (strpos($value, ',') !== false) {
                        // Caso haja somente ",", substitui por "."
                        $value = str_replace(',', '.', $value);
                    }
                    // Atualiza o valor ajustado
                    $dados[$field] = $value;
                    $soma += $value;
                }
            }

            if ( $soma > 100 ) {
                $this->Flash->error(__('O somatório das expectativas não pode passar de 100. Total='.$soma, 'Expected Yield'));
            } else {
                $expectedYield = $this->ExpectedYield->patchEntity($expectedYield, $dados);// Campos para formatar
                if ($this->ExpectedYield->save($expectedYield)) {
                    $this->Flash->success(__('The {0} has been saved.', 'Expected Yield'));
    
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Expected Yield'));
            }
        }
        $this->set(compact('expectedYield'));
    }


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
