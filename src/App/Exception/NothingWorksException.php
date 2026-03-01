<?php
namespace Core\App\Exception;

use Exception;

class NothingWorksException extends AppException
{
    protected $code = 404;
}