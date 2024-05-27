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
        $this->Authentication->allowUnauthenticated(['public']); // Ação de login não requer autenticação
        $this->Authorization->skipAuthorization();

        if (!$this->Authentication->getIdentity() && !in_array($this->request->getParam('action'), ['public'])) {
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

        $dateAccounting = date('d/m/Y');
        $dadosRelatorio = [];
        for ($i = 1; $i <= 18; $i++) {
            $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
        }
        $selectedStoreCodes = $storeCodes;
    
        if ($this->request->is('post')) {

            $selectedStoreCodes = $this->request->getData('store_codes');
            $dateAccounting = $this->request->getData('date_accounting');

            $this->loadModel('StoreCutoutCodes');
            $this->loadModel('Dma');
            $this->loadModel('Mercadorias');
            $this->loadModel('ExpectedYield');
    
            $query = $this->Dma->find()
                ->contain(['Mercadorias'])
                ->where([
                    'Dma.store_code IN' => $selectedStoreCodes,
                    'Dma.date_accounting' => $dateAccounting
                ])
                ->group([
                    'Dma.id'
                ])
                ->toArray();

            foreach( $query as $key => $dma ){
                
                $storeCode = $dma['store_code'];
                if (!isset($dadosRelatorio[$storeCode])) {
                
                    $dadosRelatorio[$storeCode] = [
                        'total_saidas_kg' => 0,
                        'total_saidas_rs' => 0,
                        'total_entradas_kg' => 0,
                        'total_entradas_rs' => 0,
                        'diferenca_saidas_entradas_kg' => 0,
                        'diferenca_saidas_entradas_rs' => 0,
                        'rendimento_esperado_primeira' => 0,
                        'rendimento_esperado_segunda' => 0,
                        'rendimento_esperado_osso_pelanca' => 0,
                        'rendimento_esperado_osso_descarte' => 0,
                        'rendimento_executado_primeira' => 0,
                        'rendimento_executado_segunda' => 0,
                        'rendimento_executado_osso_pelanca' => 0,
                        'rendimento_executado_osso_descarte' => 0,
                        'rendimento_dif_primeira' => 0,
                        'rendimento_dif_segunda' => 0,
                        'rendimento_dif_osso_pelanca' => 0,
                        'rendimento_dif_osso_a_descarte' => 0,
                        'custo_med_primeira' => 0,
                        'custo_med_segunda' => 0,
                        'custo_med_osso_pelanca' => 0,
                        'custo_med_osso_descarte' => 0,
                        'encerramento' => '2000-01-01',
                        'base_calc_rank' => 0,
                        'posicao_rank' => 1,
                        'loja' => $storeCode,
                        'finalizado_por' => ''
                    ];
                }

                $cutoutCodes = $this->StoreCutoutCodes->find()->where([
                    'StoreCutoutCodes.store_code' => $storeCode
                ]);

                $tipo_dma = $dma['type'];
                $dma_qtd = $dma['quantity'];
                $dma_cost = $dma['cost'];

                if ( $dma['ended'] === 'N' ) {
                    $dadosRelatorio[$storeCode]['finalizado_por'] = 'em andamento';
                } else {
                    $dadosRelatorio[$storeCode]['finalizado_por'] = $dma['ended_by'];
                }

                // Se o registro de DMA for do tipo saída
                if ( $tipo_dma == 'Saida' ) {
        
                    $dadosRelatorio[$storeCode]['total_saidas_kg'] += $dma_qtd;
                    $valor_mercadoria = 0;
    
                    if ( $dma['mercadoria']['opcusto'] == "M" ) {
                        $valor_mercadoria = $dma['mercadoria']['customed'];
                    }
                    else {
                        $valor_mercadoria = $dma['mercadoria']['custotab'];
                    }
                    
                    $dadosRelatorio[$storeCode]['total_saidas_rs'] += $dma_qtd * $valor_mercadoria;

                    $espectativa = $this->ExpectedYield->find()
                    ->where([
                        'ExpectedYield.good_code' => floatVal($dma['good_code'])
                    ])
                    ->first();

                    if ( $espectativa ) {                        
                        $espectativa = $espectativa->toArray();
                    } else {                        

                        $espectativa['prime'] = 0;
                        $espectativa['second'] = 0;
                        $espectativa['bones_skin'] = 0;
                        $espectativa['bones_discard'] = 0;
                    }

                    $espectativa_primeira_porc = $espectativa['prime'] ? $espectativa['prime']/100 : 0;
                    $espectativa_segunda_porc = $espectativa['second'] ? $espectativa['second']/100 : 0;
                    $espectativa_osso_pelanca_porc = $espectativa['bones_skin'] ? $espectativa['bones_skin']/100 : 0;
                    $espectativa_osso_descarte_porc = $espectativa['bones_discard'] && $espectativa['bones_discard'] > 0 ? $espectativa['bones_discard']/100 : 0;

                    $espectativa_primeira = $dma_qtd * $espectativa_primeira_porc;
                    $espectativa_segunda = $dma_qtd * $espectativa_segunda_porc;
                    $espectativa_osso_pelanca = $dma_qtd * $espectativa_osso_pelanca_porc;
                    $espectativa_osso_descarte = $dma_qtd * $espectativa_osso_descarte_porc;

                    $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] += $espectativa_primeira * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] += $espectativa_segunda * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'] += $espectativa_osso_pelanca * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['rendimento_esperado_osso_descarte'] += $espectativa_osso_descarte * $valor_mercadoria;

                }
    
                // Se o registro de DMA for do tipo entrada
                else if ( $tipo_dma == 'Entrada' ) {

                    // Soma o total em kg das entradas
                    $dadosRelatorio[$storeCode]['total_entradas_kg'] += $dma_qtd;

                    $cutout_type = $dma['cutout_type'];

                    // Busca o código correspondente ao produto de primeira da loja em questão
                    $cutCode = array_values(array_filter($cutoutCodes->toArray(), function($cc) use($cutout_type){
                        return $cc['cutout_type'] == strtoupper($cutout_type);
                    }))[0]['cutout_code'];                    

                    $cutCode = str_pad($cutCode, 7, "0", STR_PAD_LEFT);

                    // Pega o custo do cálculo, senão pega o custo do produto na tabela de marcadorias
                    if ( $dma['ended'] === 'Y' ) {
                        $valor_mercadoria = $dma_cost;
                    } else {

                        // Busca os dados do produto que corresponde ao código do produto de primeira | segunda | osso e pelanca da loja correspondente
                        $dados_mercadoria = $this->Mercadorias->find()
                        ->select([
                            'tx_descricao',
                            'customed',
                            'custotab',
                            'opcusto'
                        ])
                        ->where([
                            'Mercadorias.cd_codigoint' => $cutCode
                        ])->first()
                        ->toArray();
    
                        $valor_mercadoria = 0;
    
                        if ( $dados_mercadoria['opcusto'] == "M" ) {
                            $valor_mercadoria = $dados_mercadoria['customed'];
                        } else {
                            $valor_mercadoria = $dados_mercadoria['custotab'];
                        }

                    }


                    $dadosRelatorio[$storeCode]['total_entradas_rs'] += $dma_qtd * $valor_mercadoria;

                    if ( $cutout_type == "Primeira" ) {
                        $dadosRelatorio[$storeCode]['rendimento_executado_primeira'] += $dma_qtd * $valor_mercadoria;
                        $dadosRelatorio[$storeCode]['custo_med_primeira'] =  $valor_mercadoria;
                    }

                    else if ( $cutout_type == "Segunda" ) {
                        $dadosRelatorio[$storeCode]['rendimento_executado_segunda'] += $dma_qtd * $valor_mercadoria;
                        $dadosRelatorio[$storeCode]['custo_med_segunda'] =  $valor_mercadoria;
                    }

                    else if ( $cutout_type == "Osso e Pelanca" ) {
                        $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca'] += $dma_qtd * $valor_mercadoria;
                        $dadosRelatorio[$storeCode]['custo_med_osso_pelanca'] =  $valor_mercadoria;
                    }

                    else if ( $cutout_type == "Osso a Descarte" ) {
                        $dadosRelatorio[$storeCode]['rendimento_executado_osso_descarte'] += $dma_qtd * $valor_mercadoria;
                        $dadosRelatorio[$storeCode]['custo_med_osso_descarte'] =  $valor_mercadoria;
                    }
          
                }

                // Calcula a diferença entre entradas e saídas em Kg e em R$
                $dadosRelatorio[$storeCode]['diferenca_saidas_entradas_kg'] = $dadosRelatorio[$storeCode]['total_saidas_kg']-$dadosRelatorio[$storeCode]['total_entradas_kg'];
                $dadosRelatorio[$storeCode]['diferenca_saidas_entradas_rs'] = $dadosRelatorio[$storeCode]['total_saidas_rs']-$dadosRelatorio[$storeCode]['total_entradas_rs'];

                // Calcular a diferença de rendimento entre orçado e executado
                $dadosRelatorio[$storeCode]['rendimento_dif_primeira'] = $dadosRelatorio[$storeCode]['rendimento_executado_primeira']-$dadosRelatorio[$storeCode]['rendimento_esperado_primeira'];
                $dadosRelatorio[$storeCode]['rendimento_dif_segunda'] = $dadosRelatorio[$storeCode]['rendimento_executado_segunda']-$dadosRelatorio[$storeCode]['rendimento_esperado_segunda'];
                $dadosRelatorio[$storeCode]['rendimento_dif_osso_pelanca'] = $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca']-$dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'];
                $dadosRelatorio[$storeCode]['rendimento_dif_osso_descarte'] = $dadosRelatorio[$storeCode]['rendimento_executado_osso_descarte']-$dadosRelatorio[$storeCode]['rendimento_esperado_osso_descarte'];

                $dadosRelatorio[$storeCode]['base_calc_rank'] += 
                    (!empty($dadosRelatorio[$storeCode]['rendimento_esperado_primeira']) ? $dadosRelatorio[$storeCode]['rendimento_executado_primeira'] / $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] : $dadosRelatorio[$storeCode]['rendimento_executado_primeira'])
                    + (!empty($dadosRelatorio[$storeCode]['rendimento_esperado_segunda']) ? $dadosRelatorio[$storeCode]['rendimento_executado_segunda'] / $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] : $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'])
                    + (!empty($dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca']) ? $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca'] / $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'] : $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'])
                ;

                if ( $dadosRelatorio[$storeCode]['encerramento'] < $dma['date_accounting']->format('Y-m-d') ){
                    $dadosRelatorio[$storeCode]['encerramento'] = $dma['date_accounting']->format('Y-m-d');
                }

            }// End foreach
    
            // Ordenar o array por base_calc_rank de forma decrescente
            usort($dadosRelatorio, function($a, $b) {
                return $b['base_calc_rank'] <=> $a['base_calc_rank'];
            });

            // Atribuir posicao_rank baseado na posição do array
            foreach ($dadosRelatorio as $index => &$value) {
                $value['posicao_rank'] = $index + 1;
            }
            unset($value);
    
        } 

        $this->set(compact(
            'dadosRelatorio',
            'selectedStoreCodes',
            'dateAccounting'
        ));        

    }

    public function public()
    {
        $this->Authorization->skipAuthorization();
        $dateAccounting = date('Y-m-d');
        $dadosRelatorio = [];
        for ($i = 1; $i <= 18; $i++) {
            $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
        }
        $selectedStoreCodes = $storeCodes;

        $this->loadModel('StoreCutoutCodes');
        $this->loadModel('Dma');
        $this->loadModel('Mercadorias');
        $this->loadModel('ExpectedYield');

        $query = $this->Dma->find()
            ->contain(['Mercadorias'])
            ->where([
                'Dma.store_code IN' => $selectedStoreCodes,
                'Dma.date_accounting' => $dateAccounting
            ])
            ->group([
                'Dma.id'
            ])
            ->toArray();

        foreach( $query as $key => $dma ){
            
            $storeCode = $dma['store_code'];
            if (!isset($dadosRelatorio[$storeCode])) {
            
                $dadosRelatorio[$storeCode] = [
                    'total_saidas_kg' => 0,
                    'total_saidas_rs' => 0,
                    'total_entradas_kg' => 0,
                    'total_entradas_rs' => 0,
                    'diferenca_saidas_entradas_kg' => 0,
                    'diferenca_saidas_entradas_rs' => 0,
                    'rendimento_esperado_primeira' => 0,
                    'rendimento_esperado_segunda' => 0,
                    'rendimento_esperado_osso_pelanca' => 0,
                    'rendimento_esperado_osso_descarte' => 0,
                    'rendimento_executado_primeira' => 0,
                    'rendimento_executado_segunda' => 0,
                    'rendimento_executado_osso_pelanca' => 0,
                    'rendimento_dif_primeira' => 0,
                    'rendimento_dif_segunda' => 0,
                    'rendimento_dif_osso_pelanca' => 0,
                    'custo_med_primeira' => 0,
                    'custo_med_segunda' => 0,
                    'custo_med_osso_pelanca' => 0,
                    'custo_med_osso_descarte' => 0,
                    'encerramento' => '2000-01-01',
                    'base_calc_rank' => 0,
                    'posicao_rank' => 1,
                    'loja' => $storeCode,
                    'finalizado_por' => ''
                ];
            }

            $cutoutCodes = $this->StoreCutoutCodes->find()->where([
                'StoreCutoutCodes.store_code' => $storeCode
            ]);

            $tipo_dma = $dma['type'];
            $dma_qtd = $dma['quantity'];
            $dma_cost = $dma['cost'];

            if ( $dma['ended'] === 'N' ) {
                $dadosRelatorio[$storeCode]['finalizado_por'] = 'em andamento';
            } else if ( $dma['ended'] === 'Y' && $dma['ended_by_cron'] === 'Y' ) {
                $dadosRelatorio[$storeCode]['finalizado_por'] = 'Sistema';
            } else if ( $dma['ended'] === 'Y' && $dma['ended_by_cron'] === 'N' ) {
                $dadosRelatorio[$storeCode]['finalizado_por'] = $dma['user'];
            }

            // Se o registro de DMA for do tipo saída
            if ( $tipo_dma == 'Saida' ) {
    
                $dadosRelatorio[$storeCode]['total_saidas_kg'] += $dma_qtd;
                $valor_mercadoria = 0;

                if ( $dma['mercadoria']['opcusto'] == "M" ) {
                    $valor_mercadoria = $dma['mercadoria']['customed'];
                }
                else {
                    $valor_mercadoria = $dma['mercadoria']['custotab'];
                }
                
                $dadosRelatorio[$storeCode]['total_saidas_rs'] += $dma_qtd * $valor_mercadoria;

                $espectativa = $this->ExpectedYield->find()
                ->where([
                    'ExpectedYield.good_code' => floatVal($dma['good_code'])
                ])
                ->first();

                if ( $espectativa ) {                        
                    $espectativa = $espectativa->toArray();
                } else {                        

                    $espectativa['prime'] = 0;
                    $espectativa['second'] = 0;
                    $espectativa['bones_skin'] = 0;
                    $espectativa['bones_discard'] = 0;
                }

                $espectativa_primeira_porc = $espectativa['prime'] ? $espectativa['prime']/100 : 0;
                $espectativa_segunda_porc = $espectativa['second'] ? $espectativa['second']/100 : 0;
                $espectativa_osso_pelanca_porc = $espectativa['bones_skin'] ? $espectativa['bones_skin']/100 : 0;
                $espectativa_osso_descarte_porc = $espectativa['bones_discard'] && $espectativa['bones_discard'] > 0 ? $espectativa['bones_discard']/100 : 0;

                $espectativa_primeira = $dma_qtd * $espectativa_primeira_porc;
                $espectativa_segunda = $dma_qtd * $espectativa_segunda_porc;
                $espectativa_osso_pelanca = $dma_qtd * $espectativa_osso_pelanca_porc;
                $espectativa_osso_descarte = $dma_qtd * $espectativa_osso_descarte_porc;

                $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] += $espectativa_primeira * $valor_mercadoria;
                $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] += $espectativa_segunda * $valor_mercadoria;
                $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'] += $espectativa_osso_pelanca * $valor_mercadoria;
                $dadosRelatorio[$storeCode]['rendimento_esperado_osso_descarte'] += $espectativa_osso_descarte * $valor_mercadoria;

            }

            // Se o registro de DMA for do tipo entrada
            else if ( $tipo_dma == 'Entrada' ) {

                // Soma o total em kg das entradas
                $dadosRelatorio[$storeCode]['total_entradas_kg'] += $dma_qtd;

                $cutout_type = $dma['cutout_type'];

                // Busca o código correspondente ao produto de primeira da loja em questão
                $cutCode = array_values(array_filter($cutoutCodes->toArray(), function($cc) use($cutout_type){
                    return $cc['cutout_type'] == strtoupper($cutout_type);
                }))[0]['cutout_code'];                    

                $cutCode = str_pad($cutCode, 7, "0", STR_PAD_LEFT);

                // Pega o custo do cálculo, senão pega o custo do produto na tabela de marcadorias
                if ( $dma['ended'] === 'Y' ) {
                    $valor_mercadoria = $dma_cost;
                } else {

                    // Busca os dados do produto que corresponde ao código do produto de primeira | segunda | osso e pelanca da loja correspondente
                    $dados_mercadoria = $this->Mercadorias->find()
                    ->select([
                        'tx_descricao',
                        'customed',
                        'custotab',
                        'opcusto'
                    ])
                    ->where([
                        'Mercadorias.cd_codigoint' => $cutCode
                    ])->first()
                    ->toArray();

                    $valor_mercadoria = 0;

                    if ( $dados_mercadoria['opcusto'] == "M" ) {
                        $valor_mercadoria = $dados_mercadoria['customed'];
                    } else {
                        $valor_mercadoria = $dados_mercadoria['custotab'];
                    }

                }


                $dadosRelatorio[$storeCode]['total_entradas_rs'] += $dma_qtd * $valor_mercadoria;

                if ( $cutout_type == "Primeira" ) {
                    $dadosRelatorio[$storeCode]['rendimento_executado_primeira'] += $dma_qtd * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['custo_med_primeira'] =  $valor_mercadoria;
                }

                else if ( $cutout_type == "Segunda" ) {
                    $dadosRelatorio[$storeCode]['rendimento_executado_segunda'] += $dma_qtd * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['custo_med_segunda'] =  $valor_mercadoria;
                }

                else if ( $cutout_type == "Osso e Pelanca" ) {
                    $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca'] += $dma_qtd * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['custo_med_osso_pelanca'] =  $valor_mercadoria;
                }

                else if ( $cutout_type == "Osso a Descarte" ) {
                    $dadosRelatorio[$storeCode]['rendimento_executado_osso_descarte'] += $dma_qtd * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['custo_med_osso_descarte'] =  $valor_mercadoria;
                }
        
            }

            // Calcula a diferença entre entradas e saídas em Kg e em R$
            $dadosRelatorio[$storeCode]['diferenca_saidas_entradas_kg'] = $dadosRelatorio[$storeCode]['total_saidas_kg']-$dadosRelatorio[$storeCode]['total_entradas_kg'];
            $dadosRelatorio[$storeCode]['diferenca_saidas_entradas_rs'] = $dadosRelatorio[$storeCode]['total_saidas_rs']-$dadosRelatorio[$storeCode]['total_entradas_rs'];

            // Calcular a diferença de rendimento entre orçado e executado
            $dadosRelatorio[$storeCode]['rendimento_dif_primeira'] = $dadosRelatorio[$storeCode]['rendimento_executado_primeira']-$dadosRelatorio[$storeCode]['rendimento_esperado_primeira'];
            $dadosRelatorio[$storeCode]['rendimento_dif_segunda'] = $dadosRelatorio[$storeCode]['rendimento_executado_segunda']-$dadosRelatorio[$storeCode]['rendimento_esperado_segunda'];
            $dadosRelatorio[$storeCode]['rendimento_dif_osso_pelanca'] = $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca']-$dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'];
            $dadosRelatorio[$storeCode]['rendimento_dif_osso_descarte'] = $dadosRelatorio[$storeCode]['rendimento_executado_osso_descarte']-$dadosRelatorio[$storeCode]['rendimento_esperado_osso_descarte'];

            $dadosRelatorio[$storeCode]['base_calc_rank'] += 
                (!empty($dadosRelatorio[$storeCode]['rendimento_esperado_primeira']) ? $dadosRelatorio[$storeCode]['rendimento_executado_primeira'] / $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] : $dadosRelatorio[$storeCode]['rendimento_executado_primeira'])
                + (!empty($dadosRelatorio[$storeCode]['rendimento_esperado_segunda']) ? $dadosRelatorio[$storeCode]['rendimento_executado_segunda'] / $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] : $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'])
                + (!empty($dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca']) ? $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca'] / $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'] : $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'])
            ;

            if ( $dadosRelatorio[$storeCode]['encerramento'] < $dma['date_accounting']->format('Y-m-d') ){
                $dadosRelatorio[$storeCode]['encerramento'] = $dma['date_accounting']->format('Y-m-d');
            }

        }// End foreach

        // Ordenar o array por base_calc_rank de forma decrescente
        usort($dadosRelatorio, function($a, $b) {
            return $b['base_calc_rank'] <=> $a['base_calc_rank'];
        });

        // Atribuir posicao_rank baseado na posição do array
        foreach ($dadosRelatorio as $index => &$value) {
            $value['posicao_rank'] = $index + 1;
        }
        unset($value);

        $this->set(compact(
            'dadosRelatorio',
            'selectedStoreCodes',
            'dateAccounting'
        ));     


    }
}