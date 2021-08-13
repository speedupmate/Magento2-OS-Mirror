<?php

/**
 * @see       https://github.com/laminas/laminas-di for the canonical source repository
 * @copyright https://github.com/laminas/laminas-di/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Di\Container;

use Laminas\Di\Exception;
use Laminas\Di\InjectorInterface;
use Psr\Container\ContainerInterface;

/**
 * Create instances with autowiring
 */
class AutowireFactory
{
    /**
     * Retrieves the injector from a container
     *
     * @param ContainerInterface $container The container context for this factory
     * @return InjectorInterface The dependency injector
     * @throws Exception\RuntimeException When no dependency injector is available.
     */
    private function getInjector(ContainerInterface $container)
    {
        $injector = $container->get(InjectorInterface::class);

        if (! $injector instanceof InjectorInterface) {
            throw new Exception\RuntimeException(
                'Could not get a dependency injector form the container implementation'
            );
        }

        return $injector;
    }

    /**
     * Check creatability of the requested name
     *
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (! $container->has(InjectorInterface::class)) {
            return false;
        }

        return $this->getInjector($container)->canCreate((string) $requestedName);
    }

    /**
     * Create an instance
     *
     * @return object
     */
    public function create(ContainerInterface $container, string $requestedName, ?array $options = null)
    {
        return $this->getInjector($container)->create($requestedName, $options ?: []);
    }

    /**
     * Make invokable and implement the laminas-service factory pattern
     *
     * @param string $requestedName
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return $this->create($container, (string) $requestedName, $options);
    }
}
