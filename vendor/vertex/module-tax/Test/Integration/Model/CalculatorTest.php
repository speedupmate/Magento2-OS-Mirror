<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Test\Integration\Model;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Vertex\Tax\Test\Integration\Scenarios\QuoteWithPaymentAndInvalidAddress;
use Vertex\Tax\Test\Integration\TestCase;

/**
 * Ensure that Calculator works properly
 */
class CalculatorTest extends TestCase
{
    /** @var QuoteWithPaymentAndInvalidAddress */
    private $quoteWithPaymentAndInvalidAddress;

    /** @inheritdoc */
    protected function setUp()
    {
        parent::setUp();
        $this->quoteWithPaymentAndInvalidAddress = $this->getObject(
            QuoteWithPaymentAndInvalidAddress::class
        );
    }

    /**
     * Test if vertex error message is shown on frontend
     *
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoCache all disabled
     * @magentoConfigFixture default_store tax/vertex_settings/enable_vertex 1
     * @magentoConfigFixture default_store tax/vertex_settings/api_url https://example.org/CalculateTax70
     */
    public function testShowErrorMessageToCustomer()
    {
        $soapClient = $this->createPartialMock(\SoapClient::class, ['CalculateTax70']);
        $soapClient->expects($this->atLeastOnce())
            ->method('CalculateTax70')
            ->with(
                $this->callback(
                    function (\stdClass $request) {
                        $destination = $request->QuotationRequest->Customer->Destination;
                        if ($destination->PostalCode !== QuoteWithPaymentAndInvalidAddress::INVALID_POSTAL_CODE) {
                            $this->fail(
                                'Post code is valid, please use \''
                                . QuoteWithPaymentAndInvalidAddress::INVALID_POSTAL_CODE . '\''
                            );

                            return false;
                        }
                        $this->assertEquals(
                            QuoteWithPaymentAndInvalidAddress::INVALID_POSTAL_CODE,
                            $destination->PostalCode
                        );

                        return true;
                    }
                )
            )
            ->willReturn(new \stdClass());
        $this->getSoapFactory()->setSoapClient($soapClient);

        $cart = $this->quoteWithPaymentAndInvalidAddress->create('vertex_cart_with_invalid_address');

        /** @var TotalsInformationInterface $totalsInfo */
        $totalsInfo = $this->createObject(TotalsInformationInterface::class);
        $totalsInfo->setAddress($cart->getBillingAddress());
        $totalsInfo->setShippingCarrierCode('flatrate');
        $totalsInfo->setShippingMethodCode('flatrate');

        /** @var TotalsInformationManagementInterface $totalsManagement */
        $totalsManagement = $this->getObject(TotalsInformationManagementInterface::class);
        $totals = $totalsManagement->calculate($cart->getId(), $totalsInfo);

        $messages = $totals->getTotalSegments()['tax']->getExtensionAttributes()->getVertexTaxCalculationMessages();

        $this->assertInternalType('array', $messages);
        $this->assertContains(
            'Unable to calculate taxes. This could be caused by an invalid address provided in checkout.',
            $messages
        );
    }

    /**
     * Test if vertex error message is shown on admin
     *
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoCache all disabled
     * @magentoConfigFixture default_store tax/vertex_settings/enable_vertex 1
     * @magentoConfigFixture default_store tax/vertex_settings/api_url https://example.org/CalculateTax70
     */
    public function testShowErrorMessageToAdminUser()
    {
        $soapClient = $this->createPartialMock(\SoapClient::class, ['CalculateTax70']);
        $soapClient->expects($this->atLeastOnce())
            ->method('CalculateTax70')
            ->with(
                $this->callback(
                    function (\stdClass $request) {
                        $destination = $request->QuotationRequest->Customer->Destination;
                        if ($destination->PostalCode !== QuoteWithPaymentAndInvalidAddress::INVALID_POSTAL_CODE) {
                            $this->fail(
                                'Post code is valid, please use \''
                                . QuoteWithPaymentAndInvalidAddress::INVALID_POSTAL_CODE . '\''
                            );

                            return false;
                        }
                        $this->assertEquals(
                            QuoteWithPaymentAndInvalidAddress::INVALID_POSTAL_CODE,
                            $destination->PostalCode
                        );

                        return true;
                    }
                )
            )
            ->willReturn(new \stdClass());
        $this->getSoapFactory()->setSoapClient($soapClient);

        $cart = $this->quoteWithPaymentAndInvalidAddress->create('vertex_cart_with_invalid_address');

        /** @var TotalsInformationInterface $totalsInfo */
        $totalsInfo = $this->createObject(TotalsInformationInterface::class);
        $totalsInfo->setAddress($cart->getBillingAddress());
        $totalsInfo->setShippingCarrierCode('flatrate');
        $totalsInfo->setShippingMethodCode('flatrate');

        /** @var TotalsInformationManagementInterface $totalsManagement */
        $totalsManagement = $this->getObject(TotalsInformationManagementInterface::class);
        $totalsManagement->calculate($cart->getId(), $totalsInfo);

        /** @var ManagerInterface $messageManager */
        $messageManager = $this->getObject(ManagerInterface::class);

        $messages = $messageManager->getMessages(true);

        $this->assertInstanceOf(Collection::class, $messages);

        $this->assertContainsOnlyInstancesOf(MessageInterface::class, $messages->getItems());
        $this->assertEquals(1, $messages->getCount());

        /** @var MessageInterface $message */
        $message = current($messages->getItems());

        $this->assertContains(
            'Unable to calculate taxes. This could be caused by an invalid address provided in checkout.',
            $message->getText()
        );
    }
}
