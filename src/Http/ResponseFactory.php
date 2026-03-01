<?php
namespace Core\Http;

use Core\Container\ContainerInterface;

class ResponseFactory
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(private ContainerInterface $container)
    {}

    /**
     * @return ResponseInterface
     */
    public function create(): ResponseInterface
    {
        return $this->container->get(ResponseInterface::class);    
    }
}