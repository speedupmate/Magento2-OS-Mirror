<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Block\Adminhtml\Configuration;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Request;
use Temando\Shipping\Model\PackagingInterface;

/**
 * Temando Packaging Component Test
 *
 * @package  Temando\Shipping\Test\Integration
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class PackagingComponentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PackagingComponent
     */
    private $block;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->request = $this->getMockBuilder(Request::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $context = $objectManager->create(Context::class, ['request' => $this->request]);

        /** @var LayoutInterface $layout */
        $layout = $objectManager->get(LayoutInterface::class);

        $this->block = $layout->createBlock(PackagingComponent::class, '', ['context' => $context]);
    }

    /**
     * @test
     */
    public function getContainerIdFromRequestParams()
    {
        $containerId = '00000000-4000-0004-0000-000000000000';

        $this->request
            ->expects($this->any())
            ->method('getParam')
            ->with(PackagingInterface::PACKAGING_ID)
            ->willReturn($containerId);

        $this->assertEquals($containerId, $this->block->getContainerId());
    }
}
