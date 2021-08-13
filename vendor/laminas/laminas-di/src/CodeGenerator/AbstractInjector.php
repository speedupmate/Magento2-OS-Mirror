<?php

/**
 * @see       https://github.com/laminas/laminas-di for the canonical source repository
 * @copyright https://github.com/laminas/laminas-di/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Di\CodeGenerator;

use Laminas\Di\DefaultContainer;
use Laminas\Di\InjectorInterface;
use Psr\Container\ContainerInterface;

/**
 * Abstract class for code generated dependency injectors
 */
abstract class AbstractInjector implements InjectorInterface
{
    /** @var string[]|FactoryInterface[] */
    protected $factories = [];

    /** @var FactoryInterface[] */
    private $factoryInstances = [];

    /** @var ContainerInterface */
    private $container;

    /** @var InjectorInterface */
    private $injector;

    /**
     * {@inheritDoc}
     */
    public function __construct(InjectorInterface $injector, ?ContainerInterface $container = null)
    {
        $this->injector  = $injector;
        $this->container = $container ?: new DefaultContainer($this);

        $this->loadFactoryList();
    }

    /**
     * Init factory list
     */
    abstract protected function loadFactoryList(): void;

    private function setFactory(string $type, FactoryInterface $factory): void
    {
        $this->factoryInstances[$type] = $factory;
    }

    private function getFactory(string $type): FactoryInterface
    {
        if (isset($this->factoryInstances[$type])) {
            return $this->factoryInstances[$type];
        }

        $factoryClass = $this->factories[$type];
        $factory      = $factoryClass instanceof FactoryInterface ? $factoryClass : new $factoryClass();

        $this->setFactory($type, $factory);

        return $factory;
    }

    public function canCreate(string $name): bool
    {
        return isset($this->factories[$name]) || $this->injector->canCreate($name);
    }

    /** @return mixed */
    public function create(string $name, array $options = [])
    {
        if (isset($this->factories[$name])) {
            return $this->getFactory($name)->create($this->container, $options);
        }

        return $this->injector->create($name, $options);
    }
}
