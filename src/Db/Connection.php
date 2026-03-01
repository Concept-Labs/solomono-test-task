<?php
namespace Core\Db;

use Core\Container\Contract\SharedInterface;
use Core\Db\Exception\PingException;
use Generator;
use PDO;

class Connection implements ConnectionInterface, SharedInterface
{
    private ?PDO $pdo = null;

    /**
     * {@inheritDoc}
     */
    public function connect(string $dsn, string $username, string $password, array $options = []): static
    {
        $this->pdo = new PDO($dsn, $username, $password, $options);

        $this->ping();

        return $this;
    }

    public function ping(): static
    {
        try {
            $this->pdo()->query('SELECT 1');
            
        } catch (\Exception $e) {
            throw new PingException("Database connection failed: " . $e->getMessage(), 0, $e);
        }

        return $this;
    }

    public function pdo(): PDO
    {
        if ($this->pdo === null) {
            throw new \RuntimeException("Database connection not established. Call connect() first.");
        }

        return $this->pdo;
    }

    public function exec(string $query, array $params = []): int
    {
        $stmt = $this->pdo()->prepare($query);

        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    public function fetchAll(string $query, array $params = []): Generator
    {
        $stmt = $this->pdo()->prepare($query);

        $stmt->execute($params);
        
        foreach ($stmt->fetchAll() as $row) {
            yield $row;
        }
    }

    public function fetchOne(string $query, array $params = []): ?array
    {
        $stmt = $this->pdo()->prepare($query);

        $stmt->execute($params);
        
        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    public function beginTransaction(): static
    {
        $this->pdo()->beginTransaction();

        return $this;
    }

    public function commit(): static
    {
        $this->pdo()->commit();

        return $this;
    }

    public function rollBack(): static
    {
        $this->pdo()->rollBack();

        return $this;
    }

    
}