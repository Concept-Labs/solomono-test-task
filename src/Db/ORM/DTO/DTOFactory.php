<?php

namespace Core\Db\ORM\DTO;

use Core\Container\ContainerInterface;

class DTOFactory
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function create(array $data = []): DTO
    {
        $dto = $this->getContainer()->get(DTO::class);

        $dto->setData($data);

        return $dto;
    }
    
    private function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}