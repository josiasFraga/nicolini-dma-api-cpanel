<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\Core\Exception\Exception;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Cake\ORM\TableRegistry;

use Cake\Event\EventInterface;

class UsersController extends AppController
{

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login']);
        $this->Authorization->skipAuthorization(['login']);
    }
    

    public function login()
    {
        $userStoreTable = TableRegistry::getTableLocator()->get('ApoUsuarioloja');
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $dados = json_decode($this->request->getData('dados'), true);

        $conditions = [
            'login' => $dados["user"]
        ];
   
        $user = $usersTable->find()
            ->where($conditions)
            ->first();

        $password = md5($dados["password"]);

        if ($user && $user->pswd == $password) {

            $conditions_loja = [
                'Login' => $dados["user"]
            ];

            if ( isset($dados["store"]) && !empty($dados["store"]) ) {
                $conditions_loja['Loja'] = $dados["store"];
            }
   
            $user_store = $userStoreTable->find()
            ->where($conditions_loja)
            ->order([
                'ultatu DESC'
            ])
            ->first();

            if ( !$user_store ) {

                throw new Exception('Loja não encontrada');
            }


            $payload = [
                'sub' => $user->login,
                'exp' => time() + 4204800
            ];

        
            $jwt = JWT::encode($payload, Security::getSalt(), 'HS256');

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'status' => 'ok',
                    'token' => $jwt,
                    'loja' => $user_store['Loja']
                ]));
        } else {
            throw new Exception('Dados de login inválidos');
        }
    }

}
