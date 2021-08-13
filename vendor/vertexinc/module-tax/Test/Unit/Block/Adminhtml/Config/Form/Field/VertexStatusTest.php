<?php declare(strict_types=1);

namespace Vertex\Tax\Test\Unit\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\Element\AbstractElement;
use PHPUnit\Framework\MockObject\MockObject;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\VertexStatus;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ConfigurationValidator;
use Vertex\Tax\Model\ConfigurationValidator\Result;
use Vertex\Tax\Test\Unit\TestCase;

class VertexStatusTest extends TestCase
{
    /** @var MockObject|Context */
    private $contextMock;

    /** @var MockObject|AbstractElement */
    private $abstractElementMock;

    /** @var MockObject|Config */
    private $configMock;

    /** @var MockObject|VertexStatus */
    private $block;

    /** @var MockObject|VertexStatus */
    private $blockMock;

    /** @var MockObject|Http */
    private $httpMock;

    /** @var MockObject|ConfigurationValidator */
    private $configurationValidatorMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contextMock = $this->createPartialMock(Context::class, ['getRequest']);
        $this->configMock = $this->createMock(Config::class);
        $this->abstractElementMock = $this->createMock(AbstractElement::class);
        $this->httpMock = $this->createPartialMock(Http::class, ['getParam']);
        $this->blockMock = $this->createPartialMock(VertexStatus::class, ['getRequest']);
        $this->configurationValidatorMock = $this->createPartialMock(ConfigurationValidator::class, ['execute']);

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->httpMock);

        $this->block = $this->getObject(
            VertexStatus::class,
            [
                'context' => $this->contextMock,
                'config' => $this->configMock,
                'configurationValidator' => $this->configurationValidatorMock,
            ]
        );
    }

    public function testStatusDisabled()
    {
        $expected = '<span class="grid-severity-critical"><span>Disabled</span></span>';
        $actual = $this->invokeInaccessibleMethod($this->block, '_getElementHtml', $this->abstractElementMock);

        $this->assertEquals($expected, $actual);
    }

    public function testStatusNotValid()
    {
        $expected = '<span class="grid-severity-minor"><span>Invalid</span></span>';
        $this->configMock->expects($this->once())
            ->method('isVertexActive')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('isTaxCalculationEnabled')
            ->willReturn(true);

        $credentialResult = $this->getObject(Result::class)
            ->setValid(false)
            ->setMessage('Invalid')
            ->setArguments([]);

        $this->configurationValidatorMock->method('execute')
            ->willReturn($credentialResult);

        $actual = $this->invokeInaccessibleMethod($this->block, '_getElementHtml', $this->abstractElementMock);

        $this->assertEquals($expected, $actual);
    }

    public function testStatusIsValid()
    {
        $expected = '<span class="grid-severity-notice"><span>Valid</span></span>';
        $this->configMock->expects($this->once())
            ->method('isVertexActive')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('isTaxCalculationEnabled')
            ->willReturn(true);

        $credentialResult = $this->getObject(Result::class)
            ->setValid(true);

        $this->configurationValidatorMock->method('execute')
            ->willReturn($credentialResult);

        $actual = $this->invokeInaccessibleMethod($this->block, '_getElementHtml', $this->abstractElementMock);
        $this->assertEquals($expected, $actual);
    }
}
