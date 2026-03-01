<?php
namespace Core\Db\ORM\QueryBuilder;

use Stringable;

class Select implements Stringable
{
    private array $from = [];
    private array $columns = [];
    private array $joins = [];
    private array $where = [];
    private array $orders = [];
    private array $group = [];
    private ?int $limit = null;
    private ?int $offset = null;


    public function select(string ...$columns): static
    {
        $this->columns = count($columns) === 0 ? ['*'] : $columns;
        return $this;
    }

    public function from(string $table): static
    {
        $this->from[] = $table;

        $this->from = array_unique($this->from);

        return $this;
    }

    public function join(string $table, string $leftKey, string $rightKey, string $type = 'INNER'): static
    {
        $this->joins[] = [
            'table' => $table,
            'leftKey' => $leftKey,
            'rightKey' => $rightKey,
            'type' => $type,
        ];

        return $this;
    }

    public function where(string $column, string $operator, $value): static
    {
        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    // @todo: orWhere

    public function order(string $column, string $direction = 'ASC'): static
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * @param string ...$columns
     * 
     * @return static
     */
    public function group(string ...$columns): static
    {
        $this->group = array_unique($columns);

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * наразі просто така збірка
     * можу запропонувати і маю більш цікау
     * 
     * @return string
     */
    public function __toString(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns);
        $sql .= ' FROM ' . implode(', ', $this->from);

        foreach ($this->joins as $join) {
            $sql .= sprintf(
                ' %s JOIN %s ON %s = %s',
                $join['type'],
                $join['table'],
                $join['leftKey'],
                $join['rightKey']
            );
        }

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', array_map(function ($condition) {
                return sprintf('%s %s :%s', $condition['column'], $condition['operator'], str_replace('.', '_', $condition['column']));
            }, $this->where));
        }

        if (!empty($this->group)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->group);
        }

        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . implode(', ', array_map(function ($order) {
                return sprintf('%s %s', $order['column'], $order['direction']);
            }, $this->orders));
        }

        return $sql;
    }
}