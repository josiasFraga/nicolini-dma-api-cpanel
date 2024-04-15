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
        return ['finish', 'nextDate'];
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
    
        $entitiesToSave = $this->prepareEntities($dados, $store_code, $date_accounting);

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
            ->where(['Dma.store_code' => $store_code])
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
    
    private function prepareEntities($dados, $store_code, $date_accounting)
    {
        $entities = [];

        $this->loadModel('StoreCutoutCodes');

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
    
        foreach( $dados['saidas'] as $key => $saida ) {
            $entities[] = $this->Dma->newEntity([
                'store_code' => $store_code,
                'user' => $dados['user'],
                'date_movement' => date('Y-m-d'),
                'date_accounting' => $date_accounting,
                'type' => 'Saida',
                'cutout_type' => null,
                'good_code' => str_pad($saida['goodCode'], 7, "0", STR_PAD_LEFT),
                'quantity' => str_replace(',','.',str_replace('.','',$saida['kg']))
            ]);
        }

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
                'quantity' => $first_quantity
            ]);
    
            $entities[] = $this->Dma->newEntity([
                'store_code' => $store_code,
                'user' => $dados['user'],
                'date_movement' => date('Y-m-d'),
                'date_accounting' => $date_accounting,
                'type' => 'Entrada',
                'cutout_type' => "Segunda",
                'good_code' => str_pad($second_good_data['cutout_code'], 7, "0", STR_PAD_LEFT),
                'quantity' => $second_quantity
            ]);
    
            $entities[] = $this->Dma->newEntity([
                'store_code' => $store_code,
                'user' => $dados['user'],
                'date_movement' => date('Y-m-d'),
                'date_accounting' => $date_accounting,
                'type' => 'Entrada',
                'cutout_type' => "Osso e Pelanca",
                'good_code' => str_pad($bones_and_skin_data['cutout_code'], 7, "0", STR_PAD_LEFT),
                'quantity' => $bones_and_skin_quantity
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
}
