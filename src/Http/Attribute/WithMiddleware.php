<?php

namespace Core\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class WithMiddleware
{
    /**
     * @template T of MiddlewareInterface
     * @var class-string<T>[]
     */
    private array $middlewareClasses = [];

    /**
     * @param string|array $middleware
     */
    public function __construct(string|array $middleware)
    {
        $this->middlewareClasses = (array) $middleware;
    }

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewareClasses;
    }
}