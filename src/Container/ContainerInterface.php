<?php
namespace Core\Container;

interface ContainerInterface
{
    public function get(string $name): object;
}