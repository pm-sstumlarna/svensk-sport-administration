<?php

namespace SSA;

use DI\Container;
use Nyholm\Psr7\Response;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\handler\ActivityHandler;
use SSA\handler\BookingHandler;
use SSA\handler\CalendarHandler;
use SSA\handler\CourseHandler;
use SSA\handler\GroupHandler;
use SSA\handler\InvoiceHandler;
use SSA\handler\MessageHandler;
use SSA\handler\NaturalPersonHandler;
use SSA\handler\NewsHandler;
use SSA\handler\OrganizationHandler;
use SSA\handler\ReportHandler;
use SSA\handler\SyllabusHandler;
use SSA\library\AuthorizationServiceInterface;
use SSA\library\StorageServiceInterface;
use SSA\middleware\AuthenticationMiddleware;
use SSA\middleware\AuthorizationMiddleware;
use SSA\middleware\CorsMiddleware;
use SSA\middleware\DispatcherMiddleware;
use SSA\middleware\RoutingMiddleware;
use SSA\middleware\StorageMiddleware;
use SSA\service\AuthorizationService;
use SSA\service\StorageService;
use Throwable;

class Application implements RequestHandlerInterface, MiddlewareInterface
{
    private Container $container;

    public function __construct(array $configuration)
    {
        $this->container = new Container();
        $this->container->set('configuration', $configuration);

        $this->container->set(PDO::class, function () {
            return new PDO('sqlite::memory:');
        });

        $this->container->set(StorageServiceInterface::class, function (ContainerInterface $c) {
            $pdo = $c->get(PDO::class);
            $storage = new StorageService($pdo);
            $storage->initialize();
            return $storage;
        });

        $this->container->set(AuthorizationServiceInterface::class, function () {
            return new AuthorizationService();
        });

        $this->container->set(RouterInterface::class, function (ContainerInterface $c) {
            $configuration = $c->get('configuration');
            $router = new Router();
            foreach ($configuration['routes'] ?? [] as $route) {
                $router->addRoute($route[0], $route[1], $c->get($route[2]));
            }
            return $router;
        });

        $this->container->set(OrganizationHandler::class, function () {
            return new OrganizationHandler();
        });
        $this->container->set(ActivityHandler::class, function () {
            return new ActivityHandler();
        });
        $this->container->set(BookingHandler::class, function () {
            return new BookingHandler();
        });
        $this->container->set(CalendarHandler::class, function () {
            return new CalendarHandler();
        });
        $this->container->set(CourseHandler::class, function () {
            return new CourseHandler();
        });
        $this->container->set(GroupHandler::class, function () {
            return new GroupHandler();
        });
        $this->container->set(InvoiceHandler::class, function () {
            return new InvoiceHandler();
        });
        $this->container->set(MessageHandler::class, function () {
            return new MessageHandler();
        });
        $this->container->set(NewsHandler::class, function () {
            return new NewsHandler();
        });
        $this->container->set(ReportHandler::class, function () {
            return new ReportHandler();
        });
        $this->container->set(SyllabusHandler::class, function () {
            return new SyllabusHandler();
        });
        $this->container->set(NaturalPersonHandler::class, function (ContainerInterface $c) {
            return new NaturalPersonHandler($c->get(AuthorizationServiceInterface::class));
        });
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middlewarePipe = new MiddlewarePipe();
        $corsMiddleware = $this->container->get(CorsMiddleware::class);
        $authenticationMiddleware = $this->container->get(AuthenticationMiddleware::class);
        $routingMiddleware = $this->container->get(RoutingMiddleware::class);
        $authorizationMiddleware = $this->container->get(AuthorizationMiddleware::class);
        $storageMiddleware = $this->container->get(StorageMiddleware::class);
        $dispatcherMiddleware = $this->container->get(DispatcherMiddleware::class);

        $middlewarePipe->pipe($this);
        $middlewarePipe->pipe($corsMiddleware);
        $middlewarePipe->pipe($authenticationMiddleware);
        $middlewarePipe->pipe($routingMiddleware);
        $middlewarePipe->pipe($authorizationMiddleware);
        $middlewarePipe->pipe($storageMiddleware);
        $middlewarePipe->pipe($dispatcherMiddleware);

        return $middlewarePipe->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $body = json_encode(['error' => $e->getMessage()]);
            return new Response(500, $headers, $body);
        }
    }
    public function getContainer(): Container
    {
        return $this->container;
    }
}