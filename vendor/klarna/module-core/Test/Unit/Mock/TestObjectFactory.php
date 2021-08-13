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

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Factory to create the test objects that each test class will
 * use to run the tests against. Automatically finds and sets up
 * mocks for all class dependencies.
 *
 */
class TestObjectFactory extends TestCase
{
    /**
     * @var array
     */
    private $dependencyMocks;

    /**
     * @var MockFactory
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
     * @param array $instanceMocks
     * @return object
     */
    public function create(string $className, array $methodsToMock = [], array $instanceMocks = [])
    {
        try {
            $objectManagerHelper = new ObjectManager($this);
            $reflection = new \ReflectionClass($className);

            $constructor = $reflection->getConstructor();
            if ($constructor !== null) {
                $params = $constructor->getParameters();

                foreach ($params as $param) {
                    if ($this->isObject($param->getType())) {
                        $paramClass = $param->getType()->getName();

                        $paramMockMethods = $this->getParamMockMethods($methodsToMock, $paramClass);
                        $dependencyMock = $this->mockFactory->create($paramClass, $paramMockMethods);
                        if (isset($instanceMocks[$paramClass])) {
                            $dependencyMock = $instanceMocks[$paramClass];
                        }

                        $this->dependencyMocks[$param->getName()] = $dependencyMock;

                        continue;
                    }
                    $this->handleSpecialConstructorInstanceCases($param);
                }
            }

            return $objectManagerHelper->getObject($className, $this->dependencyMocks);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Returns true when the given parameter can be used as a mocked instance
     *
     * @param null|\ReflectionNamedType $type
     * @return bool
     */
    private function isObject($type): bool
    {
        return $type instanceof \ReflectionNamedType &&
            !in_array($type->getName(), ['array', 'string']);
    }

    /**
     * Adding special instancs of the constructor (array, ...) to the depency mock attribute
     *
     * @param \ReflectionParameter $param
     */
    private function handleSpecialConstructorInstanceCases(\ReflectionParameter $param): void
    {
        if ($param->getType() === null) {
            $this->dependencyMocks[$param->getName()] = '';
            return;
        }

        $paramClass = $param->getType()->getName();
        if ($paramClass === 'array') {
            $this->dependencyMocks[$param->getName()] = [];
            return;
        } elseif ($paramClass === 'string') {
            $this->dependencyMocks[$param->getName()] = '';
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
