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
            $dados = $this->request->getData();

            if ( $dados['cutout_type'] === 'PRIMEIRA' || $dados['cutout_type'] === 'SEGUNDA' ) {
                $dados['atribui_cm_rs'] = "";
            } else {
                $dados['percent_ad_cm'] = 0;
            }
    
            // Verifica se store_code é um array
            if (!empty($dados['store_code']) && is_array($dados['store_code'])) {
                $registros = [];

                foreach ($dados['store_code'] as $storeCode) {
                    $storeCutoutCode = $this->StoreCutoutCodes->newEmptyEntity();

                    // Clona os dados para evitar sobreposição de valores
                    $registro = $dados;
                    $registro['store_code'] = $storeCode; // Define a loja para este registro
                    
                    // Regras condicionais para os campos
                    if ($registro['cutout_type'] === 'PRIMEIRA' || $registro['cutout_type'] === 'SEGUNDA') {
                        $registro['atribui_cm_rs'] = "";
                    } else {
                        $registro['percent_ad_cm'] = 0;
                    }

                    // Popula a entidade
                    $registros[] = $this->StoreCutoutCodes->patchEntity($storeCutoutCode, $registro);
                }

                // Salva todos os registros de uma vez
                if ($this->StoreCutoutCodes->saveMany($registros)) {
                    $this->Flash->success(__('Os códigos de corte foram salvos com sucesso.'));
                    return $this->redirect(['action' => 'index']);
                }

                $this->Flash->error(__('Erro ao salvar os registros. Tente novamente.'));
            } else {
                $this->Flash->error(__('Selecione pelo menos uma loja.'));
            }
        }
        $this->set(compact('storeCutoutCode'));
    }
    
    public function edit($id = null)
    {
        $storeCutoutCode = $this->StoreCutoutCodes->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dados = $this->request->getData();

            if ( $dados['cutout_type'] === 'PRIMEIRA' || $dados['cutout_type'] === 'SEGUNDA' ) {
                $dados['atribui_cm_rs'] = "";
            } else {
                $dados['percent_ad_cm'] = 0;
            }

            $storeCutoutCode = $this->StoreCutoutCodes->patchEntity($storeCutoutCode, $dados);
            if ($this->StoreCutoutCodes->save($storeCutoutCode)) {
                $this->Flash->success(__('The store cutout code has been saved.'));
    
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The store cutout code could not be saved. Please, try again.'));
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
