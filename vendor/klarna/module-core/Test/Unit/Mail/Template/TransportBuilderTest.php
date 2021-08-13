<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Mail\Template;

use Klarna\Core\Mail\Template\TransportBuilder;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\MimeMessageInterface;
use Magento\Framework\Mail\MimePartInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Core\Mail\Template\TransportBuilder
 */
class TransportBuilderTest extends TestCase
{
    /**
     * @var TransportBuilder
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::getTransport()
     */
    public function testGetTransportWithNoAttachments(): void
    {
        $this->setUpGetTransportTest();

        $result = $this->model->getTransport();

        $parts = $result->getMessage()->getBody()->getParts();

        self::assertCount(1, $parts);
        self::assertSame('BODY_TEXT', $parts[0]->getContent());
    }

    /**
     * @covers ::getTransport()
     */
    public function testGetTransportWithTwoDifferentAttachments(): void
    {
        $this->setUpGetTransportTest();

        $this->model->addAttachment('attachmentContent1', 'fileName1');
        $this->model->addAttachment('attachmentContent2', 'fileName2');
        $result = $this->model->getTransport();

        $parts = $result->getMessage()->getBody()->getParts();

        self::assertCount(3, $parts);
        self::assertSame('BODY_TEXT', $parts[0]->getContent());
        self::assertSame('attachmentContent1', $parts[1]->getContent());
        self::assertSame('attachmentContent2', $parts[2]->getContent());
    }

    /**
     * @covers ::addAttachment()
     * @doesNotPerformAssertions
     */
    public function testAddAttachment(): void
    {
        $this->dependencyMocks['mimePartInterfaceFactory']
            ->expects($this->once())
            ->method('create')
            ->with([
                'content' => 'content1',
                'fileName' => 'fileName1',
                'disposition' => 'attachment',
                'encoding' => 'base64',
                'type' => 'application/octet-stream',
            ]);

        $this->model->addAttachment('content1', 'fileName1');
    }

    /**
     * Set up of the getTransport() tests
     */
    private function setUpGetTransportTest()
    {
        $templateNamespace = 'TEMPLATE_MODEL';
        $templateType = TemplateTypesInterface::TYPE_HTML;
        $bodyText = 'BODY_TEXT';
        $vars = [];
        $options = [];

        $this->model->setTemplateModel($templateNamespace);

        $body = $this->getMockForAbstractClass(MimeMessageInterface::class);
        $this->dependencyMocks['mimeMessageInterfaceFactory']->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($input) use ($body){
                $body
                    ->method('getParts')
                    ->willReturn($input['parts']);
            });

        /** @var EmailMessageInterface|MockObject $emailMessage */
        $emailMessage = $this->getMockForAbstractClass(EmailMessageInterface::class);
        $this->dependencyMocks['emailMessageInterfaceFactory']->expects($this->any())
            ->method('create')
            ->willReturnCallback(function() use ($emailMessage, $body){
                $emailMessage
                    ->method('getBody')
                    ->willReturn($body);
                return $emailMessage;
            });

        $transport = $this->getMockForAbstractClass(TransportInterface::class);
        $this->dependencyMocks['mailTransportFactory']->expects($this->at(0))
            ->method('create')
            ->willReturnCallback(function ($array) use ($transport){
                $transport
                    ->method('getMessage')
                    ->willReturn($array['message']);
                return $transport;
            });

        $this->dependencyMocks['mimePartInterfaceFactory']
            ->method('create')
            ->willReturnCallback(function ($input) {
                /** @var MimePartInterface|MockObject $mimePartMock */
                $mimePartMock = $this->getMockForAbstractClass(MimePartInterface::class);

                $mimePartMock
                    ->method('getContent')
                    ->willReturn($input['content']);
                return $mimePartMock;
            });

        $template = $this->getMockForAbstractClass(TemplateInterface::class);
        $template->expects($this->once())->method('setVars')->with($vars)->willReturnSelf();
        $template->expects($this->once())->method('setOptions')->with($options)->willReturnSelf();
        $template->expects($this->once())->method('getSubject')->willReturn('Email Subject');
        $template->expects($this->once())->method('getType')->willReturn($templateType);
        $template->expects($this->once())->method('processTemplate')->willReturn($bodyText);

        $this->dependencyMocks['templateFactory']->expects($this->once())
            ->method('get')
            ->with('identifier', $templateNamespace)
            ->willReturn($template);

        $this->model->setTemplateIdentifier('identifier')->setTemplateVars($vars)->setTemplateOptions($options);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $mockFactory           = new MockFactory();
        $objectFactory         = new TestObjectFactory($mockFactory);
        $this->model           = $objectFactory->create(TransportBuilder::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
