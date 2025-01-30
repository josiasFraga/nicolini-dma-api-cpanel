<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;


class ProduceSectionMainProductsController extends AppController
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
		$this->loadModel('DmaProduceSectionMainGoods');

		$searchTerm = "";        

		if ($this->request->is('post')) {
			// Get the search term from the form data
			$searchTerm = $this->request->getData('table_search');

			// Perform the search query based on the search term
			$query = $this->DmaProduceSectionMainGoods
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
			$mercadorias = $this->paginate($this->DmaProduceSectionMainGoods);
			$this->set(compact('mercadorias'));
		}

	}

	public function add()
	{
		$this->loadModel('DmaProduceSectionMainGoods');
		$dmaProduceSectionMainGood = $this->DmaProduceSectionMainGoods->newEmptyEntity();
		if ($this->request->is('post')) {
			$this->loadModel('Mercadorias');
			$dados = $this->request->getData();
			$dmaProduceSectionMainGood = $this->DmaProduceSectionMainGoods->patchEntity($dmaProduceSectionMainGood, $dados);// Campos para formatar
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
				$dmaProduceSectionMainGood = $this->DmaProduceSectionMainGoods->patchEntity($dmaProduceSectionMainGood, $dados);

				if ($this->DmaProduceSectionMainGoods->save($dmaProduceSectionMainGood)) {
					$this->Flash->success(__('The {0} has been saved.', 'Dma Produce Section Good'));
					return $this->redirect(['action' => 'index']);
				}
	
				$this->Flash->error(__('The {0} could not be saved. Please, try again.', 'Dma Produce Section Good'));
			} else {
				// Produto não encontrado - mostrar erro
				$this->Flash->error(__('Produto não encontrado na tabela Mercadorias. Verifique o código digitado.'));
			}
		
		}
		$this->set(compact('dmaProduceSectionMainGood'));
	}

	public function delete($id) {
		
        $this->request->allowMethod(['post', 'delete']);
		$this->loadModel('DmaProduceSectionMainGoods');
        $dmaProduceSectionMainGood = $this->DmaProduceSectionMainGoods->get($id);
        if ($this->DmaProduceSectionMainGoods->delete($dmaProduceSectionMainGood)) {
            $this->Flash->success(__('The {0} has been deleted.', 'Dma Produce Section Main Goods'));
        } else {
            $this->Flash->error(__('The {0} could not be deleted. Please, try again.', 'Dma Produce Section Good'));
        }

        return $this->redirect(['action' => 'index']);
	}

   
}
