<?php
namespace Core\Config;

use ArrayAccess;
use Core\Container\Contract\SharedInterface;

class Config implements ConfigInterface, SharedInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [];

    /**
     * @var array<string, mixed>
     */
    private array $cache = [];
    
    /**
     * 
     * для зручності
     * @param string $key
     * 
     * @return mixed
     */
    public function __invoke(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function load(string $path): static
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new \RuntimeException("Config file not found or not readable at path: {$path}");
        }

        $configData = require $path;

        if (!is_array($configData)) {
            throw new \RuntimeException("Config file must return an array");
        }

        return $this->inflate($configData);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): mixed
    {
        return $this->cached($key) ?? $this->raw($key);
    }

    /**
     * @param array<string, mixed> $config
      * 
      * @return static
     */
    protected function inflate(array $config): static
    {
        $this->cache = [];
        $this->config = $config;

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function cached(string $key): mixed
    {
        return isset($this->cache[$key]) ? $this->cache[$key] : null;
    }

    /**
     * @param string        $key
     * @param mixed|null    $default   
     * 
     * @return mixed
     */
    protected function raw(string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return null;
            }
        }

        return $this-> cache[$key] = $value;
    }

    //...
}