<?php
namespace Core\App\Middleware;

use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;
use Core\Http\ServerAttribute;
use Core\Middleware\MiddlewareInterface;

class RequestAttributes implements MiddlewareInterface
{

    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $request->attribute(
            ServerAttribute::ROUTE->value(), 
            $request->get(ServerAttribute::ROUTE->value()) ?? '/'
        );

        return $next($request);
    }
}