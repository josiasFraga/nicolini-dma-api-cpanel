<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;
use Cake\I18n\FrozenTime;
use Cake\I18n\FrozenDate;

class DmaController extends AppController
{
	public function beforeFilter(\Cake\Event\EventInterface $event)
	{
		parent::beforeFilter($event);
		// Isso permite ações não autenticadas a serem acessadas sem autenticação
		$this->Authentication->addUnauthenticatedActions($this->getUnauthenticatedActions());
	
		// Isso isenta todas as actions deste controlador das verificações de autorização
		$this->Authorization->skipAuthorization();
	}

	protected function getUnauthenticatedActions() {
		return ['finish', 'nextDate', 'saveIncome', 'saveOutcome', 'loadOutcomes', 'loadIncomes', 'autoFinish', 'saveProduction', 'loadProductions', 'loadDiscrepancies', 'saveDiscrepancy', 'saveIncomeOutcome'];
	}

	public function finish()
	{
	
		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;
		$dados = json_decode($this->request->getData('dados'), true);
		//Log::debug(addslashes($this->request->getData('dados')));
		//die();

		$this->loadModel('Dma');
		$this->loadModel('Users');
		$this->loadModel('DmaConfigurations');
		$userStoreTable = TableRegistry::getTableLocator()->get('ApoUsuarioloja');

		$tolerancia_diferenca_pesos = $this->DmaConfigurations
		->find()
		->where([
			'DmaConfigurations.config_key' => 'weight_difference_margin'
		])
		->first();
		$tolerancia_diferenca_pesos = floatval($tolerancia_diferenca_pesos->config);

		$store_code = $dados['store_code'];
		$app_product_id = empty($dados['app_product_id']) ? 1 : $dados['app_product_id'];

		$entradas = [];
		$saidas = [];
		$producoes = [];
		$quebras = [];

		// Caso seja açougue
		if ( $app_product_id == 1 ) {

			$entradas = $this->Dma->find('all')
			->where([
				'Dma.ended' => 'N',
				//'Dma.user' => $userId,
				'Dma.type' => 'Entrada',
				'Dma.store_code' => $store_code
			])
			->toArray();
	
			$saidas = $this->Dma->find('all')
			->where([
				'Dma.ended' => 'N',
				//'Dma.user' => $userId,
				'Dma.type' => 'Saida',
				'Dma.store_code' => $store_code
			])
			->toArray();
	 
			if ( count($saidas) == 0 ) {
				return $this->jsonResponse('erro', 'Nenhuma saída informada.');
			}
		
			if ( count($entradas) == 0 ) {
				return $this->jsonResponse('erro', 'Nenhuma entrada informada.');
			}

			$quantities_in = array_map(function($dma) {
				return $dma->quantity;
			}, $entradas);
		
			$quantities_out = array_map(function($dma) {
				return $dma->quantity;
			}, $saidas);
	
			$total_kg_entradas = array_sum($quantities_in);
			$total_kg_saidas = array_sum($quantities_out);
	
			$diferenca_em_percent = $this->calcularDiferencaPercentual($total_kg_entradas, $total_kg_saidas);
	
			if ( $tolerancia_diferenca_pesos < $diferenca_em_percent ) {
				return $this->jsonResponse('erro', 'A diferença entre entradas e saídas não pode ser maior que '.$tolerancia_diferenca_pesos.'%, atualmente está em '.$diferenca_em_percent.'%');
			}

		}
		// Caso seja horti
		else if ( $app_product_id == 2 ) {

			$producoes = $this->Dma->find('all')
			->where([
				'Dma.ended' => 'N',
				//'Dma.user' => $userId,
				'Dma.type' => 'Producao',
				'Dma.store_code' => $store_code
			])
			->toArray();
	
			$quebras = $this->Dma->find('all')
			->where([
				'Dma.ended' => 'N',
				//'Dma.user' => $userId,
				'Dma.type' => 'Quebra',
				'Dma.store_code' => $store_code
			])
			->toArray();
	 
			if ( count($producoes) == 0 ) {
				return $this->jsonResponse('erro', 'Nenhuma produção informada.');
			}
		
			if ( count($quebras) == 0 ) {
				return $this->jsonResponse('erro', 'Nenhuma quebra informada.');
			}

		}
	
		$user = $this->Users->find()
		->where([
			'login' => $dados["user"]
		])
		->first();
	
		if (!$user || $user->pswd != md5($dados["password"])) {
			return $this->jsonResponse('erro', 'O usuário ou a senha informados são inválidos!');
		}

		$user_store = $userStoreTable->find()
		->where([
			'Login' => $dados["user"],
			'Loja' => $store_code
		])
		->first();

		if ( !$user_store ) {
			return $this->jsonResponse('erro', 'O usuário não tem acesso a loja informada!');
		}

		// Tratamento de datas
		$date_accounting = $this->calculateDateAccounting($store_code, $app_product_id);

		if ( !$date_accounting ) {
			return $this->jsonResponse('erro', 'Não é possível salvar lançamentos para depois de amanhã.');
		}
		
		return $this->finishDma([
			'entradas' => $entradas, 
			'saidas' => $saidas,
			'producoes' => $producoes, 
			'quebras' => $quebras
		], 'N', $dados["user"], $app_product_id);
		
	}

	public function nextDate()
	{
		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;
	
		$this->loadModel('Users');
		$this->loadModel('Dma');
	
		// Tratamento de datas
		$store_code = $this->request->getQuery('store_code');
		$app_product_id = $this->request->getQuery('app_product_id');
		$next_date = $this->calculateDateAccounting($store_code, $app_product_id);
	
		if ( !$next_date ) {
			return $this->jsonResponse('info', 'Lançamentos encerrados por hoje');
		}
	
		
		return $this->jsonResponse('ok', '', [], $next_date);
		
	}
	
	private function calculateDateAccounting($store_code, $product_id = 1)
	{
		$checkLastData = $this->Dma->find()
			->select(['Dma.date_accounting'])
			->where([
				'Dma.store_code' => $store_code, 
				'Dma.ended' => 'Y',
				'Dma.app_product_id' => $product_id
			])
			->order(['date_accounting DESC'])
			->first();
	
		if ($checkLastData) {
			if ($checkLastData->date_accounting->format('Y-m-d') == date('Y-m-d')) {
				return date('Y-m-d', strtotime('+1 day'));
			}
	
			if ($checkLastData->date_accounting->format('Y-m-d') == date('Y-m-d', strtotime('+1 day'))) {
				return false;
			}
		}
	
		return date('Y-m-d');
	}
	
	private function extractErrors($entities)
	{
		return array_filter(array_map(function ($entity) {
			return $entity->getErrors();
		}, $entities), function ($error) {
			return !empty($error);
		});
	}

	public function saveIncome() {
		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;
		$dados = json_decode($this->request->getData('dados'), true);
		//Log::debug($this->request->getData('dados'));

		$date_accounting = $this->calculateDateAccounting($dados['store_code']);

		if ( !$date_accounting ) {
			return $this->jsonResponse('erro', 'Não é possível salvar lançamentos para depois de amanhã.');
		}
		
		$dados['primeMeatKg'] = str_replace(',','.',str_replace('.','',$dados['primeMeatKg']));
		$dados['secondMeatKg'] = str_replace(',','.',str_replace('.','',$dados['secondMeatKg']));
		$dados['boneAndSkinKg'] = str_replace(',','.',str_replace('.','',$dados['boneAndSkinKg']));
		$dados['boneDiscardKg'] = str_replace(',','.',str_replace('.','',$dados['boneDiscardKg']));

		$saveIncomePrime = $this->saveIncomeRow($dados['store_code'], 'Primeira', $dados['primeMeatKg'], $date_accounting);
		$saveIncomeSecond = $this->saveIncomeRow($dados['store_code'], 'Segunda', $dados['secondMeatKg'], $date_accounting);
		$saveIncomeBoneAndSkin = $this->saveIncomeRow($dados['store_code'], 'Osso e Pelanca', $dados['boneAndSkinKg'], $date_accounting);
		$saveIncomeBoneDiscard = $this->saveIncomeRow($dados['store_code'], 'Osso a Descarte', $dados['boneDiscardKg'], $date_accounting);

		if ( $saveIncomePrime === false ){
			return $this->jsonResponse('erro', 'O código de recorte de primeira não foi configurado para a loja '.$dados['store_code'].'!');
		}

		if ( $saveIncomeSecond === false ){
			return $this->jsonResponse('erro', 'O código de recorte de segunda não foi configurado para a loja '.$dados['store_code'].'!');
		}

		if ( $saveIncomeBoneAndSkin === false ){
			return $this->jsonResponse('erro', 'O código de recorte de osso e pelanca não foi configurado para a loja '.$dados['store_code'].'!');
		}

		if ( $saveIncomeBoneDiscard === false ){
			return $this->jsonResponse('erro', 'O código de recorte de osso a descarte não foi configurado para a loja '.$dados['store_code'].'!');
		}
		
		return $this->jsonResponse('ok', 'Entrada cadastrada com sucesso!');
	}

	private function saveIncomeRow($store_code, $label, $kg, $date_accounting){

		if ( $kg == 0 ) {
			return true;
		}
		
		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;

		$this->loadModel('StoreCutoutCodes');
		$this->loadModel('Dma');

		$dados_recorte = $this->StoreCutoutCodes->find()
		->where([
			'StoreCutoutCodes.store_code' => $store_code,
			'StoreCutoutCodes.cutout_type' => strtoupper($label)
		])
		->first()
		->toArray();

		if ( count($dados_recorte) === 0 ) {
			return false;
		}

		$good_code = $dados_recorte['cutout_code'];

		$check_registered = $this->Dma->find('all')
		->where([
			'app_product_id' => 1,
			'store_code' => $store_code,
			'user' => $userId,
			'good_code' => str_pad($good_code, 7, "0", STR_PAD_LEFT),
			'date_accounting' => $date_accounting,
			'cutout_type' => $label,
			'ended' => 'N'
		])
		->first();

		if ( $check_registered ) {
			$dados_salvar = $check_registered;

			$dados_salvar->quantity += $kg;

			if ( $dados_salvar->quantity <= 0 ) {
				$this->Dma->delete($check_registered);
				return true;
			}

			$dma = $dados_salvar;

		} else {

			if ( $kg < 0) {
				return true;
			}

			$dados_salvar=[
				'store_code' => $store_code,
				'app_product_id' => 1,
				'quantity' => $kg,
				'good_code' => str_pad($good_code, 7, "0", STR_PAD_LEFT),
				'type' => 'Entrada',
				'user' => $userId,
				'date_movement' => date('Y-m-d'),
				'cutout_type' => $label,
				'date_accounting' => $date_accounting,
				'ended' => 'N'
			];
			$dma = $this->Dma->newEntity($dados_salvar);
		}

		return $this->Dma->save($dma);

	}

	public function saveProduction() {
		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;
		$dados = json_decode($this->request->getData('dados'), true);
		$app_product_id = $dados['app_product_id'];
		//Log::debug(addslashes($this->request->getData('dados')));
		//die();

		$date_accounting = $this->calculateDateAccounting($dados['store_code'], $app_product_id);

		if ( !$date_accounting ) {
			return $this->jsonResponse('erro', 'Não é possível salvar lançamentos para depois de amanhã.');
		}

		$dados['kg'] = str_replace(',','.',str_replace('.','',$dados['kg']));

		if ( $dados['kg'] == 0 ) {
			return $this->jsonResponse('ok', 'Produção cadastrada com sucesso!');
		}
		
		$dados['kg'] = (float)$dados['kg'];

		if ( $dados['kg'] < 0 ) {
			return $this->removeQuantity(2, $dados['store_code'], $userId, 'Producao', $dados['goodCode'], $dados['kg']);
		}

		$dados_salvar = [
			'app_product_id' => $app_product_id,
			'store_code' => $dados['store_code'],
			'date_movement' => date('Y-m-d'),
			'date_accounting' => $date_accounting,
			'cost' => $dados['good']['opcusto'] === 'M' ? floatval($dados['good']['customed']) : floatval($dados['good']['custotab']),
			'user' => $userId,
			'type' => 'Producao',
			'quantity' => $dados['kg'],
			'good_code' => str_pad($dados['goodCode'], 7, "0", STR_PAD_LEFT),
			'ended' => 'N',
		];        
	  
		$dmaEntity = $this->Dma->newEmptyEntity();
		$dmaEntity = $this->Dma->patchEntity($dmaEntity, $dados_salvar);
	
		// Tentar salvar e capturar erros
		try {
			if (!$this->Dma->save($dmaEntity)) {
				// Inclua erros de validação no retorno, se existirem
				$validationErrors = $dmaEntity->getErrors();
				return $this->jsonResponse(
					'erro',
					'Erro ao salvar a produção.',
					$validationErrors
				);
			}
		} catch (\Exception $e) {
			// Retorne o erro SQL ou qualquer outro erro
			return $this->jsonResponse(
				'erro',
				'Erro ao salvar a produção. Detalhes do erro: ' . $e->getMessage()
			);
		}

		
		return $this->jsonResponse('ok', 'Produção cadastrada com sucesso!');
	}

	public function saveDiscrepancy() {
		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;
		$dados = json_decode($this->request->getData('dados'), true);
		$app_product_id = $dados['app_product_id'];
		//Log::debug(addslashes($this->request->getData('dados')));
		//die();

		$date_accounting = $this->calculateDateAccounting($dados['store_code'], $app_product_id);

		if ( !$date_accounting ) {
			return $this->jsonResponse('erro', 'Não é possível salvar lançamentos para depois de amanhã.');
		}

		$dados['kg'] = str_replace(',','.',str_replace('.','',$dados['kg']));

		if ( $dados['kg'] == 0 ) {
			return $this->jsonResponse('ok', 'Quebra cadastrada com sucesso!');
		}
		
		$dados['kg'] = (float)$dados['kg'];

		if ( $dados['kg'] < 0 ) {
			return $this->removeQuantity(2, $dados['store_code'], $userId, 'Quebra', $dados['goodCode'], $dados['kg']);
		}

		$dados_salvar = [
			'app_product_id' => $app_product_id,
			'store_code' => $dados['store_code'],
			'date_movement' => date('Y-m-d'),
			'date_accounting' => $date_accounting,
			'cost' => $dados['good']['opcusto'] === 'M' ? floatval($dados['good']['customed']) : floatval($dados['good']['custotab']),
			'user' => $userId,
			'type' => 'Quebra',
			'quantity' => $dados['kg'],
			'good_code' => str_pad($dados['goodCode'], 7, "0", STR_PAD_LEFT),
			'ended' => 'N',
		];        
	  
		$dmaEntity = $this->Dma->newEmptyEntity();
		$dmaEntity = $this->Dma->patchEntity($dmaEntity, $dados_salvar);
	
		// Tentar salvar e capturar erros
		try {
			if (!$this->Dma->save($dmaEntity)) {
				// Inclua erros de validação no retorno, se existirem
				$validationErrors = $dmaEntity->getErrors();
				return $this->jsonResponse(
					'erro',
					'Erro ao salvar a quebra.',
					$validationErrors
				);
			}
		} catch (\Exception $e) {
			// Retorne o erro SQL ou qualquer outro erro
			return $this->jsonResponse(
				'erro',
				'Erro ao salvar a quebra. Detalhes do erro: ' . $e->getMessage()
			);
		}

		
		return $this->jsonResponse('ok', 'Quebra cadastrada com sucesso!');
	}

	private function removeQuantity($app_product_id = 1, $store_code, $userId, $type, $good_code, $quantity = 0) {
		$qtd_diminuir = abs($quantity);

		$dados_lancados = $this->Dma->find('all')
		->where([
			'Dma.app_product_id' => $app_product_id,
			'Dma.store_code' => $store_code,
			'Dma.ended' => 'N',
			'Dma.user' => $userId,
			'Dma.type' => $type,
			'Dma.good_code' => str_pad($good_code, 7, "0", STR_PAD_LEFT),
		])->toArray();

		if ( count($dados_lancados) === 0 ) {
			return $this->jsonResponse('ok', 'Ajuste cadastrado com sucesso!');
		}

		foreach( $dados_lancados as $key => $lancado) {

			$qtd_lancamento = $lancado->quantity;
			$new_qtd_lancamento = $qtd_lancamento-$qtd_diminuir;

			if ( $new_qtd_lancamento <= 0 ) {
				$this->Dma->delete($lancado);
				$qtd_diminuir -= $qtd_lancamento;
			} else {
				$lancado->quantity = $new_qtd_lancamento;
				$this->Dma->save($lancado);
			}

		}

		return $this->jsonResponse('ok', 'Ajuste cadastrado com sucesso!');

	}

	public function saveOutcome() {

		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;
		$dados = json_decode($this->request->getData('dados'), true);
		//Log::debug($this->request->getData('dados'));
		
		$this->loadModel('Dma');

		$date_accounting = $this->calculateDateAccounting($dados['store_code']);

		if ( !$date_accounting ) {
			return $this->jsonResponse('erro', 'Não é possível salvar lançamentos para depois de amanhã.');
		}

		$dados['kg'] = str_replace(',','.',str_replace('.','',$dados['kg']));

		if ( $dados['kg'] == 0 ) {
			return $this->jsonResponse('ok', 'Saída cadastrada com sucesso!');
		}

		$dados['kg'] = (float)$dados['kg'];

		$dados_lancados = $this->Dma->find('all')
		->where([
			'Dma.app_product_id' => 1,
			'Dma.store_code' => $dados['store_code'],
			'Dma.ended' => 'N',
			'Dma.user' => $userId,
			'Dma.type' => 'Saida',
			'Dma.good_code' => str_pad($dados['goodCode'], 7, "0", STR_PAD_LEFT),
		])->toArray();

		if ( $dados['kg'] < 0 ) {
			return $this->removeQuantity(1, $dados['store_code'], $userId, 'Saida', $dados['goodCode'], $dados['kg']);
		}

		if ( count($dados_lancados) > 0 ) {

			$dados_salvar = $dados_lancados[0];
			$dados_salvar['quantity']+= $dados['kg'];
			$dma = $dados_salvar;

		} else {
			$dados_salvar=[
				'app_product_id' => 1,
				'store_code' => $dados['store_code'],
				'quantity' => $dados['kg'],
				'good_code' => str_pad($dados['goodCode'], 7, "0", STR_PAD_LEFT),
				'type' => 'Saida',
				'user' => $userId,
				'date_movement' => date('Y-m-d'),
				'date_accounting' => $date_accounting,
				'ended' => 'N'
			];
			$dma = $this->Dma->newEntity($dados_salvar);
		}


		if ( $this->Dma->save($dma) ) {
			return $this->jsonResponse('ok', 'Saída cadastrada com sucesso!');
		} else {
			$errors = $dma->getErrors();
			return $this->jsonResponse('erro', 'Erro ao cadastrar a saída!', $errors);
		}

	}

	public function saveIncomeOutcome() {

		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;
		$dados = json_decode($this->request->getData('dados'), true);
		Log::debug($this->request->getData('dados'));
		
		$this->loadModel('Dma');

		$date_accounting = $this->calculateDateAccounting($dados['store_code']);

		if ( !$date_accounting ) {
			return $this->jsonResponse('erro', 'Não é possível salvar lançamentos para depois de amanhã.');
		}

		if ( count($dados) === 0 ) {
			return $this->jsonResponse('erro', 'Nenhum dado informado para salvar!');
		}

		foreach( $dados['dados'] AS $key => $dado ) {
			$dado['kg'] = str_replace(',','.',str_replace('.','',$dado['kg']));
			$dado['kg'] = (float)$dado['kg'];

			if ( $dado['kg'] < 0 ) {
				$this->removeQuantity(3, $dados['store_code'], $userId, $dado['tipo'], $dado['goodCode'], $dado['kg']);
			} else {

				$dados_salvar=[
					'app_product_id' => 3,
					'store_code' => $dados['store_code'],
					'quantity' => $dado['kg'],
					'good_code' => str_pad($dado['goodCode'], 7, "0", STR_PAD_LEFT),
					'type' => $dado['tipo'],
					'user' => $userId,
					'date_movement' => date('Y-m-d'),
					'date_accounting' => $date_accounting,
					'ended' => 'N'
				];

				$dma = $this->Dma->newEntity($dados_salvar);

				if ( !$this->Dma->save($dma) ) {
					$errors = $dma->getErrors();
					return $this->jsonResponse('erro', 'Erro ao cadastrar a entrada/saida!', $errors);
				}

			}

		}
		
		return $this->jsonResponse('ok', 'Saída cadastrada com sucesso!');

	}

	public function loadIncomes() {

		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;        
		$store_code = $this->request->getQuery('store_code');
		$app_product_id = $this->request->getQuery('app_product_id');

		if ( !$store_code ) {

			$this->set([
				'status' => 'erro',
				'message' => 'Código da loja não informado ao buscar as entradas!'
			]);
		}

		if ( !$app_product_id ) {
			$app_product_id = 1; // Default para açougue
		}

		if ( $app_product_id == 1 ) {
			$entradas_retornar = $this->loadButcherIncomes($userId, $store_code);
		} else if ( $app_product_id == 3 ) {
			$entradas_retornar = $this->loadBakeryIncomes($userId, $store_code);
		} else {
			$entradas_retornar = [];
		}


		return $this->jsonResponse('ok', '', [], $entradas_retornar);
	}

	private function loadBakeryIncomes($userId, $store_code) {

		$this->loadModel('Dma');
		$this->loadModel('Mercadorias');
		
		$entradas = $this->Dma->find('all')
		->where([
			'store_code' => $store_code,
			'user' => $userId,
			'ended' => 'N',
			'type' => 'Entrada',
			'app_product_id' => 3
		])
		->toArray();

		$entradas_retornar = [];
		if ( count($entradas) > 0 ) {
			foreach( $entradas as $key => $entrada){
				$item = [
					'kg' => number_format($entrada['quantity'], 3, ',', ''),
					'goodCode' => $entrada['good_code'],
					'store_code' => $store_code,
					'good' => $this->Mercadorias->find('all')
					->where([
						'Mercadorias.cd_codigoint' => str_pad($entrada['good_code'], 7, "0", STR_PAD_LEFT)
					])
					->first()
				];

				$entradas_retornar[] = $item;
			}
		}

		return $entradas_retornar;

	}

	private function loadButcherIncomes($userId, $store_code) {

		$this->loadModel('Dma');
		
		$total_entradas_primeira = $this->Dma->find('all')
		->select([
			'cutout_type',
			'total_quantity' => $this->Dma->find()->func()->sum('quantity')
		])
		->where([
			'store_code' => $store_code,
			'user' => $userId,
			'ended' => 'N',
			'type' => 'Entrada',
			'cutout_type' => 'Primeira'
		])
		->group(['cutout_type'])
		->enableAutoFields(true)
		->first();

		$total_entradas_segunda = $this->Dma->find('all')
		->select([
			'cutout_type',
			'total_quantity' => $this->Dma->find()->func()->sum('quantity')
		])
		->where([
			'store_code' => $store_code,
			'user' => $userId,
			'ended' => 'N',
			'type' => 'Entrada',
			'cutout_type' => 'Segunda'
		])
		->group(['cutout_type'])
		->enableAutoFields(true)
		->first();

		$total_entradas_osso_e_pelanca = $this->Dma->find('all')
		->select([
			'cutout_type',
			'total_quantity' => $this->Dma->find()->func()->sum('quantity')
		])
		->where([
			'store_code' => $store_code,
			'user' => $userId,
			'ended' => 'N',
			'type' => 'Entrada',
			'cutout_type' => 'Osso e Pelanca'
		])
		->group(['cutout_type'])
		->enableAutoFields(true)
		->first();

		$total_entradas_osso_a_descarte = $this->Dma->find('all')
		->select([
			'cutout_type',
			'total_quantity' => $this->Dma->find()->func()->sum('quantity')
		])
		->where([
			'store_code' => $store_code,
			'user' => $userId,
			'ended' => 'N',
			'type' => 'Entrada',
			'cutout_type' => 'Osso a Descarte'
		])
		->group(['cutout_type'])
		->enableAutoFields(true)
		->first();

		$total_primeira = $total_entradas_primeira ? $total_entradas_primeira->toArray() : ['total_quantity' => 0];
		$total_segunda = $total_entradas_segunda ? $total_entradas_segunda->toArray() : ['total_quantity' => 0];
		$total_osso_e_pelanca = $total_entradas_osso_e_pelanca ? $total_entradas_osso_e_pelanca->toArray() : ['total_quantity' => 0];
		$total_osso_a_descarte = $total_entradas_osso_a_descarte ? $total_entradas_osso_a_descarte->toArray() : ['total_quantity' => 0];

		$entradas_retornar[] = [
			"primeMeatKg" => number_format($total_primeira['total_quantity'], 3, ',', ''),
			"secondMeatKg" => number_format($total_segunda['total_quantity'], 3, ',', ''),
			"boneAndSkinKg" => number_format($total_osso_e_pelanca['total_quantity'], 3, ',', ''),
			"boneDiscardKg" => number_format($total_osso_a_descarte['total_quantity'], 3, ',', ''),
			'store_code' => $store_code
		];

		return $entradas_retornar;

	}

	public function loadOutcomes() {

		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;        
		$store_code = $this->request->getQuery('store_code');
		$app_product_id = $this->request->getQuery('app_product_id');

		if ( !$store_code ) {

			$this->set([
				'status' => 'erro',
				'message' => 'Código da loja não informado ao buscar as saidas!'
			]);
		}

		if ( !$app_product_id ) {
			$app_product_id = 1; // Default para açougue
		}

		if ( $app_product_id == 1 ) {
			$saidas_retornar = $this->loadButcherOutcomes($userId, $store_code);
		} else if ( $app_product_id == 3 ) {
			$saidas_retornar = $this->loadBakeryOutcomes($userId, $store_code);
		} else {
			$saidas_retornar = [];
		}


		return $this->jsonResponse('ok', '', [], $saidas_retornar);
	}

	private function loadButcherOutcomes($userId, $store_code) {
		
		$this->loadModel('Dma');
		$this->loadModel('Mercadorias');

		$saidas = $this->Dma->find('all')
		->where([
			'store_code' => $store_code,
			'user' => $userId,
			'ended' => 'N',
			'type' => 'Saida',
			'app_product_id' => 1
		])->toArray();

		$saidas_retornar = [];
		if ( count($saidas) > 0 ) {
			foreach( $saidas as $key => $saida){
				$item = [
					'kg' => number_format($saida['quantity'], 3, ',', ''),
					'goodCode' => $saida['good_code'],
					'store_code' => $store_code,
					'good' => $this->Mercadorias->find('all')
					->where([
						'Mercadorias.cd_codigoint' => str_pad($saida['good_code'], 7, "0", STR_PAD_LEFT)
					])
					->first()
				];

				$saidas_retornar[] = $item;
			}
		}

		return $saidas_retornar;

	}

	private function loadBakeryOutcomes($userId, $store_code) {

		$this->loadModel('Dma');
		
		$saidas = $this->Dma->find('all')
		->where([
			'store_code' => $store_code,
			'user' => $userId,
			'ended' => 'N',
			'type' => 'Saida',
			'app_product_id' => 3
		])
		->toArray();

		$saidas_retornar = [];
		if ( count($saidas) > 0 ) {
			foreach( $saidas as $key => $saida){
				$item = [
					'kg' => number_format($saida['quantity'], 3, ',', ''),
					'goodCode' => $saida['good_code'],
					'store_code' => $store_code,
					'good' => $this->Mercadorias->find('all')
					->where([
						'Mercadorias.cd_codigoint' => str_pad($saida['good_code'], 7, "0", STR_PAD_LEFT)
					])
					->first()
				];

				$saidas_retornar[] = $item;
			}
		}

		return $saidas_retornar;

	}

	public function loadProductions() {

		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;        
		$store_code = $this->request->getQuery('store_code');
		$app_product_id = empty($this->request->getQuery('app_product_id')) ? 1 : $this->request->getQuery('app_product_id');

		if ( !$store_code ) {

			$this->set([
				'status' => 'erro',
				'message' => 'Código da loja não informado ao buscar as produções!'
			]);
		}

		$this->loadModel('Dma');
		$this->loadModel('Mercadorias');

		$producoes = $this->Dma->find('all')
		->where([
			'app_product_id' => $app_product_id,
			'store_code' => $store_code,
			'user' => $userId,
			'ended' => 'N',
			'type' => 'Producao'
		])->toArray();

		$dados_retornar = [];
		if ( count($producoes) > 0 ) {
			foreach( $producoes as $key => $producao){
				$item = [
					'id' => $producao['id'],
					'kg' => number_format($producao['quantity'], 3, ',', ''),
					'goodCode' => $producao['good_code'],
					'cost' => $producao['cost'],
					'store_code' => $store_code,
					'good' => $this->Mercadorias->find('all')
					->where([
						'Mercadorias.cd_codigoint' => str_pad($producao['good_code'], 7, "0", STR_PAD_LEFT)
					])
					->first()
				];

				$dados_retornar[] = $item;
			}
		}


		return $this->jsonResponse('ok', '', [], $dados_retornar);
	}

	public function loadDiscrepancies() {

		$jwtPayload = $this->request->getAttribute('jwtPayload');
		$userId = $jwtPayload->sub;        
		$store_code = $this->request->getQuery('store_code');
		$app_product_id = empty($this->request->getQuery('app_product_id')) ? 1 : $this->request->getQuery('app_product_id');

		if ( !$store_code ) {

			$this->set([
				'status' => 'erro',
				'message' => 'Código da loja não informado ao buscar as produções!'
			]);
		}

		$this->loadModel('Dma');
		$this->loadModel('Mercadorias');

		$quebras = $this->Dma->find('all')
		->where([
			'app_product_id' => $app_product_id,
			'store_code' => $store_code,
			'user' => $userId,
			'ended' => 'N',
			'type' => 'Quebra'
		])->toArray();

		$dados_retornar = [];
		if ( count($quebras) > 0 ) {
			foreach( $quebras as $key => $quebra){
				$item = [
					'id' => $quebra['id'],
					'kg' => number_format($quebra['quantity'], 3, ',', ''),
					'goodCode' => $quebra['good_code'],
					'cost' => $quebra['cost'],
					'store_code' => $store_code,
					'good' => $this->Mercadorias->find('all')
					->where([
						'Mercadorias.cd_codigoint' => str_pad($quebra['good_code'], 7, "0", STR_PAD_LEFT)
					])
					->first()
				];

				$dados_retornar[] = $item;
			}
		}


		return $this->jsonResponse('ok', '', [], $dados_retornar);
	}

	public function autoFinish() {
		
		$token = $this->request->getQuery('token');

		if ( $token != "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJ1c2VyMSIsIm5hbWUiOiJKb2huIERvZSIsImlhdCI6MTUxNjIzOTAyMn0.ZjR16uQfmIXOzQdXNl5_eQShjOU2vAyL9apQmIR-4hM" ) {
			return $this->jsonResponse('erro', 'Requisição inválida!');
		}

		$this->loadModel('Dma');

		$dmas_nao_finalizados = $this->Dma->find('all')
		->where([
			'Dma.ended' => 'N',
			'Dma.date_accounting' => date('Y-m-d')
		])
		->group([
			'Dma.store_code',
			'Dma.app_product_id'
		])->toArray();

		if ( count($dmas_nao_finalizados) > 0 ) {

			foreach ($dmas_nao_finalizados as $dma ) {

				if ( $dma['app_product_id'] == 1 ) {

					$entradas = $this->Dma->find('all')
					->where([
						'Dma.ended' => 'N',
						'Dma.type' => 'Entrada',
						'Dma.store_code' => $dma['store_code']
					])
					->toArray();
	
					$saidas = $this->Dma->find('all')
					->where([
						'Dma.ended' => 'N',
						'Dma.type' => 'Saida',
						'Dma.store_code' => $dma['store_code']
					])
					->toArray();
			
					if ( count($saidas) == 0 ) {
						return $this->jsonResponse('erro', 'Nenhuma saída informada.');
					}
				
					if ( count($entradas) == 0 ) {
						return $this->jsonResponse('erro', 'Nenhuma entrada informada.');
					}
	
					$this->finishDma([
						'entradas' => $entradas, 
						'saidas' => $saidas
					], 'Y', 'Sistema', $dma['app_product_id']);

				} else if ( $dma['app_product_id'] == 2 ) {

					$producoes = $this->Dma->find('all')
					->where([
						'Dma.ended' => 'N',
						'Dma.type' => 'Producao',
						'Dma.store_code' => $dma['store_code']
					])
					->toArray();

					$quebras = $this->Dma->find('all')
					->where([
						'Dma.ended' => 'N',
						'Dma.type' => 'Quebra',
						'Dma.store_code' => $dma['store_code']
					])
					->toArray();

					$this->finishDma([
						'producoes' => $producoes, 
						'quebras' => $quebras
					], 'Y', 'Sistema', $dma['app_product_id']);

				}

			}

		}
		

		return $this->jsonResponse('ok', 'DMAs finalizados com sucesso!');

	}

	private function finishDma ($dados, $ended_by_cron, $ended_by, $app_product_id = 1) {

		$entradas = !empty($dados['entradas']) ? $dados['entradas'] : [];
		$saidas = !empty($dados['saidas']) ? $dados['saidas'] : [];
		$producoes = !empty($dados['producoes']) ? $dados['producoes'] : [];
		$quebras = !empty($dados['quebras']) ? $dados['quebras'] : [];
	
		$this->loadModel('Dma');

		if ( $app_product_id == 1 ) {
			return $this->finishDmaButcher($entradas, $saidas, $ended_by_cron, $ended_by);
		} else if ( $app_product_id == 2 ) {
			return $this->finishDmaProduceSection($producoes, $quebras, $ended_by_cron, $ended_by);
		}
		
	}

	private function finishDmaButcher($entradas, $saidas, $ended_by_cron, $ended_by) {
	
		$this->loadModel('Mercadorias');
		$this->loadModel('Dma');
	
		$custo_saidas_total = 0;
		$peso_saidas_total = 0;

		foreach( $saidas as $key => $saida ){

			$good_code = $saida['good_code'];
			$quantity = $saida['quantity'];
   
			$dados_mercadoria = $this->Mercadorias->find('all')
			->where([
				'Mercadorias.cd_codigoint' => str_pad($good_code, 7, "0", STR_PAD_LEFT)
			])
			->first();

			if ( !$dados_mercadoria ) {
				return $this->jsonResponse('erro', 'Dados da mercadoria '.$good_code.' não encontrados!');
			}

			$dados_mercadoria = $dados_mercadoria->toArray();

			$custo_total = 0;
			if ( $dados_mercadoria['opcusto'] == "M" ) {
				$custo_total = $saida['quantity'] * $dados_mercadoria['customed'];
			} else {
				$custo_total = $saida['quantity'] * $dados_mercadoria['custotab'];
			}

			$custo_saidas_total += $custo_total;
			$peso_saidas_total += $saida['quantity'];
		}

		$custo_saidas_medio = $custo_saidas_total/$peso_saidas_total;
		$peso_entradas_total = 0;
		$custo_entradas_total = 0;
	
		// Calcula os totais
		foreach( $entradas as $key => $entrada ){

			$good_code = $saida['good_code'];
			$quantity = $saida['quantity'];
			$date_accounting = $saida['date_accounting'];
			$dateString = date('Y-m-d');
			$dateToday = new FrozenDate($dateString);

			if ($date_accounting->gt($dateToday)) {
				return $this->jsonResponse('erro', 'Você não pode finalizar lançamentos de amanhã.');
			}
   
			$dados_mercadoria = $this->Mercadorias->find('all')
			->where([
				'Mercadorias.cd_codigoint' => str_pad($good_code, 7, "0", STR_PAD_LEFT)
			])
			->first();

			if ( !$dados_mercadoria ) {
				return $this->jsonResponse('erro', 'Dados da mercadoria '.$good_code.' não encontrados!');
			}

			$dados_mercadoria = $dados_mercadoria->toArray();

			$entradas[$key]['good'] = $dados_mercadoria;

			$custo_total = 0;
			if ( $dados_mercadoria['opcusto'] == "M" ) {
				$custo_total = $entrada['quantity'] * $dados_mercadoria['customed'];
			} else {
				$custo_total = $entrada['quantity'] * $dados_mercadoria['custotab'];
			}

			$custo_entradas_total += $custo_total;
			$peso_entradas_total += $entrada['quantity'];
		}

		$calculos_entradas = [];

		$this->loadModel('StoreCutoutCodes');
	
		// Calcula a representatividade
		foreach( $entradas as $key => $entrada ){

			$good_code = $entrada['good_code'];
			$store_code = $entrada['store_code'];
			$label = $entrada['cutout_type'];
			$quantity = $entrada['quantity'];
			$date_accounting = $entrada['date_accounting'];
			$dateString = date('Y-m-d');
			$dateToday = new FrozenDate($dateString);

			if ($date_accounting->gt($dateToday)) {
				return $this->jsonResponse('erro', 'Você não pode finalizar lançamentos de amanhã.');
			}

			$this->loadModel('Dma');
	
			$dados_recorte = $this->StoreCutoutCodes->find()
			->where([
				'StoreCutoutCodes.store_code' => $store_code,
				'StoreCutoutCodes.cutout_type' => strtoupper($label)
			])
			->first()
			->toArray();

			$calculos_entradas[$entrada['cutout_type']]['representatividade'] = (100*$quantity)/$peso_entradas_total;
			$calculos_entradas[$entrada['cutout_type']]['kg'] = $quantity;

			$calculos_entradas[$entrada['cutout_type']]['_cutout_data'] = $dados_recorte;

		}

		$total_saidas_prev = 0;
		foreach( $calculos_entradas as $key => $calculo ){

			if ( $key == 'Osso e Pelanca') {
				if ( $calculo['_cutout_data']['atribui_cm_rs'] == 'CM' ) {
					$calculos_entradas[$key]['custo_total_prev'] = $custo_saidas_medio;
				} else {
					$calculos_entradas[$key]['custo_total_prev'] = $calculo['_cutout_data']['atribui_cm_rs'] * $calculo['kg'];
				}
			} else {
				$calculos_entradas[$key]['custo_total_prev'] = $custo_saidas_total*($calculo['representatividade']/100);
			}

			$total_saidas_prev += $calculos_entradas[$key]['custo_total_prev'];
		}

		$dif_total_saidas_x_total_entradas_prev = $custo_saidas_total-$total_saidas_prev;


		
		foreach( $calculos_entradas as $key => $calculo ){
			if ( $key === 'Primeira' ) {
				$percentage_to_sum = $calculo['_cutout_data']['percent_ad_cm']/100;
				$value_to_sum = $dif_total_saidas_x_total_entradas_prev * $percentage_to_sum;
				$calculos_entradas[$key]['custo_total'] = $calculos_entradas[$key]['custo_total_prev'] + $value_to_sum;
				$calculos_entradas[$key]['custo_medio'] = $calculos_entradas[$key]['custo_total']/$calculos_entradas[$key]['kg'];
			}
			else if ( $key === 'Segunda' ) {
				$percentage_to_sum = $calculo['_cutout_data']['percent_ad_cm']/100;
				$value_to_sum = $dif_total_saidas_x_total_entradas_prev * $percentage_to_sum;
				$calculos_entradas[$key]['custo_total'] = $calculos_entradas[$key]['custo_total_prev'] + $value_to_sum;
				$calculos_entradas[$key]['custo_medio'] = $calculos_entradas[$key]['custo_total']/$calculos_entradas[$key]['kg'];
			} else {
				
				if ( $calculo['_cutout_data']['atribui_cm_rs'] == 'CM' ) {
					$calculos_entradas[$key]['custo_medio'] = $custo_saidas_medio;
				} else {
					$calculos_entradas[$key]['custo_medio'] = $calculo['_cutout_data']['atribui_cm_rs'];
				}

			}
  
   
		}

		$novas_entradas = [];
		foreach( $entradas as $key => $entrada ){

			$novas_entradas[] = [
				'id' => $entrada['id'],
				'cost' => $calculos_entradas[$entrada['cutout_type']]['custo_medio'],
				'ended' => 'Y',
				'ended_by_cron' => $ended_by_cron,
				'ended_by' => $ended_by
			];
		}

		$novas_saidas = [];
		foreach( $saidas as $key => $saida ){

			$novas_saidas[] = [
				'id' => $saida['id'],
				'ended' => 'Y',
				'ended_by_cron' => $ended_by_cron,
				'ended_by' => $ended_by
			];
		}

		$dmaEntities = $this->Dma->patchEntities(array_merge($entradas, $saidas), array_merge($novas_entradas, $novas_saidas));
	
		if ($this->Dma->saveMany($dmaEntities)) {
			return $this->jsonResponse('ok', 'DMA finalizado com sucesso!');
		} else {

			$errors = $dmaEntities->getErros();
			return $this->jsonResponse('erro', 'Ocorreu um erro ao finalizar o DMA.', $errors);
		}
	}

	private function finishDmaProduceSection($producoes, $quebras, $ended_by_cron, $ended_by) {
	
		$this->loadModel('Mercadorias');
		$this->loadModel('Dma');

		$dateToday = new FrozenDate(date('Y-m-d'));
	
		$dmaEntities = [];
	
		// Processar Produções
		foreach ($producoes as $producao) {
			$date_accounting = $producao['date_accounting'];
			
			if ($date_accounting->gt($dateToday)) {
				return $this->jsonResponse('erro', 'Você não pode finalizar lançamentos de amanhã.');
			}
	
			$dmaEntities[] = $this->Dma->patchEntity($this->Dma->get($producao['id']), [
				'ended' => 'Y',
				'ended_by_cron' => $ended_by_cron,
				'ended_by' => $ended_by,
			]);
		}
	
		// Processar Quebras
		foreach ($quebras as $quebra) {
			$date_accounting = $quebra['date_accounting'];
	
			if ($date_accounting->gt($dateToday)) {
				return $this->jsonResponse('erro', 'Você não pode finalizar lançamentos de amanhã.');
			}
	
			$dmaEntities[] = $this->Dma->patchEntity($this->Dma->get($quebra['id']), [
				'ended' => 'Y',
				'ended_by_cron' => $ended_by_cron,
				'ended_by' => $ended_by,
			]);
		}
	
		// Salvar Entidades
		if ($this->Dma->saveMany($dmaEntities)) {
			return $this->jsonResponse('ok', 'DMA finalizado com sucesso!');
		} else {
			$errors = $dmaEntities->getErros();
			return $this->jsonResponse('erro', 'Ocorreu um erro ao finalizar o DMA.', $errors);
		}
	}
	
	private function calcularDiferencaPercentual($valorOriginal, $novoValor) {
		if ($valorOriginal == 0) {
			return "Erro: O valor original não pode ser zero.";
		}
		
		$diferenca = $novoValor - $valorOriginal;
		$percentual = ($diferenca / $valorOriginal) * 100;

		$percentual = abs($percentual);
		
		return $percentual;
	}
	
}
