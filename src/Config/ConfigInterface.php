<?php
namespace Core\Config;

interface ConfigInterface
{
    /**
     * @param string $path
     * 
     * @return static
     */
    public function load(string $path): static;

    /**
     * @param string $key       "Dot notation"
     * @param mixed $default
     * 
     * @return mixed
     */
    public function get(string $key): mixed;
}