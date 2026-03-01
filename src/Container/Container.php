<?php

namespace Core\Container;

use Core\Container\Attribute\Inject;
use Core\Container\Attribute\Injectable;
use Core\Container\Attribute\Injector;
use Core\Container\Contract\SharedInterface;
use Core\Container\Exception\CircularDependencyException;
use Core\Container\Exception\NotFoundException;
use ReflectionClass;

/**
 * простий контейнер
 * Поки що розв'язує залежності за типом, але можна розширити для підтримки конфігурації, синглтонів, фабрик тощо.
 */

class Container implements ContainerInterface
{
    /**
     * Кеш для шаред сервісів
     * @var SharedRepository|null
     */
    private ?SharedRepository $shared = null;

    /** 
     * для запобігання циклічних залежностей
     * @var array<string, bool>
     */
    private array $resolving = [];

    /**
     * {@inheritDoc}
     */
    public function get(string $id, ...$args): object
    {
        try {
            $this
                ->assertNotResolving($id)
                ->markResolving($id);

            $resolved = $this->resolve($id);

            if (is_a($this, $id)) {
                return $this; // себе повернемо хардкодно
            }

            if ($this->hasShared($resolved)) {
                return $this->getShared($resolved);
            }

            if (!class_exists($resolved)) {
                throw new NotFoundException("Class {$resolved} not found");
            }

            $reflection = new \ReflectionClass($resolved);

            if (!$reflection->isInstantiable()) {
                throw new NotFoundException("Class {$resolved} is not instantiable");
            }

            $constructor = $reflection->getConstructor();

            if (!$constructor) {
                $service =  $reflection->newInstance(); // new $resolved() 
            } else {

                $dependencies = $args; // якщо аргументи передані, використовуємо їх, інакше резолвимо залежності через рефлексію

                if (empty($dependencies)) {
                    $parameters = $constructor->getParameters();

                    foreach ($parameters as $parameter) {

                        if (!$parameter->hasType() && !$parameter->isDefaultValueAvailable()) {
                            throw new NotFoundException("Cannot resolve untyped parameter \${$parameter->getName()} (w/o default value) in class {$resolved}");
                        }

                        if ($parameter->getType()->isBuiltin() && !$parameter->isDefaultValueAvailable()) {
                            throw new NotFoundException("Cannot resolve built-in type for parameter \${$parameter->getName()} (w/o default value) in class {$resolved}");
                        }

                        $default = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;

                        $dependencies[] = $default ?? $this->getDependency($parameter);
                    }
                }

                $service = $reflection->newInstanceArgs($dependencies);
                //$service = new $resolved(...$dependencies);
            }

            $this
                ->invokeInjector($service)
                ->lifecycle($resolved, $service);

        } finally {
            $this->unmarkResolving($id);
        }

        return $service;
    }

    /**
     * @param \ReflectionParameter $parameter
     * 
     * @return object
     */
    private function getDependency(\ReflectionParameter $parameter): object
    {
        $dependency = $parameter->getType()?->getName();
        if ($dependency === null) {
            throw new NotFoundException("Cannot resolve untyped parameter \${$parameter->getName()}");
        }

         return $this->get($dependency);
    }

    /**
     * Мега простий резолвер (зараз для інтерфейсів щось типу RequestInterface -> Request), для зручності і можливого маштабування
     * 
     * @param string $id
     * 
     * @return string
     */
    protected function resolve(string $id): string
    {
         if (str_ends_with($id, 'Interface')) {
            $id = str_replace('Interface', '', $id);
        }

        // можна якісь біндінги резолвити також, але поки що так

        return $id;
    }

    /**
     * @param string $id
     * @param mixed $service
     * 
     * @return static
     */
    protected function lifecycle(string $id, $service): static
    {
        // простий спосіб позначити, що сервіс має бути спільнимі розшарити
        if ($service instanceof SharedInterface) {
            $this->share($id, $service);
        }

        return $this;
    }

    /**
     * коли дійшов до http-хендлерів, то вирішив додати можливість інжекції через атрибути, щоб не захаращувати конструктори і не робити їх надто великими, але це можна використовувати і для інших сервісів
     * ще добавлю метод для інжекції в сервіси через атрибути, поки що тільки для методів, але можна і для властивостей
     * 
     * @param object $service
     * 
     * @return static
     */
    protected function invokeInjector(object $service): static
    {
        $reflection = new ReflectionClass($service);
        $isInjectable = count($reflection->getAttributes(Injectable::class)) > 0;

        if (!$isInjectable) {
            return $this;
        }

        foreach ($reflection->getMethods() as $method) {
            $injectAttributes = $method->getAttributes(Injector::class);
            if (count($injectAttributes) > 0) {
                $parameters = $method->getParameters();
                $dependencies = [];

                foreach ($parameters as $parameter) {
                    if (!$parameter->hasType() && !$parameter->isDefaultValueAvailable()) {
                        throw new NotFoundException("Cannot resolve untyped parameter \${$parameter->getName()} (w/o default value) in method {$method->getName()} of class {$reflection->getName()}");
                    }

                    if ($parameter->getType()->isBuiltin() && !$parameter->isDefaultValueAvailable()) {
                        throw new NotFoundException("Cannot resolve built-in type for parameter \${$parameter->getName()} (w/o default value) in method {$method->getName()} of class {$reflection->getName()}");
                    }

                    $default = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;

                    $dependencies[] = $default ?? $this->getDependency($parameter);
                }

                $method->invokeArgs($service, $dependencies);
            }
        }

        return $this;
    }

     /**
     * @param string $id
     * 
     * @return bool
     */

    /**
     * @param string $id
     * 
     * @return static
     * @throws CircularDependencyException
     */
    private function assertNotResolving(string $id): static
    {
        if (isset($this->resolving[$id])) {
            throw new CircularDependencyException("Circular dependency detected while resolving '{$id}'");
        }
        return $this;
    }

    /**
     * @param string $id
     * 
     * @return void
     */
    private function markResolving(string $id): static
    {
        $this->resolving[$id] = true;

        return $this;
    }

    /**
     * @param string $id
     * 
     * @return void
     */
    private function unmarkResolving(string $id): static
    {
        unset($this->resolving[$id]);

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $instance
     * 
     * @return static
     */
    private function share(string $name, mixed $instance): static
    {
        $this->getSharedRepository()->share($name, $instance);

        return $this;
    }

    /**
     * @param string $name
     * 
     * @return bool
     */
    private function hasShared(string $name): bool
    {
        return $this->getSharedRepository()->get($name) !== null;
    }

    /**
     * @param string $name
     * 
     * @return object|null
     */
    private function getShared(string $name): ?object
    {
        return $this->getSharedRepository()->get($name);
    }

    /**
     * @return SharedRepository
     */
    private function getSharedRepository(): SharedRepository
    {
        return $this->shared ??= new SharedRepository();
    }
    
}
