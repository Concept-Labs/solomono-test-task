<?php
namespace Core\Db;

use Generator;
use PDO;

interface ConnectionInterface
{
    /**
      *
      * @param string $dsn 
      * @param string $username
      * @param string $password
      * @param array $options
      *
      * @return static
     */
    public function connect(string $dsn, string $username, string $password, array $options = []): static;

    public function pdo(): PDO;

    public function exec(string $query, array $params = []): int;

    public function fetchAll(string $query, array $params = []): Generator;

    public function fetchOne(string $query, array $params = []): ?array;

    public function beginTransaction(): static;

    public function commit(): static;

    public function rollBack(): static;

}