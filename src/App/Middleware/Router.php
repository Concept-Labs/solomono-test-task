<?php

namespace Core\App\Middleware;

use Core\Config\ConfigInterface;
use Core\Http\Exception\ForbiddenException;
use Core\Http\Exception\NotFoundException;
use Core\Http\RequestHandler\RequestHandlerFactory;
use Core\Http\RequestInterface;
use Core\Http\ResponseFactory;
use Core\Http\ResponseInterface;
use Core\Http\ServerAttribute;
use Core\Middleware\MiddlewareInterface;

class Router implements MiddlewareInterface
{
    /**
     * @param ConfigInterface $config
     * @param RequestHandlerFactory $requestHandlerFactory
     */
    public function __construct(
        private ConfigInterface $config,
        private ResponseFactory $responseFactory,
        private RequestHandlerFactory $requestHandlerFactory
    ) {}

    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $response = $this->dispatch($request);

        return $response instanceof ResponseInterface 
            ? $response 
            : $next($request); // можна добавити інші роутери якщощо
    }

    /**
     * @param RequestInterface $request
     * 
     * @return ResponseInterface|null
     */
    protected function dispatch(RequestInterface $request): ?ResponseInterface
    {
        if (null === $handlerClass = $this->resolveHandler($request)) {
            return null;
        }

        try {

            $requestHandler = $this->requestHandlerFactory()->create($handlerClass);

        } catch (\Throwable $e) {
            throw new NotFoundException("Failed to create request handler '{$handlerClass}': " . $e->getMessage());
        }

        return $requestHandler->handle($request, $this->responseFactory()->create());
    }

    /**
     * @param RequestInterface $request
     * 
     * @return string|null
     */
    protected function resolveHandler(RequestInterface $request): ?string
    {
        $routeName = $this->getRequestedRouteName($request);

        if (!$routeName) {
            return null;
        }

        $routeName = $this->matchRouteName($routeName);

        $handlerClass = $this->config("routes.{$routeName}");

        if (null === $handlerClass || !is_string($handlerClass) || empty($handlerClass)) {
            return null;
        }

        if (!$this->isHttpMethodAllowed($request, $handlerClass)) {
            throw new ForbiddenException("HTTP method '{$request->method()}' not allowed for this route");
        }

        return $handlerClass;
    }

     /**
     * @param RequestInterface $request
     * 
     * @return string|null
     */
    protected function getRequestedRouteName(RequestInterface $request): ?string
    {
        /**
         * @see \Core\App\Middleware\RequestAttributes
         * ми заповнювали цей атрибут в RequestAttributes Middleware
         */
        return $request->attribute(ServerAttribute::ROUTE->value());

        // return $request->get(ServerAttribute::ROUTE->value()); // або напряму з GET параметра (див. .htaccess)
    }

    /**
     * @param string $route
     * 
     * @return string|null
     */
    protected function matchRouteName(string $route): ?string
    {
        $routeNames = $this->getRouteNames();

        foreach ($routeNames as $routeName) {
            if (fnmatch($routeName, $route) ) {
                return $routeName;
            }

            // @todo: додати підтримку параметрів у маршрутах, наприклад /category/{id}
        }

        return null;
    }

    protected function getRouteNames(): array
    {
        $routes = $this->config('routes', []);

        return array_keys($routes);
    }

    /**
     * @param RequestInterface $request
     * @param string $handlerClass
     * 
     * @return bool
     */
    protected function isHttpMethodAllowed(RequestInterface $request, string $handlerClass): bool
    {
        $reflection = new \ReflectionClass($handlerClass);
        $requireAllowed = $reflection->getAttributes(\Core\Http\Attribute\AllowMethod::class);

        if (count($requireAllowed) < 1) {
            return true; // Якщо атрибут не вказаний, дозволяємо всі методи
        }

        /** @var \Core\Http\Attribute\AllowMethod $allowedMethodAttribute */
        $allowedMethodAttribute = $requireAllowed[0]->newInstance();

        return $allowedMethodAttribute->isAllowed($request->method());
    }

    /**
     * @param string $key
     * 
     * @return mixed
     */
    protected function config(string $key): mixed
    {
        return $this->config->get($key);
    }

    /**
     * @return RequestHandlerFactory
     */
    protected function requestHandlerFactory(): RequestHandlerFactory
    {
        return $this->requestHandlerFactory;
    }

    /**
     * @return ResponseFactory
     */
    protected function responseFactory(): ResponseFactory
    {
        return $this->responseFactory;
    }

    // на потім вже...
    // protected function extractRouteParameters(string $route, string $routePattern): array
    // {
    //     $routeParts = explode('/', trim($route, '/'));
    //     $patternParts = explode('/', trim($routePattern, '/'));

    //     $parameters = [];

    //     foreach ($patternParts as $index => $part) {
    //         if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
    //             $paramName = trim($part, '{}');
    //             $parameters[$paramName] = $routeParts[$index] ?? null;
    //         }
    //     }

    //     return $parameters;
    // }

    
}
