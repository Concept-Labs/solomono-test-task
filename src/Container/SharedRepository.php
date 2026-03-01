<?php
namespace Core\Container;

class SharedRepository
{
    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * @param string $name
     * @param mixed $instance
     * 
     * @return static
     */
    public function share(string $name, mixed $instance): static
    {
        $this->instances[$name] = $instance;

        return $this;
    }

    /**
     * @param string $name
     * 
     * @return object|null
     */
    public function get(string $name): ?object
    {
        return $this->instances[$name] ?? null;
    }
}