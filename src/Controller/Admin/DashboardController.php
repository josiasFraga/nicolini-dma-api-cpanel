<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

use Cake\Event\EventInterface;

class DashboardController extends AppController
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

    }

    public function login()
    {

        return $this->redirect(['action' => 'index']);

    }

}
?>