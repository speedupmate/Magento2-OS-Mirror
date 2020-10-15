<?php
/**
 * This file is part of the Klarna KpGraphQl module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Test\ApiFunctional;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;

/**
 * Functional tests for the CreateKlarnaPaymentsSession mutatioon
 */
class CreateKlarnaPaymentsSessionTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testKpSessionReturnsErrorWhenNoCartIdProvided(): void
    {
        $mutation = $this->createKlarnaPaymentsSessionMutation('');
        self::expectExceptionMessage('Required parameter \'cart_id\' is missing');
        $this->graphQlMutation($mutation);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoConfigFixture default_store payment/klarna_kp/active 0
     */
    public function testKpSessionReturnsErrorWhenKlarnaIsDisabled(): void
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $mutation = $this->createKlarnaPaymentsSessionMutation($maskedQuoteId);
        self::expectExceptionMessage('Klarna Payments method is not active');
        $this->graphQlMutation($mutation);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoConfigFixture default_store payment/klarna_kp/active 1
     */
    public function testCreateKlarnaPaymentsSessionReturnsClientToken(): void
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $mutation = $this->createKlarnaPaymentsSessionMutation($maskedQuoteId);
        $response = $this->graphQlMutation($mutation);
        self::assertArrayHasKey('client_token', $response['createKlarnaPaymentsSession']);
    }

    /**
     * @param int $maskedQuoteId
     * @return string
     */
    private function createKlarnaPaymentsSessionMutation($maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  createKlarnaPaymentsSession(input: {cart_id: "$maskedQuoteId"}) {
    client_token
    payment_method_categories {
      identifier
      name
    }
  }
}
QUERY;
    }
}
