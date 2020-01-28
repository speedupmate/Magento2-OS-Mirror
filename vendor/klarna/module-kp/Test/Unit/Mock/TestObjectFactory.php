<?php
/**
 * This file is part of the Klarna Kp module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Test\Unit\Mock;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Factory to create the test objects that each test class will
 * use to run the tests against. Automatically finds and sets up
 * mocks for all class dependencies.
 *
 * @package Klarna\Kp\Test\Unit\Mock
 */
class TestObjectFactory extends TestCase
{
    /**
     * @var array
     */
    private $dependencyMocks;

    /**
     * @var \Klarna\Kp\Test\Unit\Mock\MockFactory
     */
    private $mockFactory;

    /**
     * @param MockFactory $mockFactory
     */
    public function __construct(MockFactory $mockFactory)
    {
        parent::__construct();
        $this->dependencyMocks = [];
        $this->mockFactory = $mockFactory;
    }

    /**
     * Reflects over the given class to find and insert all dependencies
     * into a test object which is returned and used for testing the class.
     *
     * Some mocked dependencies need some or all of their methods defined and/or stubbed.
     * That's where $methodsToMock comes in.
     *
     * @param string $className
     * @param array $methodsToMock
     * @return object
     */
    public function create(string $className, array $methodsToMock = [])
    {
        try {
            $objectManagerHelper = new ObjectManager($this);
            $reflection = new \ReflectionClass($className);
            if (!$reflection->getConstructor()) {
                return $objectManagerHelper->getObject($className);
            }
            $params = $reflection->getConstructor()->getParameters();

            foreach ($params as $param) {
                $paramName = $param->getName();
                $paramClass = $param->getType()->getName();
                $this->dependencyMocks[$paramName] = [];
                if ($paramClass !== "array") {
                    $paramMockMethods = $this->getParamMockMethods($methodsToMock, $paramClass);
                    $dependencyMock = $this->mockFactory->create($paramClass, $paramMockMethods);
                    $this->dependencyMocks[$paramName] = $dependencyMock;
                }
            }

            return $objectManagerHelper->getObject($className, $this->dependencyMocks);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Returns all dependency mocks connected to the class given in ::create
     *
     * @return \PHPUnit\Framework\MockObject\MockObject[]
     */
    public function getDependencyMocks(): array
    {
        return $this->dependencyMocks;
    }

    /**
     * Returns an array of all methods that are to be mocked for the given dependency
     *
     * @param array $methodsToMock
     * @param string $paramClass
     * @return array
     */
    private function getParamMockMethods(array $methodsToMock, string $paramClass): array
    {
        if (!isset($methodsToMock[$paramClass])) {
            return [];
        }

        return $methodsToMock[$paramClass];
    }
}
