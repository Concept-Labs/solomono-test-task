<?php
namespace Core\Db\ORM;

use Core\Db\ConnectionInterface;
use Core\Db\ORM\DTO\DTO;
use Core\Container\Attribute\Injectable;
use Core\Db\ORM\Collection\CollectionFactory;
use Core\Db\ORM\Collection\CollectionInterface;


/**
дуже примітивна реалізація, просто для демонстрації
квері білдер і всяке інше не буду вже робити
*/
#[Injectable]
class Model implements ModelInterface
{
    /**
     * можна в фінальній можелі вказати, або якась логіка @see getTableName()
     * 
     * @var string|null
     */
    private ?string $tableName = null;

    /**те ж саме @see getPrimaryKey()
     * 
     * @var string|null
     */
    private ?string $primaryKey = null;

    
    /** 
     * @var CollectionInterface|null
     */
    private ?CollectionInterface $collection = null;

    /**
     * @param ConnectionInterface $connection
     * @param DTO $dto
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private ConnectionInterface $connection, 
        private DTO $dto,
        private CollectionFactory $collectionFactory
    )
    {
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->dto = clone $this->dto;
        $this->collection = null;
    }

    /**
     * {@inheritDoc}
     */
    public function fromDto(DTO $dto): static
    {
        $clone = clone $this;
        $clone->dto = $dto;
        
        return $clone;
    }

    /**
     * @param array|null $data
     * 
     * @return DTO
     */
    public function dto(?array $data = null): DTO
    {
        if ($data !== null) {       
            $this->dto->setData($data ?? []);
        }

        return $this->dto;
    }

    /**
     * {@inheritDoc}
     */
    public function table(?string $tableName = null): string
    {
        if (null !== $tableName) {
            $this->tableName = $tableName;
        }

        if (null === $this->tableName) {
            // fallback
            // можа щось типу як в Laravel
            $this->tableName = str_snake((new \ReflectionClass($this))->getShortName());
        }

        return $this->tableName;
    }

    /**
     * {@inheritDoc}
     */
    public function primaryKey(?string $primaryKey = null): string
    {
        if (null !== $primaryKey) {
            $this->primaryKey = $primaryKey;
        }

        if (null === $this->primaryKey) {
            // fallback
            $this->primaryKey = $this->table() . '_id';
        }

        return $this->primaryKey;
    }

    /**
     * {@inheritDoc}
     */
    public function collection(): CollectionInterface
    {
        if (null === $this->collection) {
            $this->collection = $this->collectionFactory->create($this);
        }

        return $this->collection;
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): static
    {
        
        $sql = sprintf(
            'SELECT * FROM %s WHERE %s = :id',
            $this->table(),
            $this->primaryKey()
        );

        $data = $this->connection()->fetchOne(
            $sql,
            ['id' => $id]
        );


        $this->dto($data);

        return $this;
    }

    /**
     * @return ConnectionInterface
     */
    protected function connection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function jsonSerialize(): mixed
    {
        return $this->dto()->jsonSerialize();
    }

    public function offsetExists($offset): bool
    {
        return isset($this->dto()[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->dto()[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->dto()[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->dto()[$offset]);
    }
    //...
}