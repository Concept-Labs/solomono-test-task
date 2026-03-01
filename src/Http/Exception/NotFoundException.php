<?php

namespace Core\Http\Exception;

use Core\Http\Code;

class NotFoundException extends HttpException
{
    protected function getStatusCode(): int
    {
        return Code::NOT_FOUND->value();
    }
}