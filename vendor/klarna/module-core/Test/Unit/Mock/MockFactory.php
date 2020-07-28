<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Mock;

use PHPUnit\Framework\TestCase;

/**
 * Factory to create PHPUnit MockObjects. Runs the default methods
 * we want to run on all MockObjects and updates the object based on
 * the given parameters.
 *
 * @package Klarna\Core\Test\Unit\Mock
 */
class MockFactory extends TestCase
{
    /**
     * Creates and returns a unique PHPUnit MockObject
     *
     * @param string $className
     * @param array $methods
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function create(string $className, array $methods = [])
    {
        $mock = $this->getMockBuilder($className);
        $mock->disableOriginalConstructor();

        if (!empty($methods)) {
            $mock->setMethods($methods);
        }

        return $mock->getMock();
    }
}
