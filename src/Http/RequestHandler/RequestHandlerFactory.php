<?php
namespace Core\Http\RequestHandler;

use Core\Container\ContainerInterface;
use Core\Container\Contract\SharedInterface;
use Core\Container\Exception\NotFoundException;
use RuntimeException;

class RequestHandlerFactory implements SharedInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @param string $handler
     * 
     * @return RequestHandlerInterface
     * @throws RuntimeException
     */
    public function create(string $handler): RequestHandlerInterface
    {
        try {
            $instance = $this->getContainer()->get($handler);
        } catch (NotFoundException $e) {
            throw new RuntimeException("Handler '$handler' not found", 0, $e);
        }

        if (!$instance instanceof RequestHandlerInterface) {
            throw new RuntimeException("Handler '$handler' must implement RequestHandlerInterface");
        }

        return $instance;
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
    