<?php

namespace Core\Db\ORM\Collection;

use Core\Db\ConnectionInterface;
use Core\Db\ORM\ModelInterface;
use Core\Db\ORM\QueryBuilder\Select;
use IteratorAggregate;
use JsonSerializable;

class Collection implements CollectionInterface, IteratorAggregate ,JsonSerializable
{

    private ?ModelInterface $model = null;


    
    private int $pageSize = 10;
    private int $page = 1;

    public function __construct(
        private ConnectionInterface $connection,
        private Select $select 
    )
    {
    }

    public function withModel(ModelInterface $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function select(...$columns): Select
    {
        return $this->select->select(...$columns)->from($this->table());
    }

    public function with(ModelInterface $withModel): self
    {
        $this->select()->join(
            $withModel->table(),
            sprintf('%s.%s', $this->table(), $this->model()->primaryKey()),
            sprintf('%s.%s', $withModel->table(), $withModel->primaryKey()),
            'LEFT'
        );

        return $this;
    }

    public function size(int $pageSize): self
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    public function page(int $page): self
    {
        $this->select()->limit($this->pageSize)->offset(($page - 1) * $this->pageSize);
        return $this;
    }

    protected function model(): ModelInterface
    {
        if (null === $this->model) {
            throw new \LogicException('Model is not set for collection');
        }

        return $this->model;
    }

    protected function table(): string
    {
        return $this->model()->table();
    }

    protected function connection(): ConnectionInterface
    {
        return $this->connection;
    }


    public function getIterator(): \Traversable
    {
        yield 'nothing yet';
    }

    public function jsonSerialize(): mixed
    {
        //return iterator_to_array($this->getIterator());
    }

    

    //...
}