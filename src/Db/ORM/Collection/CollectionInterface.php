<?php
namespace Core\Db\ORM\Collection;

use Core\Db\ORM\ModelInterface;

interface CollectionInterface
{
    public function withModel(ModelInterface $model): static;
    public function raw(string $sql, array $params = []): static;
    public function size(?int $pageSize = null): int|static;
    public function page(?int $page = null): int|static;
    public function sort(string $column, string $direction = 'ASC'): static;
    public function fetchAll(?string $sql = null, array $params = []): \Generator;
    public function total(): int;
    public function pages(): int;
}