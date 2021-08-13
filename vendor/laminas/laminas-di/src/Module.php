<?php

/**
 * @see       https://github.com/laminas/laminas-di for the canonical source repository
 * @copyright https://github.com/laminas/laminas-di/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Di;

/**
 * Provides Module functionality for Laminas applications
 *
 * To add the DI integration to your application use laminas frameworks component installer or
 * add `Laminas\\Di` to the Laminas modules list:
 *
 * <code>
 *  // application.config.php
 *  return [
 *      // ...
 *      'modules' => [
 *          'Laminas\\Di',
 *          // ...
 *      ]
 *  ];
 * </code>
 */
class Module
{
    /**
     * Returns the configuration for laminas-mvc
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
