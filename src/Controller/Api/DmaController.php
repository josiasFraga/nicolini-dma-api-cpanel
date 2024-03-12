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
    
        foreach (['saidas' => 'Saida', 'entradas' => 'Entrada'] as $key => $type) {
            foreach ($dados[$key] as $item) {
                $entities[] = $this->Dma->newEntity([
                    'store_code' => $store_code,
                    'user' => $dados['user'],
                    'date_movement' => date('Y-m-d'),
                    'date_accounting' => $date_accounting,
                    'type' => $type,
                    // Campos específicos para 'entradas' ou 'saidas'
                    'cutout_type' => $type == 'Entrada' ? ($key === 'entradas' ? "Primeira" : null) : null,
                    'good_code' => $type == 'Saida' ? str_pad($item['goodCode'], 7, "0", STR_PAD_LEFT) : null,
                    'quantity' => str_replace(',', '.', str_replace('.', '', $item[$type == 'Saida' ? 'kg' : 'primeMeatKg'])),
                ]);
            }
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
