<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Event\EventInterface;


class ProductUsersController extends AppController
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
        return ['index'];
    }


    public function index()
    {
        $jwtPayload = $this->request->getAttribute('jwtPayload');
        $userId = $jwtPayload->sub;

        $this->loadModel('AppProductUsers');

        $permissoes = $this->AppProductUsers->find()
        ->where([
            'AppProductUsers.user_login' => $userId
        ])
        ->contain([
            'AppProducts'
        ]);

        if ( $permissoes ) {
            $permissoes->toArray();
        }

        return $this->jsonResponse('ok', null, null, $permissoes);
    }
}
