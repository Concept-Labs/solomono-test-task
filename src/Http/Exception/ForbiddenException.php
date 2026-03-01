<?php

namespace Core\Http\Exception;

use Core\Http\Code;

class ForbiddenException extends HttpException
{
    protected function getStatusCode(): int
    {
        return Code::FORBIDDEN->value();
    }
}