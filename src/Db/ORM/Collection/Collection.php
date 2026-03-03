<?php

namespace Core\Db\ORM\Collection;

use Core\Db\ConnectionInterface;
use Core\Db\ORM\DTO\DTO;
use Core\Db\ORM\ModelInterface;
use Generator;
use IteratorAggregate;
use JsonSerializable;

class Collection implements CollectionInterface, IteratorAggregate ,JsonSerializable
{

    const MAX_PAGE_SIZE = 1000;
    const MAX_PAGE = 1000000;

    private ?ModelInterface $model = null;

    private ?string $rawSql = null;
    private array $rawParams = [];

    
    private int $pageSize = 10;
    private int $page = 1;
    private array $sort = [];

    public function __construct(
        private ConnectionInterface $connection
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function withModel(ModelInterface $model): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function raw(string $sql, array $params = []): static
    {
        $this->rawSql = $sql;
        $this->rawParams = $params;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll(?string $sql = null, array $params = []): Generator
    {
        $this->rawSql = $sql ?? $this->rawSql;
        $this->rawParams = $params ?: $this->rawParams;

        $sql = $this->sql();

        foreach ($this->connection()->fetchAll($sql, $this->rawParams) as $row) {
            yield $this->model()
                ->fromDto(
                    DTO::fromArray($row)
                );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sort(string $column, string $direction = 'ASC'): static
    {
        $this->sort[] = [
            'column' => $this->sanitizeSortColumn($column),
            'direction' => $this->normalizeSortDirection($direction),
        ];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function size(?int $pageSize = null): int|static
    {
        if ($pageSize === null) {
            return $this->pageSize;
        }

        if ($pageSize < 1) {
            throw new \InvalidArgumentException('Page size must be greater than 0.');
        }

        if ($pageSize > self::MAX_PAGE_SIZE) {
            throw new \InvalidArgumentException('Page size must be less than or equal to ' . static::MAX_PAGE_SIZE . '.');
        }

        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function page(?int $page = null): int|static
    {
        if ($page === null) {
            return $this->page;
        }

        if ($page < 1) {
            throw new \InvalidArgumentException('Page must be greater than 0.');
        }

        if ($page > self::MAX_PAGE) {
            throw new \InvalidArgumentException('Page must be less than or equal to ' . static::MAX_PAGE . '.');
        }

        $this->page = $page;

        return $this;
    }

    /**
     * коряво але поки так, і так звже забурився :)
     * {@inheritDoc}
     */
    public function total(): int
    {
        $sql = preg_replace('/SELECT\s+.*\s+FROM/i', 'SELECT COUNT(*) as count FROM', $this->rawSql);
        
        $result = $this->connection()->fetchOne($sql, $this->rawParams);

        return (int)($result['count'] ?? 0);
    }

    /**
     * {@inheritDoc}
     */
    public function pages(): int
    {
        return (int)ceil($this->total() / $this->pageSize);
    }

    /**
     * @return string
     */
    protected function sql(): string
    {
        $sql = $this->rawSql;

        if (!$sql) {
            $sql = sprintf('SELECT * FROM %s', $this->table());
        }

        if ($this->sort) {
            $orderBy = implode(', ', array_map(fn($s) => sprintf('%s %s', $s['column'], $s['direction']), $this->sort));
            $sql .= sprintf(' ORDER BY %s', $orderBy);
        }

        if (!str_contains(strtolower($sql), 'limit')) {
            $sql .= sprintf(' LIMIT %d OFFSET %d', $this->pageSize, ($this->page - 1) * $this->pageSize);
        }

        return $sql;
    }

    /**
     * @return ModelInterface
     */
    protected function model(): ModelInterface
    {
        if (null === $this->model) {
            throw new \LogicException('Model is not set for collection');
        }

        return $this->model;
    }

    /**
     * @return string
     */
    protected function table(): string
    {
        return $this->model()->table();
    }

    /**
     * @return ConnectionInterface
     */
    protected function connection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        yield from $this->fetchAll();
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): mixed
    {
        return iterator_to_array($this->getIterator());
    }

    /**
     * @param string $direction
     *
     * @return string
     */
    protected function normalizeSortDirection(string $direction): string
    {
        $direction = strtoupper(trim($direction));

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException('Sort direction must be ASC or DESC.');
        }

        return $direction;
    }

    /**
     * @param string $column
     *
     * @return string
     */
    protected function sanitizeSortColumn(string $column): string
    {
        $column = trim($column);

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $column)) {
            throw new \InvalidArgumentException('Invalid sort column format.');
        }

        return $column;
    }


    ///----- було почав робити але ну часу не вагон Ж)

    // public function getSelectBuilder(): Select
    // {
    //     return $this->select;
    // }


    // public function select(...$columns): Select
    // {
    //     return $this->getSelectBuilder()
    //         ->select(...$columns)
    //         ->from($this->table());
    // }

    // public function join(string $table, string $rightKey, string $type = 'INNER'): static
    // {
    //    $this->select()->join(
    //         $table,
    //         sprintf('%s.%s', $this->table(), $this->model()->primaryKey()),
    //         sprintf('%s.%s', $table, $rightKey),
    //         $type
    //     );

    //     return $this;
    // }

    // public function joinModel(ModelInterface $model, string $type = 'INNER'): static
    // {
    //     return $this->join($model->table(), $this->model()->primaryKey(), $type);
    // }

    // public function sql(): string
    // {
    //     $this->select()
    //         ->limit($this->pageSize)
    //         ->offset(($this->page - 1) * $this->pageSize);

    //     return (string)$this->select();
    // }

    

    //...
}