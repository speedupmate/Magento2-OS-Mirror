<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Unit\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vertex\Tax\Model\Config\Source\DeliveryTerm;
use Vertex\Tax\Test\Unit\TestCase;

/**
 * Test Class @see DeliveryTerm
 */
class DeliveryTermTest extends TestCase
{
    /**
     * Check if object is an instance of @see OptionSourceInterface
     *
     * @return void
     */
    public function testImplementsOptionSourceInterface()
    {
        $object = $this->createObject();
        $this->assertInstanceOf(OptionSourceInterface::class, $object);
    }

    /**
     * Test if type is an array
     *
     * @return void
     */
    public function testReturnArray()
    {
        $object = $this->createObject();
        $this->assertIsArray($object->toOptionArray());
    }

    /**
     * Create Object DeliveryTerm
     *
     * @return DeliveryTerm
     */
    private function createObject()
    {
        return $this->getObject(DeliveryTerm::class);
    }
}
