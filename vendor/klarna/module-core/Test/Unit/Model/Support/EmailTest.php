<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Model\Support;

use Klarna\Core\Model\Support\Email;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use Magento\Email\Model\Transport;

/**
 * @coversDefaultClass \Klarna\Core\Model\Support\Email
 */
class EmailTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var Email
     */
    private $email;
    /**
     * @var array
     */
    private $dependencyMocks;
    /**
     * @var string[]
     */
    private $contentData;

    /**
     * @covers ::getTemplateContent()
     */
    public function testGetTemplateContentReturnsArray(): void
    {
        static::assertIsArray($this->email->getTemplateContent($this->contentData));
    }

    /**
     * @covers ::getTemplateContent()
     */
    public function testGetTemplateContentModuleListJustHasKlarna(): void
    {
        $result = $this->email->getTemplateContent($this->contentData);
        static::assertFalse(strpos($result['module_versions'], 'Any_random_module'));
    }

    /**
     * @covers ::getTemplateContent()
     */
    public function testGetTemplateContentReturnsNotEmptyPhpVersion(): void
    {
        $result = $this->email->getTemplateContent($this->contentData);
        static::assertNotEmpty($result['php_version']);
    }

    /**
     * @covers ::getTemplateContent()
     */
    public function testGetTemplateContentReturnsCorrectShopInformation(): void
    {
        $result = $this->email->getTemplateContent($this->contentData);
        static::assertEquals('2.4.3 Community', $result['shop_version']);
    }

    /**
     * @covers ::getTemplateContent()
     */
    public function testGetTemplateContentRemovedCodeInInput(): void
    {
        $this->contentData['contact_name'] = "<script>my script.</script><?php my php code ?>";
        $result = $this->email->getTemplateContent($this->contentData);
        static::assertEquals('my script.', $result['contact_name']);
    }

    /**
     * @covers ::send()
     * @doesNotPerformAssertions
     */
    public function testSendUsingTheCorrectTemplate(): void
    {
        $this->setUpSendTest();

        $this->dependencyMocks['transportBuilder']->method('setTemplateIdentifier')
            ->with(Email::TEMPLATE_NAME);

        $this->email->send($this->contentData);
    }

    /**
     * @covers ::send()
     * @doesNotPerformAssertions
     */
    public function testSendWithoutAttachments(): void
    {
        $this->setUpSendTest();

        $this
            ->dependencyMocks['transportBuilder']
            ->expects($this->never())
            ->method('addAttachment');

        $this->email->send($this->contentData);
    }

    /**
     * @covers ::send()
     * @doesNotPerformAssertions
     */
    public function testSendWithOneAttachment(): void
    {
        $this->setUpSendTest();

        $this->contentData['attachment'] = [
            [
                'path' => '/path',
                'file' => 'file_1.jpg',
                'name' => 'file (1).jpg',
            ]
        ];

        $this
            ->dependencyMocks['driverInterface']
            ->expects($this->once())
            ->method('fileGetContents')
            ->with('/path/file_1.jpg')
            ->willReturn('content1');

        $this
            ->dependencyMocks['transportBuilder']
            ->expects($this->once())
            ->method('addAttachment')
            ->with('content1', 'file (1).jpg');

        $this->email->send($this->contentData);
    }

    /**
     * @covers ::send()
     * @doesNotPerformAssertions
     */
    public function testSendWithKlarnaSettingsUnselected(): void
    {
        $this->setUpSendTest();

        $this
            ->dependencyMocks['infoExtractor']
            ->expects($this->never())
            ->method('getKlarnaInfo');

        $this->email->send($this->contentData);
    }

    /**
     * @covers ::send()
     * @doesNotPerformAssertions
     */
    public function testSendWithKlarnaSettingsNoSelected(): void
    {
        $this->setUpSendTest();

        $this->contentData['include_klarna_settings'] = "2";

        $this
            ->dependencyMocks['infoExtractor']
            ->expects($this->never())
            ->method('getKlarnaInfo');

        $this->email->send($this->contentData);
    }

    /**
     * @covers ::send()
     * @doesNotPerformAssertions
     */
    public function testSendWithKlarnaSettingsYesSelected(): void
    {
        $this->setUpSendTest();

        $this->contentData['include_klarna_settings'] = "1";

        $this
            ->dependencyMocks['infoExtractor']
            ->expects($this->once())
            ->method('getKlarnaInfo')
            ->willReturn([
                'name1' => [
                    'klarna/one' => "string",
                    'klarna/two' => 2,
                    'klarna/three' => null,
                ]
            ]);

        $this
            ->dependencyMocks['transportBuilder']
            ->expects($this->once())
            ->method('addAttachment')
            ->with(
                "{\n".
                "    \"klarna/one\": \"string\",\n".
                "    \"klarna/two\": 2,\n".
                "    \"klarna/three\": null\n".
                "}", 'name1.json'
            );

        $this->email->send($this->contentData);
    }

    /**
     * @covers ::send()
     * @doesNotPerformAssertions
     */
    public function testSendWithTaxSettingsUnselected(): void
    {
        $this->setUpSendTest();

        $this
            ->dependencyMocks['infoExtractor']
            ->expects($this->never())
            ->method('getTaxInfo');

        $this->email->send($this->contentData);
    }

    /**
     * @covers ::send()
     * @doesNotPerformAssertions
     */
    public function testSendWithTaxSettingsNoSelected(): void
    {
        $this->setUpSendTest();

        $this->contentData['include_tax_settings'] = "2";

        $this
            ->dependencyMocks['infoExtractor']
            ->expects($this->never())
            ->method('getTaxInfo');

        $this->email->send($this->contentData);
    }

    /**
     * @covers ::send()
     * @doesNotPerformAssertions
     */
    public function testSendWithTaxSettingsYesSelected(): void
    {
        $this->setUpSendTest();

        $this->contentData['include_tax_settings'] = "1";

        $this
            ->dependencyMocks['infoExtractor']
            ->expects($this->once())
            ->method('getTaxInfo')
            ->willReturn([
                'name1' => [
                    'tax/one' => "string",
                    'tax/two' => 2,
                    'tax/three' => null,
                ]
            ]);

        $this
            ->dependencyMocks['transportBuilder']
            ->expects($this->once())
            ->method('addAttachment')
            ->with(
                "{\n".
                "    \"tax/one\": \"string\",\n".
                "    \"tax/two\": 2,\n".
                "    \"tax/three\": null\n".
                "}", 'name1.json'
            );

        $this->email->send($this->contentData);
    }

    private function setUpSendTest()
    {
        $this->contentData['contact_name'] = 'abc';
        $this->contentData['contact_email'] = 'def';

        $store = $this->mockFactory->create(Store::class);
        $this->dependencyMocks['storeManager']->method('getStore')
            ->willReturn($store);

        $transport = $this->mockFactory->create(Transport::class);
        $this->dependencyMocks['transportBuilder']->method('getTransport')
            ->willReturn($transport);
    }

    protected function setUp(): void
    {
        $this->mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($this->mockFactory);
        $this->email = $objectFactory->create(Email::class);

        $this->dependencyMocks = $objectFactory->getDependencyMocks();
        $this->dependencyMocks['moduleList']->method('getAll')
            ->willReturn(
                [
                    'Any_random_module' => ['setup_version' => '4.5.6'],
                    'Klarna_Core' => ['setup_version' => '7.8.9']
                ]
            );
        $this->dependencyMocks['productMetadata']->method('getVersion')
            ->willReturn('2.4.3');
        $this->dependencyMocks['productMetadata']->method('getEdition')
            ->willReturn('Community');

        $this->contentData = [
            'data' => [
                'KP',
                'Core'
            ]
        ];
    }
}
