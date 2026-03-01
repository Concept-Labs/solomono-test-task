<?php
namespace Core\Http\RequestHandler;

use Core\Http\RequestInterface;
use Core\Http\ResponseInterface;

interface RequestHandlerInterface
{
    /**
     * @param RequestInterface $request
     * 
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request): ResponseInterface;
}