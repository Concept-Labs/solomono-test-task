<?php
namespace App\Http\Handler\Middleware;

use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;
use Core\Middleware\MiddlewareInterface;

class TestRequestHandlerMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        return $next($request)->header('X-Test-Middleware', 'TestRequestHandlerMiddleware was here');
    }
}