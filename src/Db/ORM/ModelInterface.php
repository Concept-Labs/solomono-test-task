<?php

namespace Core\Db\ORM;

use ArrayAccess;
use Core\Db\ORM\Collection\CollectionInterface;
use Core\Db\ORM\DTO\DTO;
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

/**
     * @param DTO $dto
     * 
     * @return static
     */
    public function fromDto(DTO $dto): static;

    /**
     * @param array|null $data
     * 
     * @return DTO
     */
    public function dto(?array $data = null): DTO;

    /**
     * @return CollectionInterface
     */
    public function collection(): CollectionInterface;

     /**
     * @param int $id
     * 
     * @return static
     */
    public function find(int $id): static;

}