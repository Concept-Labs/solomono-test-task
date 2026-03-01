<?php

namespace Core\Middleware;

use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;

interface MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next): ResponseInterface;
}