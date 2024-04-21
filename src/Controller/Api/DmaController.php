<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Event\EventInterface;
use Cake\Log\Log;
use Cake\I18n\FrozenTime;

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
        return ['finish', 'nextDate', 'saveIncome', 'saveOutcome', 'loadOutcomes', 'loadIncomes'];
    }

    public function finish()
    {
        $jwtPayload = $this->request->getAttribute('jwtPayload');
        $userId = $jwtPayload->sub;
        $dados = json_decode($this->request->getData('dados'), true);

        //Log::debug($this->request->getData('dados'));
    
        // Verifica a presença e não-vazio de 'saidas' e 'entradas'
        if (empty($dados['saidas'])) {
            return $this->jsonResponse('erro', 'Nenhuma saída informada.');
        }
    
        if (empty($dados['entradas'])) {
            return $this->jsonResponse('erro', 'Nenhuma entrada informada.');
        }
    
        $this->loadModel('Users');
        $this->loadModel('Dma');
    
        if ($dados['user'] != $userId) {
            return $this->jsonResponse('erro', 'Usuário informado diferente do usuário logado no app');
        }
    
        $user = $this->Users->find()
            ->where(['login' => $dados["user"]])
            ->first();
    
        if (!$user || $user->pswd != md5($dados["password"])) {
            return $this->jsonResponse('erro', 'O usuário ou a senha informados são inválidos!');
        }
    
        // Tratamento de datas
        $store_code = $dados['store_code'];
        $date_accounting = $this->calculateDateAccounting($store_code);

        if ( !$date_accounting ) {
            return $this->jsonResponse('erro', 'Não é possível salvar lançamentos para depois de amanhã.');
        }
    
        $entitiesToSave = $this->prepareEntities($dados, $store_code, $date_accounting, 'Y');

        if ( $entitiesToSave === false ) {
            return $this->jsonResponse('erro', 'Não é possível salvar lançamentos.');
        }
    
        if ($this->Dma->saveMany($entitiesToSave)) {
            return $this->jsonResponse('ok', 'DMA finalizado com sucesso!');
        } else {
            $errors = $this->extractErrors($entitiesToSave);
            return $this->jsonResponse('erro', 'Ocorreu um erro ao finalizar o DMA.', $errors);
        }
    }

    public function nextDate()
    {
        $jwtPayload = $this->request->getAttribute('jwtPayload');
        $userId = $jwtPayload->sub;
    
        $this->loadModel('Users');
        $this->loadModel('Dma');
    
        // Tratamento de datas
        $store_code = $this->request->getQuery('store_code');
        $next_date = $this->calculateDateAccounting($store_code);
    
        if ( !$next_date ) {
            return $this->jsonResponse('info', 'Lançamentos encerrados por hoje');
        }
    
        
        return $this->jsonResponse('ok', '', [], $next_date);
        
    }
    
    private function jsonResponse($status, $message, $errors = [], $data = [])
    {
        return $this->response->withType('application/json')
            ->withStringBody(json_encode(compact('status', 'message', 'errors', 'data')));
    }
    
    private function calculateDateAccounting($store_code)
    {
        $checkLastData = $this->Dma->find()
            ->select(['Dma.date_accounting'])
            ->where(['Dma.store_code' => $store_code, 'Dma.ended' => 'Y'])
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
    
    private function prepareEntities($dados, $store_code, $date_accounting, $ended)
    {
        $entities = [];

        $this->loadModel('StoreCutoutCodes');
        $this->loadModel('Mercadorias');

        $first_good_data = $this->StoreCutoutCodes->find('all')
        ->where([            
            'StoreCutoutCodes.store_code' => $store_code,
            'StoreCutoutCodes.cutout_type' => 'PRIMEIRA'
        ])
        ->first()
        ->toArray();

        $second_good_data = $this->StoreCutoutCodes->find('all')
        ->where([            
            'StoreCutoutCodes.store_code' => $store_code,
            'StoreCutoutCodes.cutout_type' => 'SEGUNDA'
        ])
        ->first()
        ->toArray();

        $bones_and_skin_data = $this->StoreCutoutCodes->find('all')
        ->where([            
            'StoreCutoutCodes.store_code' => $store_code,
            'StoreCutoutCodes.cutout_type' => 'OSSO E PELANCA'
        ])
        ->first()
        ->toArray();

        $custo_total_saidas = 0;
        $peso_total_saidas = 0;
        $custo_med_saidas = 0;
    
        foreach( $dados['saidas'] as $key => $saida ) {
            $entities[] = $this->Dma->newEntity([
                'store_code' => $store_code,
                'user' => $dados['user'],
                'date_movement' => date('Y-m-d'),
                'date_accounting' => $date_accounting,
                'type' => 'Saida',
                'cutout_type' => null,
                'good_code' => str_pad($saida['goodCode'], 7, "0", STR_PAD_LEFT),
                'quantity' => str_replace(',','.',str_replace('.','',$saida['kg'])),
                'ended' => $ended
            ]);

            $dados_mercadoria = $this->Mercadorias->find('all')
            ->where([
                'Mercadorias.cd_codigoint' => str_pad($saida['goodCode'], 7, "0", STR_PAD_LEFT)
            ])
            ->first()
            ->toArray();

            $custo_total = 0;

            if ( count($dados_mercadoria) > 0 && $dados_mercadoria['opcusto'] == "M" ) {
                $custo_total = str_replace(',','.',str_replace('.','',$saida['kg'])) * $dados_mercadoria['customed'];
            } else {
                $custo_total = str_replace(',','.',str_replace('.','',$saida['kg'])) * $dados_mercadoria['custotab'];
            }

            $custo_total_saidas += $custo_total;
            $peso_total_saidas += str_replace(',','.',str_replace('.','',$saida['kg']));
        }

        $custo_med_saidas = $custo_total_saidas/$peso_total_saidas;


        debug($custo_total_saidas);
        debug($peso_total_saidas);
        debug($custo_med_saidas);
        die();

        $dados_entradas_salvar = [];

        foreach( $dados['entradas'] as $key => $entrada ) {

            $first_quantity = str_replace(',','.',str_replace('.','',$entrada['primeMeatKg']));
            $second_quantity = str_replace(',','.',str_replace('.','',$entrada['secondMeatKg']));
            $bones_and_skin_quantity = str_replace(',','.',str_replace('.','',$entrada['boneAndSkinKg']));

            if ( $first_quantity > 0 ) {

                // Procura itens negativos para diminuir
                foreach( $dados['entradas'] as $key_remover => $entrada_remover ) {
                    $first_quantity_remove = str_replace(',','.',str_replace('.','',$entrada_remover['primeMeatKg']));
                    if ( $first_quantity_remove < 0 ) {

                        $first_new_quantity = $first_quantity - $first_quantity_remove;

                        if ( $first_new_quantity < 0 ){
                            $first_quantity = 0;
                            $dados['entradas'][$key]['primeMeatKg'] = 0;
                            $dados['entradas'][$key_remover]['primeMeatKg'] = $first_quantity_remove - $first_quantity_remove;
                        } else {
                            $first_quantity = $first_new_quantity;
                            $dados['entradas'][$key]['primeMeatKg'] = $first_new_quantity;
                            $dados['entradas'][$key_remover]['primeMeatKg'] = 0;
                        }

                    }
                }

            } else if ( $first_quantity < 0 ) {

                foreach( $dados['entradas'] as $key_remover => $entrada_remover ) {
                    $first_quantity_remove = str_replace(',','.',str_replace('.','',$entrada_remover['primeMeatKg']));
                    if ( $first_quantity_remove > 0 ) {

                        $first_new_quantity =  $first_quantity_remove + $first_quantity;

                        if ( $first_new_quantity >= 0 ){
                            $first_quantity = 0;                            
                            $dados['entradas'][$key_remover]['primeMeatKg'] = $first_new_quantity;
                            $dados['entradas'][$key]['primeMeatKg'] = 0;
                        } else {
                            $first_quantity = $first_quantity + $dados['entradas'][$key_remover]['primeMeatKg'];
                            $dados['entradas'][$key_remover]['primeMeatKg'] = 0;
                            $dados['entradas'][$key]['primeMeatKg'] = $first_quantity + $dados['entradas'][$key_remover]['primeMeatKg'];
                        }

                    }
                }

                if ( $first_quantity < 0 ) {
                    return false;
                }

                
            }

            if ( $second_quantity > 0 ) {

                // Procura itens negativos para diminuir
                foreach( $dados['entradas'] as $key_remover => $entrada_remover ) {
                    $second_quantity_remove = str_replace(',','.',str_replace('.','',$entrada_remover['secondMeatKg']));
                    if ( $second_quantity_remove < 0 ) {

                        $second_new_quantity = $second_quantity - $second_quantity_remove;

                        if ( $second_new_quantity < 0 ){
                            $second_quantity = 0;
                            $dados['entradas'][$key]['secondMeatKg'] = 0;
                            $dados['entradas'][$key_remover]['secondMeatKg'] = $second_quantity_remove - $second_quantity_remove;
                        } else {
                            $second_quantity = $second_new_quantity;
                            $dados['entradas'][$key]['secondMeatKg'] = $second_new_quantity;
                            $dados['entradas'][$key_remover]['secondMeatKg'] = 0;
                        }

                    }
                }

            } else if ( $second_quantity < 0 ) {

                foreach( $dados['entradas'] as $key_remover => $entrada_remover ) {
                    $second_quantity_remove = str_replace(',','.',str_replace('.','',$entrada_remover['secondMeatKg']));
                    if ( $second_quantity_remove > 0 ) {

                        $second_new_quantity =  $second_quantity_remove + $second_quantity;

                        if ( $second_new_quantity >= 0 ){
                            $second_quantity = 0;                            
                            $dados['entradas'][$key_remover]['secondMeatKg'] = $second_new_quantity;
                            $dados['entradas'][$key]['secondMeatKg'] = 0;
                        } else {
                            $second_quantity = $second_quantity + $dados['entradas'][$key_remover]['secondMeatKg'];
                            $dados['entradas'][$key_remover]['secondMeatKg'] = 0;
                            $dados['entradas'][$key]['secondMeatKg'] = $second_quantity + $dados['entradas'][$key_remover]['secondMeatKg'];
                        }

                    }
                }

                if ( $second_quantity < 0 ) {
                    return false;
                }

                
            }

            if ( $bones_and_skin_quantity > 0 ) {

                // Procura itens negativos para diminuir
                foreach( $dados['entradas'] as $key_remover => $entrada_remover ) {
                    $bones_and_skin_quantity_remove = str_replace(',','.',str_replace('.','',$entrada_remover['boneAndSkinKg']));
                    if ( $bones_and_skin_quantity_remove < 0 ) {

                        $bones_and_skin_new_quantity = $bones_and_skin_quantity - $bones_and_skin_quantity_remove;

                        if ( $bones_and_skin_new_quantity < 0 ){
                            $bones_and_skin_quantity = 0;
                            $dados['entradas'][$key]['boneAndSkinKg'] = 0;
                            $dados['entradas'][$key_remover]['boneAndSkinKg'] = $bones_and_skin_quantity_remove - $bones_and_skin_quantity_remove;
                        } else {
                            $bones_and_skin_quantity = $bones_and_skin_new_quantity;
                            $dados['entradas'][$key]['boneAndSkinKg'] = $bones_and_skin_new_quantity;
                            $dados['entradas'][$key_remover]['boneAndSkinKg'] = 0;
                        }

                    }
                }

            } else if ( $bones_and_skin_quantity < 0 ) {

                foreach( $dados['entradas'] as $key_remover => $entrada_remover ) {
                    $bones_and_skin_quantity_remove = str_replace(',','.',str_replace('.','',$entrada_remover['boneAndSkinKg']));
                    if ( $bones_and_skin_quantity_remove > 0 ) {

                        $bones_and_skin_new_quantity =  $bones_and_skin_quantity_remove + $bones_and_skin_quantity;

                        if ( $bones_and_skin_new_quantity >= 0 ){
                            $bones_and_skin_quantity = 0;                            
                            $dados['entradas'][$key_remover]['boneAndSkinKg'] = $bones_and_skin_new_quantity;
                            $dados['entradas'][$key]['boneAndSkinKg'] = 0;
                        } else {
                            $bones_and_skin_quantity = $bones_and_skin_quantity + $dados['entradas'][$key_remover]['boneAndSkinKg'];
                            $dados['entradas'][$key_remover]['boneAndSkinKg'] = 0;
                            $dados['entradas'][$key]['boneAndSkinKg'] = $bones_and_skin_quantity + $dados['entradas'][$key_remover]['boneAndSkinKg'];
                        }

                    }
                }

                if ( $bones_and_skin_quantity < 0 ) {
                    return false;
                    /*return $this->jsonResponse('erro', 'Você inseriu um valor negativo em osso e pelanca muito grande.');
                    debug($bones_and_skin_quantity);
                    debug('trews');
                    die(0);*/
                }

                
            }

            $dados_entradas_salvar[] = $dados['entradas'][$key];
            
        }

        foreach( $dados_entradas_salvar as $key => $entrada) {

            $first_quantity = str_replace(',','.',str_replace('.','',$entrada['primeMeatKg']));
            $second_quantity = str_replace(',','.',str_replace('.','',$entrada['secondMeatKg']));
            $bones_and_skin_quantity = str_replace(',','.',str_replace('.','',$entrada['boneAndSkinKg']));

            $entities[] = $this->Dma->newEntity([
                'store_code' => $store_code,
                'user' => $dados['user'],
                'date_movement' => date('Y-m-d'),
                'date_accounting' => $date_accounting,
                'type' => 'Entrada',
                'cutout_type' => "Primeira",
                'good_code' => str_pad($first_good_data['cutout_code'], 7, "0", STR_PAD_LEFT),
                'quantity' => $first_quantity,
                'ended' => $ended
            ]);
    
            $entities[] = $this->Dma->newEntity([
                'store_code' => $store_code,
                'user' => $dados['user'],
                'date_movement' => date('Y-m-d'),
                'date_accounting' => $date_accounting,
                'type' => 'Entrada',
                'cutout_type' => "Segunda",
                'good_code' => str_pad($second_good_data['cutout_code'], 7, "0", STR_PAD_LEFT),
                'quantity' => $second_quantity,
                'ended' => $ended
            ]);
    
            $entities[] = $this->Dma->newEntity([
                'store_code' => $store_code,
                'user' => $dados['user'],
                'date_movement' => date('Y-m-d'),
                'date_accounting' => $date_accounting,
                'type' => 'Entrada',
                'cutout_type' => "Osso e Pelanca",
                'good_code' => str_pad($bones_and_skin_data['cutout_code'], 7, "0", STR_PAD_LEFT),
                'quantity' => $bones_and_skin_quantity,
                'ended' => $ended
            ]);            

        }
    
        return $entities;
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

        $dados_lancados = $this->Dma->find('all')
        ->where([
            'Dma.ended' => 'N',
            'Dma.user' => $userId,
            'Dma.type' => 'Saida',
            'Dma.good_code' => str_pad($dados['goodCode'], 7, "0", STR_PAD_LEFT),
        ])->toArray();

        if ( $dados['kg'] < 0 ) {

            $qtd_diminuir = abs($dados['kg']);

            if ( count($dados_lancados) === 0 ) {
                return $this->jsonResponse('ok', 'Ajuste na saída cadastrada com sucesso!');
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

            return $this->jsonResponse('ok', 'Ajuste na saída cadastrada com sucesso!');
        }

        if ( count($dados_lancados) > 0 ) {

            $dados_salvar = $dados_lancados[0];
            $dados_salvar['quantity']+= $dados['kg'];
            $dma = $dados_salvar;

        } else {
            $dados_salvar=[
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

    public function loadIncomes() {

        $jwtPayload = $this->request->getAttribute('jwtPayload');
        $userId = $jwtPayload->sub;        
        $store_code = $this->request->getQuery('store_code');

        if ( !$store_code ) {

            $this->set([
                'status' => 'erro',
                'message' => 'Código da loja não informado ao buscar as entradas!'
            ]);
        }

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


        return $this->jsonResponse('ok', '', [], $entradas_retornar);
    }

    public function loadOutcomes() {

        $jwtPayload = $this->request->getAttribute('jwtPayload');
        $userId = $jwtPayload->sub;        
        $store_code = $this->request->getQuery('store_code');

        if ( !$store_code ) {

            $this->set([
                'status' => 'erro',
                'message' => 'Código da loja não informado ao buscar as saidas!'
            ]);
        }

        $this->loadModel('Dma');
        $this->loadModel('Mercadorias');

        $saidas = $this->Dma->find('all')
        ->where([
            'store_code' => $store_code,
            'user' => $userId,
            'ended' => 'N',
            'type' => 'Saida'
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


        return $this->jsonResponse('ok', '', [], $saidas_retornar);
    }
}
