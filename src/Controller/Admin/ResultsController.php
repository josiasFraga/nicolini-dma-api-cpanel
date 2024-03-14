<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

use Cake\Event\EventInterface;

class ResultsController extends AppController
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

    public function isAuthorized($user)
    {
        // Permitir acesso a todas as ações para usuários logados
        return $this->Authentication->getIdentity() !== null;
    }

    public function index()
    {

        $dateAccounting = "";
        $storeCode = "";
        $dadosRelatorio = [];
    
        if ($this->request->is('post')) {

            $storeCode = $this->request->getData('store_code');
            $dateAccounting = $this->request->getData('date_accounting');

            $this->loadModel('StoreCutoutCodes');
            $this->loadModel('Dma');
            $this->loadModel('Mercadorias');
    
            // Exemplo de como começar a construir sua consulta
            // Isso precisará ser expandido e adaptado às suas necessidades específicas
            $query = $this->Dma->find()
                ->contain(['Mercadorias'])
                ->where([
                    'Dma.store_code' => $storeCode,
                    'Dma.date_accounting' => $dateAccounting
                ])
                ->group([
                    'Dma.id'
                ])
                ->toArray();

            foreach( $query as $key => $dma ){
                $dadosRelatorio[$dma['store_code']] = [
                    'total_saidas_kg' => 0,
                    'total_saidas_rs' => 0,
                    'total_entradas_kg' => 0,
                    'total_entradas_rs' => 0,
                    'diferenca_saidas_entradas_kg' => 0,
                    'diferenca_saidas_entradas_rs' => 0,
                    'rendimento_primeira' => 0,
                    'rendimento_segunda' => 0,
                    'rendimento_osso_pelanca' => 0,
                    'encerramento' => '2000-01-01',
                    'posicao_rank' => 1,
                ];

            }
            foreach( $query as $key => $dma ){

                $cutoutCodes = $this->StoreCutoutCodes->find()->where([
                    'StoreCutoutCodes.store_code' => $dma['store_code']
                ]);


                // Se o registro de DMA for do tipo saída
                if ( $dma['type'] == 'Saida' ) {
                    $dadosRelatorio[$dma['store_code']]['total_saidas_kg'] += $dma['quantity'];

                    if ( $dma['mercadoria']['opcusto'] == "M" ) {
                        $dadosRelatorio[$dma['store_code']]['total_saidas_rs'] += $dma['quantity'] * $dma['mercadoria']['customed'];
                    }
                    else {
                        $dadosRelatorio[$dma['store_code']]['total_saidas_rs'] += $dma['quantity'] * $dma['mercadoria']['custotab'];
                    }
                }
    
                // Se o registro de DMA for do tipo entrada
                else if ( $dma['type'] == 'Entrada' ) {

                    // Soma o total em kg das entradas
                    $dadosRelatorio[$dma['store_code']]['total_entradas_kg'] += $dma['quantity'];

                    // Busca o código correspondente ao produto de primeira da loja em questão
                    $primeCutCode = array_values(array_filter($cutoutCodes->toArray(), function($cc){
                        return $cc['cutout_type'] == 'PRIMEIRA';
                    }))[0]['cutout_code'];

                    // Busca o código correspondente ao produto de segunda da loja em questão
                    $secondCutCode = array_values(array_filter($cutoutCodes->toArray(), function($cc){
                        return $cc['cutout_type'] == 'SEGUNDA';
                    }))[0]['cutout_code'];

                    // Busca o código correspondente ao produto de osso e pelanca da loja em questão
                    $bonesAndSkinCutCode = array_values(array_filter($cutoutCodes->toArray(), function($cc){
                        return $cc['cutout_type'] == 'OSSO E PELANCA';
                    }))[0]['cutout_code'];

                    // Se o tipo de corte for de primeira
                    if ( $dma['cutout_type'] == 'Primeira' ) {

                        // Busca os dados do produto que corresponde ao código do produto de primeira da loja correspondente
                        $dados_primeira = $this->Mercadorias->find()
                        ->select([
                            'tx_descricao',
                            'customed',
                            'custotab',
                            'opcusto'
                        ])
                        ->where([
                            'Mercadorias.cd_codigoint' => str_pad($primeCutCode, 7, "0", STR_PAD_LEFT)
                        ])->first()
                        ->toArray();

                        if ( $dados_primeira['opcusto'] == "M" ) {
                            $dadosRelatorio[$dma['store_code']]['total_entradas_rs'] += $dma['quantity'] * $dados_primeira['customed'];
                        } else {
                            $dadosRelatorio[$dma['store_code']]['total_entradas_rs'] += $dma['quantity'] * $dados_primeira['custotab'];
                        }

                    }
                    // Se o tipo de corte for de segunda
                    else if ( $dma['cutout_type'] == 'Segunda' ) {

                        // Busca os dados do produto que corresponde ao código do produto de segunda da loja correspondente
                        $dados_segunda = $this->Mercadorias->find()
                        ->select([
                            'tx_descricao',
                            'customed',
                            'custotab',
                            'opcusto'
                        ])
                        ->where([
                            'Mercadorias.cd_codigoint' => str_pad($secondCutCode, 7, "0", STR_PAD_LEFT)
                        ])->first()
                        ->toArray();

                        if ( $dados_segunda['opcusto'] == "M" ) {
                            $dadosRelatorio[$dma['store_code']]['total_entradas_rs'] += $dma['quantity'] * $dados_segunda['customed'];
                        } else {
                            $dadosRelatorio[$dma['store_code']]['total_entradas_rs'] += $dma['quantity'] * $dados_segunda['custotab'];
                        }

                    }

                    // Se o tipo de corte for osso e pelanca
                    else if ( $dma['cutout_type'] == 'Osso e Pelanca' ) {

                        // Busca os dados do produto que corresponde ao código do produto de osso e pelanca da loja correspondente
                        $dados_osso_pelanca = $this->Mercadorias->find()
                        ->select([
                            'tx_descricao',
                            'customed',
                            'custotab',
                            'opcusto'
                        ])
                        ->where([
                            'Mercadorias.cd_codigoint' => str_pad($bonesAndSkinCutCode, 7, "0", STR_PAD_LEFT)
                        ])->first()
                        ->toArray();

                        if ( $dados_osso_pelanca['opcusto'] == "M" ) {
                            $dadosRelatorio[$dma['store_code']]['total_entradas_rs'] += $dma['quantity'] * $dados_osso_pelanca['customed'];
                        } else {
                            $dadosRelatorio[$dma['store_code']]['total_entradas_rs'] += $dma['quantity'] * $dados_osso_pelanca['custotab'];
                        }
                    }

                }

                $dadosRelatorio[$dma['store_code']]['diferenca_saidas_entradas_kg'] = $dadosRelatorio[$dma['store_code']]['total_saidas_kg']-$dadosRelatorio[$dma['store_code']]['total_entradas_kg'];
                $dadosRelatorio[$dma['store_code']]['diferenca_saidas_entradas_rs'] = $dadosRelatorio[$dma['store_code']]['total_saidas_rs']-$dadosRelatorio[$dma['store_code']]['total_entradas_rs'];

                
                debug($dma);

            }
            debug($dadosRelatorio);
            die();

    
        } 

        $this->set(compact(
            'dadosRelatorio',
            'storeCode',
            'dateAccounting'
        ));        

    }
}