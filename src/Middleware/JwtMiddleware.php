<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Cake\Utility\Security;
use Cake\Http\Exception\UnauthorizedException;

class JwtMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $url = $request->getUri()->getPath();

        // Verifica se a URL começa com /api/
        if (strpos($url, '/api/') === 0) {
            
            // Permite a rota de login sem verificação de JWT
            if (preg_match('/^\/api\/users\/login/', $url)) {
                return $handler->handle($request);
            }
            if (preg_match('/^\/api\/dma\/auto-finish/', $url)) {
                return $handler->handle($request);
            }
            if (preg_match('/^\/api\/mercadorias\/index/', $url)) {
                return $handler->handle($request);
            }
            if (preg_match('/^\/api\/lojas\/index/', $url)) {
                return $handler->handle($request);
            }

            // A lógica JWT é aplicada apenas para rotas com prefixo /api/
            $header = $request->getHeaderLine('Authorization');
            $bearerToken = str_replace('Bearer ', '', $header);

            if (empty($bearerToken)) {
                throw new UnauthorizedException("Token não encontrado");
            }

            try {
                // Decodifica o token JWT usando a chave secreta
                $payload = JWT::decode($bearerToken, new Key(Security::getSalt(), 'HS256'));
            } catch (\Exception $e) {
                throw new UnauthorizedException("Token inválido: " . $e->getMessage());
            }

            // Anexa o payload decodificado ao request para uso posterior
            $request = $request->withAttribute('jwtPayload', $payload);
        }

        // Passa o controle para o próximo middleware/request handler
        return $handler->handle($request);
    }
}
