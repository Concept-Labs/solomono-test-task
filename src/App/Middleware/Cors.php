<?php
namespace Core\App\Middleware;

use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;
use Core\Middleware\MiddlewareInterface;

class Cors implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $response = $next($request);
        
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        return $response;
    }
}