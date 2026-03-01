<?php
namespace Core\Http\Exception;

use Exception;

class HttpException extends Exception
{

    public function __construct(string $message = "", int $code = 0, Exception|null $previous = null)
    {
        parent::__construct($message, $this->getStatusCode() ?? $code, $previous);
    }

    protected function getStatusCode(): ?int
    {
        return null;
    }

}