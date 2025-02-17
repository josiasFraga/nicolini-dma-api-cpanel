<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;


class BakeryMainProductsController extends AppController
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
		$this->loadModel('DmaBakeryMainGoods');

		$searchTerm = "";        

		if ($this->request->is('post')) {
			// Get the search term from the form data
			$searchTerm = $this->request->getData('table_search');

			// Perform the search query based on the search term
			$query = $this->DmaBakeryMainGoods
				->find()
				->where([
					'OR' => [
						
						'good_code LIKE' => '%' . $searchTerm . '%',
						'good_description LIKE' => '%' . $searchTerm . '%',
					]
				])
				->order(['good_description' => 'ASC']);
	
			$this->paginate = [
				'limit' => 20, // Set your desired limit per page
			];
	
			// Paginate the query before fetching the results
			$mercadorias = $this->paginate($query);
	
			// Pass the search results to the view
			$this->set(compact('mercadorias', 'searchTerm'));
		} else {
			// If the form has not been submitted, fetch all the service subcategories as usual
			$this->paginate = [
				// Pagination settings here
			];
			$mercadorias = $this->paginate($this->DmaBakeryMainGoods);
			$this->set(compact('mercadorias'));
		}

	}

	public function add()
	{
		$this->loadModel('DmaBakeryMainGoods');
		$dmaBakeryMainGood = $this->DmaBakeryMainGoods->newEmptyEntity();
		if ($this->request->is('post')) {
			$this->loadModel('Mercadorias');
			$dados = $this->request->getData();
			$dmaBakeryMainGood = $this->DmaBakeryMainGoods->patchEntity($dmaBakeryMainGood, $dados);// Campos para formatar
			$dados['good_code'] = str_pad((string)$dados['good_code'], 7, "0", STR_PAD_LEFT);
			// Verificar se o produto existe no model Mercadorias
			$mercadoria = $this->Mercadorias->find()
			->select(['cd_codigoint', 'tx_descricao'])
			->where(['cd_codigoint' => $dados['good_code']])
			->first();
			
			if ($mercadoria) {
				// Produto encontrado - preencher a descrição
				$dados['good_description'] = $mercadoria->tx_descricao;
	
				// Continuar com o patchEntity e salvar
				$dmaBakeryMainGood = $this->DmaBakeryMainGoods->patchEntity($dmaBakeryMainGood, $dados);

				if ($this->DmaBakeryMainGoods->save($dmaBakeryMainGood)) {
					$this->Flash->success(__('The {0} has been saved.', 'Dma Bakery Good'));
					return $this->redirect(['action' => 'index']);
				}
	
				$this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Dma Bakery Good'));
			} else {
				// Produto não encontrado - mostrar erro
				$this->Flash->error(__('Produto não encontrado na tabela Mercadorias. Verifique o código digitado.'));
			}
		
		}
		$this->set(compact('dmaBakeryMainGood'));
	}

	public function delete($id) {
		
        $this->request->allowMethod(['post', 'delete']);
		$this->loadModel('DmaBakeryMainGoods');
        $dmaBakeryMainGood = $this->DmaBakeryMainGoods->get($id);
        if ($this->DmaBakeryMainGoods->delete($dmaBakeryMainGood)) {
            $this->Flash->success(__('The {0} has been deleted.', 'Dma Bakery Main Goods'));
        } else {
            $this->Flash->error(__('The {0} could not be deleted. Please, try again.', 'Dma Bakery Good'));
        }

        return $this->redirect(['action' => 'index']);
	}

   
}
