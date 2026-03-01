<?php
namespace Core\Db\ORM\Collection;

use Core\Db\ORM\ModelInterface;

interface CollectionInterface
{
    public function withModel(ModelInterface $model): self;
    public function with(ModelInterface $withModel): self;
}