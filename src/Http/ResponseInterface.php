<?php
namespace Core\Http;

interface ResponseInterface
{
    /**
     * @param Code $code HTTP status code
     * 
     * @return static
     */
    public function status(Code $code): static;

    /**
     * @param string $name
     * @param string $value
     * @return static
     */
    public function header(string $name, string $value): static;

    /**
     * @param string $content
     * @return static
     */
    public function body(string $content): static;

    /**
     * @return static
     */
    public function send(): static;
}