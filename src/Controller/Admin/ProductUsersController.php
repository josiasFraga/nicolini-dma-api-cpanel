<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;


class ProductUsersController extends AppController
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
        $this->set('title', 'Acesso de Usuários');
        $this->loadModel('AppProductUsers');
    
        $searchTerm = '';
        $query = $this->AppProductUsers->find('all')
            ->contain(['AppProducts']) // Inclui a associação com AppProducts
            ->order(['AppProductUsers.created' => 'DESC']);
    
        if ($this->request->is('post')) {
            $searchTerm = $this->request->getData('table_search');
    
            // Se for uma data no formato brasileiro, converte para o formato americano
            if (substr_count($searchTerm, "/") === 2) {
                $searchTerm = date('Y-m-d', strtotime(str_replace('/', '-', $searchTerm)));
            }
    
            // Adiciona condições de busca
            $query = $query->where([
                'OR' => [
                    'AppProductUsers.user_login LIKE' => '%' . $searchTerm . '%',
                    'AppProducts.name LIKE' => '%' . $searchTerm . '%',
                    'AppProductUsers.created LIKE' => '%' . $searchTerm . '%',
                ]
            ]);
        }
    
        $this->paginate = [
            'limit' => 20
        ];
    
        $list = $this->paginate($query);
    
        $this->set(compact('list', 'searchTerm'));
    }

    public function add()
    {
        $this->loadModel('AppProducts');
        $this->loadModel('AppProductUsers');
        $this->loadModel('Users');
        $appProductUsers = $this->AppProductUsers->newEmptyEntity();
    
        if ($this->request->is('post')) {
            $dados = $this->request->getData();
    
            // Pegue os IDs dos produtos selecionados
            $productIds = $dados['app_product_id'];
            unset($dados['app_product_id']); // Remova o campo do array principal
    
            $saved = true; // Para verificar se todos os registros foram salvos
    
            foreach ($productIds as $productId) {
                $dados['app_product_id'] = $productId; // Atribua o ID atual do produto
                $appProductUser = $this->AppProductUsers->patchEntity($this->AppProductUsers->newEmptyEntity(), $dados);
                
                if (!$this->AppProductUsers->save($appProductUser)) {
                    $saved = false; // Marque como falha se algum registro não for salvo
                    break;
                }
            }
    
            if ($saved) {
                $this->Flash->success(__('Permissões de acesso adicionadas com sucesso!'));
                return $this->redirect(['action' => 'index']);
            }
    
            $this->Flash->error(__('Não foi possível adicionar todas as permissões. Por favor, tente novamente.'));
        }
    
        // Lista de usuários com login como chave
        $users = $this->Users->find()
            ->select([
                'Users.login', // Chave
                'Users.name'   // Valor
            ])
            ->where([
                'Users.active' => 'Y'
            ])
            ->order([
                'Users.name'
            ])
            ->toArray();
    
        // Lista de produtos com id como chave
        $products = $this->AppProducts->find()
            ->select([
                'AppProducts.id',   // Chave
                'AppProducts.name'  // Valor
            ])
            ->order([
                'AppProducts.name'
            ])
            ->toArray();
    
        $this->set(compact('appProductUsers', 'users', 'products'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->loadModel('AppProductUsers');
        $permission = $this->AppProductUsers->get($id);
        if ($this->AppProductUsers->delete($permission)) {
            $this->Flash->success(__('The {0} has been deleted.', 'Dma'));
        } else {
            $this->Flash->error(__('The {0} could not be deleted. Please, try again.', 'Dma'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
