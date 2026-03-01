<?php
namespace Core\Http;

enum ServerAttribute: string
{
    case ROUTE = '_route';

    public function value(): string
    {
        return $this->value;
    }
}