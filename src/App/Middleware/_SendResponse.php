<?php
namespace Core\App\Middleware;

use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;
use Core\Middleware\MiddlewareInterface;

class SendResponse implements MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $response = $next($request);
        $response->send();
        
        return $response;
    }
}