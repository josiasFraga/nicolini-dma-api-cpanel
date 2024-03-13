<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Authentication\PasswordHasher\DefaultPasswordHasher;


class UsersController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
    
        $this->Authentication->allowUnauthenticated(['login', 'hashPassword']);

        // Pular verificações de autorização para a ação de login
        if (in_array($this->request->getParam('action'), ['login', 'hashPassword'])) {
            $this->Authorization->skipAuthorization();
        }
    }
    

    public function login()
    {
        $this->Authorization->skipAuthorization();
        if ($this->request->is('post')) {
            $result = $this->Authentication->getResult();
            // If the user is logged in send them away.
            if ($result->isValid()) {
                $target = 'admin/dashboard/index';
                return $this->redirect($target);
            }
            if ($this->request->is('post')) {
                $this->Flash->error(__('Nome de usuários e/ou senha inválidos!'));
            }
        }
    }

    public function logout()
    {
        $this->Authorization->skipAuthorization();
        $this->Authentication->logout();
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function hashPassword()
    {
        $this->Authorization->skipAuthorization();
        $password = "zap123";

        $hasher = new DefaultPasswordHasher();
        echo $hasher->hash($password);
    
        die();
    }
}
