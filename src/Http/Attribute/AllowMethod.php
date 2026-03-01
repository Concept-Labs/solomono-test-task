<?php
namespace Core\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AllowMethod
{
    private array $allowedMethods = [];

    public function __construct(string|array $methods)
    {
        $this->allowedMethods = (array) $methods;
    }

    public function isAllowed(string $method): bool
    {
        return in_array($method, $this->allowedMethods, true);
    }
}