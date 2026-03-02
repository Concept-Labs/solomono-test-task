<?php
namespace Core\Http;

interface RequestInterface
{

    /**
     * @return static
     */
    public function capture(): static;

    /**
     * @return string (GET, POST, PUT, DELETE і т.д.)
     */
    public function method(): string;
    
    /**
     * @param string $key
     * 
     * @return mixed|null
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * 
     * @return mixed|null
     */
    public function post(string $key): mixed;

    /**
     * @param string $key
     * @param mixed|null $default
     * 
     * @return mixed|null
     */
    public function request(string $key, mixed $default = null): mixed;

    /**
     * @param string $key
     * @param mixed|null $value
     * 
     * @return mixed|null|$this
     */
    public function cookie(string $key, mixed $value = null): mixed;

    /**
     * @param string $key
     * @param mixed|null $value
     * 
     * @return mixed|null|$this
     */
    public function session(string $key, mixed $value = null): mixed;

    /**
     * @param array $session
     * 
     * @return static
     */
    public function setSession(array $session): static;

    /**
     * @param string $key
     * @param mixed|null $value
     * 
     * @return mixed|null|$this
     */
    public function attribute(string $key, mixed $value = null): mixed;

    //...
}
