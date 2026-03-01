<?php

namespace Core\Db\ORM;

use ArrayAccess;
use JsonSerializable;

interface ModelInterface  extends ArrayAccess, JsonSerializable
{
    /**
     * @return string
     */
    public function table(?string $tableName = null): string;

    /**
     * ну один кей, не ускладюю
     * @return string
     */
    public function primaryKey(?string $primaryKey = null): string;
}