<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

//authent
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\IdentifierInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

//author
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }

        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            $this->addPlugin('DebugKit');
        }

        // Load more plugins here
        $this->addPlugin('Authentication');
        $this->addPlugin('Authorization');
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // Middleware padrão
        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(Configure::read('Error')))
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware());

        // Adiciona AuthenticationMiddleware ao middleware queue
        $authenticationMiddleware = new AuthenticationMiddleware($this);

        // Adiciona AuthorizationMiddleware com configuração customizada
        $authorizationMiddleware = new AuthorizationMiddleware($this, [
            
            'scope' => function ($request) {
                return str_starts_with($request->getPath(), '/admin');
            },
            // Aqui você pode adicionar configurações específicas para o AuthorizationMiddleware se necessário
            'unauthorizedHandler' => [
                'className' => 'Authorization.Redirect',
                'url' => '/users/login',
                'queryParam' => 'redirectUrl',
                'statusCode' => 302,
            ],
            'skipAuthorization' => [
                '/api/users/login.json',
            ],
        ]);
    
        $middlewareQueue->add(new \App\Middleware\JwtMiddleware());

        // Adicionando middlewares de autenticação e autorização
        $middlewareQueue->add($authenticationMiddleware);
        $middlewareQueue->add($authorizationMiddleware);

        /* Descomente se precisar do CsrfProtectionMiddleware
        $middlewareQueue->add(new CsrfProtectionMiddleware([
            'httponly' => true,
        ]));
        */
            

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Bootstrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        $this->addOptionalPlugin('Cake/Repl');
        $this->addOptionalPlugin('Bake');

        $this->addPlugin('Migrations');

        // Load more plugins here
    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService();
        
        // Configura redirecionamento para não autenticados e outros parâmetros globais
        $service->setConfig([
            'unauthenticatedRedirect' => Router::url('/users/login'),
            'queryParam' => 'redirect',
        ]);

        // Obtém o prefixo da rota atual
        $prefix = $request->getAttribute('params')['prefix'] ?? '';

        if ($prefix === 'Api') {
            $service->loadAuthenticator('Authentication.Jwt', [
                'returnPayload' => false,
                'secretKey' => env('JWT_SECRET_KEY', "7Kki8zIRn&>w*et"),
                'algorithm' => 'HS256',
                'header' => 'Authorization',
                'prefix' => 'Bearer',
                'parameter' => 'token',
                'queryParam' => 'token',
                'tokenType' => 'jwt',
            ]);

            $service->loadIdentifier('Authentication.JwtSubject');

        } elseif ($prefix === 'Admin') {
            // Para o Admin, usamos autenticação baseada em sessão e formulário
            $service->loadAuthenticator('Authentication.Session');
            $service->loadAuthenticator('Authentication.Form', [
                'fields' => [
                    IdentifierInterface::CREDENTIAL_USERNAME => 'email',
                    IdentifierInterface::CREDENTIAL_PASSWORD => 'password',
                ],
                'loginUrl' => Router::url([
                    'prefix' => 'Admin',
                    'controller' => 'Users',
                    'action' => 'login',
                ]),
            ]);
            $service->loadIdentifier('Authentication.Password', [
                'fields' => [
                    'username' => 'email',
                    'password' => 'password',
                ],
                'resolver' => [
                    'className' => 'Authentication.Orm',
                    'userModel' => 'Users', // Ajuste para o nome do seu modelo de Usuários
                ],
            ]);
        }

        return $service;
    }

    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $resolver = new OrmResolver();
        return new AuthorizationService($resolver);
    }
}
