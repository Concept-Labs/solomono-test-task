<?php
namespace Core\Http;

use Core\Container\Contract\SharedInterface;

/**
 * Мегапроста реалізація, достатньо для демонстрації
 */
class Request implements RequestInterface, SharedInterface
{

    /**
     * Тут можна зберігати будь-які атрибути, наприклад зараз потрібно інформацію про маршрут (реврайт в .htaccess)
     * я використав для цього міддлвар @see RequestAttributes
     */
    private array $attributes = [];

    /** @var array<string, mixed> */
    private array $server = [];

    /** @var array<string, mixed> */
    private array $get = [];

    /** @var array<string, mixed> */
    private array $post = [];

    /** @var array<string, mixed> */
    private array $request = [];

    /** @var array<string, mixed> */
    private array $cookie = [];

    /** @var array<string, mixed> */
    private array $session = [];

    public function __construct()
    {
        $this->capture();
    }

    /**
     * {@inheritDoc}
     */
    public function capture(): static
    {
        $this->server = $_SERVER;
        $this->get = $_GET;
        $this->request = $_REQUEST;
        $this->post = $_POST;
        $this->cookie = $_COOKIE;
        $this->session = $_SESSION ?? []; // скоріш за все сесії ще нема
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function method(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): mixed
    {
        return $this->get[$key] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function post(string $key): mixed
    {
        return $this->post[$key] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function request(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }

    /**
     * {@inheritDoc}
     */
    public function cookie(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $this->cookie[$key] = $value;
            setcookie($key, $value); // Синхронізуємо

            return $this; // для fluent API
        }

        return $this->cookie[$key] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function session(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $this->session[$key] = $value;
            $_SESSION[$key] = $value; // та ж фігня

            return $this;
        }

        return $this->session[$key] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function setSession(array $session): static
    {
        $this->session = $session;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function attribute(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $this->attributes[$key] = $value;

            return $this;
        }

        return $this->attributes[$key] ?? null;
    }
}