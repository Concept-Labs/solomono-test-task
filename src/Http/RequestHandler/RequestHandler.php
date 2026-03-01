<?php

namespace Core\Http\RequestHandler;

use Core\Container\Attribute\Injectable;
use Core\Container\Attribute\Injector;
use Core\Http\RequestHandler\RequestHandlerInterface;
use Core\Http\RequestInterface;
use Core\Http\ResponseFactory;
use Core\Http\ResponseInterface;

#[Injectable]
abstract class RequestHandler implements RequestHandlerInterface
{

    /**
     * @var ResponseFactory|null
     */
    protected ?ResponseFactory $responseFactory = null;

    /**
     * {@inheritDoc}
     */
    abstract public function handle(RequestInterface $request): ResponseInterface;

    #[Injector]
    /**
     * @param ResponseFactory $responseFactory
     */
    public function setResponseFactory(ResponseFactory $responseFactory): void
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return ResponseFactory
     */
    protected function getResponseFactory(): ResponseFactory
    {
        if ($this->responseFactory === null) {
            throw new \RuntimeException('ResponseFactory dependency not injected');
        }
        return $this->responseFactory;
    }

    
    
}