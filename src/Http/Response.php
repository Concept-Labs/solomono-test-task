<?php
namespace Core\Http;


class Response implements ResponseInterface
{

    /**
     * @var Code
     */
    protected Code $statusCode = Code::OK;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var string
     */
    protected string $body = '';

    /**
     * {@inheritDoc}
     */
    public function status(Code $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function body(string $content): static
    {
        $this->body = $content;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function send(): static
    {
        http_response_code($this->statusCode->value());

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->body;

        return $this;
    }
}