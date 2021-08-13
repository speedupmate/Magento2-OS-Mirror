<?php

/**
 * @see       https://github.com/laminas/laminas-di for the canonical source repository
 * @copyright https://github.com/laminas/laminas-di/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Di;

use Psr\Container\ContainerInterface;

use function get_class;

/**
 * Default IoC container implementation.
 *
 * This is using the dependency injector to create instances.
 */
class DefaultContainer implements ContainerInterface
{
    /**
     * Dependency injector
     *
     * @var InjectorInterface
     */
    protected $injector;

    /**
     * Registered services and cached values
     *
     * @var array
     */
    protected $services = [];

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;

        $this->services[InjectorInterface::class]  = $injector;
        $this->services[ContainerInterface::class] = $this;
        $this->services[get_class($injector)]      = $injector;
        $this->services[static::class]             = $this;
    }

    /**
     * Explicitly set a service
     *
     * @param string $name The name of the service retrievable by get()
     * @param object $service The service instance
     */
    public function setInstance(string $name, $service): self
    {
        $this->services[$name] = $service;
        return $this;
    }

    /**
     * Check if a service is available
     *
     * @see ContainerInterface::has()
     *
     * @param string $name
     * @return mixed
     */
    public function has($name)
    {
        if (isset($this->services[$name])) {
            return true;
        }

        return $this->injector->canCreate($name);
    }

    /**
     * Retrieve a service
     *
     * Tests first if a service is registered, and, if so,
     * returns it.
     *
     * If the service is not yet registered, it is attempted to be created via
     * the dependency injector and then it is stored for further use.
     *
     * @see ContainerInterface::get()
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if (! isset($this->services[$name])) {
            $this->services[$name] = $this->injector->create($name);
        }

        return $this->services[$name];
    }
}
