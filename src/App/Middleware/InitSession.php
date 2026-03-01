<?php
namespace Core\App\Middleware;

use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;
use Core\Middleware\MiddlewareInterface;

class InitSession implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            $request->setSession($_SESSION);
        }

        return $next($request);
    }
}