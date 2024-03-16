<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes) {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder) {


        $builder->setExtensions(['json']);

        // Redirecionar a raiz para admin/dashboard/login
        $builder->redirect('/', ['controller' => 'admin', 'action' => 'index']);


  
        $builder->connect('/pages/*', 'Pages::display');

 
        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder) {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */

     $routes->prefix('admin', function (RouteBuilder $builder) {
        $builder->connect('/users/login', ['controller' => 'Users', 'action' => 'login']); // <-- Adicione esta linha
        $builder->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        $builder->connect('/users', ['controller' => 'Users', 'action' => 'index']);
        $builder->connect('/images/upload', ['controller' => 'Images', 'action' => 'upload', '_method' => 'POST']);
        $builder->fallbacks();
    });

    // Definindo o escopo da API
    $routes->prefix('api', function (RouteBuilder $builder) {
        // Aqui você pode aplicar middleware específico para a API, se necessário
        // Por exemplo, para parsear JSON ou XML:
        $builder->setExtensions(['json']);

        // Conecte as ações da API aqui
        /*$builder->connect('/pagarme/teste', [
            'controller' => 'Pagarme',
            'action' => 'teste'
        ])
          ->setMethods(['GET']);

        // Conecte as ações da API aqui
        /*$builder->connect('/affiliate/:id', [
            'controller' => 'Affiliates',
            'action' => 'view',
            'pass' => ['id']
        ])->setPatterns(['id' => '\d+'])
          ->setMethods(['GET']);*/
        
        $builder->connect('/users/login', [
            'controller' => 'Users',
            'action' => 'login'
        ])->setMethods(['POST']);
        
        $builder->connect('/mercadorias/index', [
            'controller' => 'Mercadorias',
            'action' => 'index'
        ])->setMethods(['GET']);
        
        $builder->connect('/store-cutout-codes/index', [
            'controller' => 'StoreCutoutCodes',
            'action' => 'index'
        ])->setMethods(['GET']);

        $builder->connect('/expected-yield/index', [
            'controller' => 'ExpectedYield',
            'action' => 'index'
        ])->setMethods(['GET']);

        $builder->connect('/dma/finish', [
            'controller' => 'Dma',
            'action' => 'finish'
        ])->setMethods(['POST']);

        $builder->connect('/dma/next-date', [
            'controller' => 'Dma',
            'action' => 'nextDate'
        ])->setMethods(['GET']);
        
        // Você pode adicionar mais rotas da API conforme necessário
    });
};
