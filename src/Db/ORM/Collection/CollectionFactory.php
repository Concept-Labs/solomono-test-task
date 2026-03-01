<?php
namespace Core\Db\ORM\Collection;

use Core\Container\ContainerInterface;
use Core\Db\ORM\ModelInterface;

class CollectionFactory
{
    public function __construct(private ContainerInterface $container)
    {
    }
    

    public function create(ModelInterface $model)
    {
        $modelClass = $model::class;

        $possibleCollection = "{$modelClass}\\Collection";

        if (class_exists($possibleCollection)) {
            $collection = $this->container->get($possibleCollection);
        } else {
            $collection = $this->container->get(Collection::class);
        }

        

        return $collection->withModel($model);
    }
}