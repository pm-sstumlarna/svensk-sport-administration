<?php 

namespace SSA\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthorizationMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    private AuthorizationServiceInterface $authService;

    public function __construct()
    {
        $this->authService = new AuthorizationService();
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $validToken = $this->authService->validateToken($authHeader);
        if (!$validToken) {
            $response = new \Nyholm\Psr7\Response();
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json')
                            ->withBody(\Nyholm\Psr7\Stream::create(json_encode(['error' => 'Unauthorized'])));
        }

        return $handler->handle($request);
    }
}