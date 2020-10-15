<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Exception;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Add configurable product to cart testcases
 */
class AddConfigurableProductToCartSingleMutationTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductToCart()
    {
        $product = $this->getConfigurableProductInfo();
        $quantity = 2;
        $parentSku = $product['sku'];
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $valueIndex = $product['configurable_options'][0]['values'][1]['value_index'];

        $selectedConfigurableOptionsQuery = $this->generateSuperAttributesUIDQuery($attributeId, $valueIndex);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery(
            $maskedQuoteId,
            $product['sku'],
            2,
            $selectedConfigurableOptionsQuery
        );

        $response = $this->graphQlMutation($query);

        $cartItem = current($response['addProductsToCart']['cart']['items']);
        self::assertEquals($quantity, $cartItem['quantity']);
        self::assertEquals($parentSku, $cartItem['product']['sku']);
        self::assertArrayHasKey('configurable_options', $cartItem);

        $option = current($cartItem['configurable_options']);
        self::assertEquals($attributeId, $option['id']);
        self::assertEquals($valueIndex, $option['value_id']);
        self::assertArrayHasKey('option_label', $option);
        self::assertArrayHasKey('value_label', $option);
    }

    /**
     * Generates UID for super configurable product super attributes
     *
     * @param int $attributeId
     * @param int $valueIndex
     * @return string
     */
    private function generateSuperAttributesUIDQuery(int $attributeId, int $valueIndex): string
    {
        return 'selected_options: ["' . base64_encode("configurable/$attributeId/$valueIndex") . '"]';
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/configurable_products_with_custom_attribute_layered_navigation.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductWithWrongSuperAttributes()
    {
        $product = $this->getConfigurableProductInfo();
        $quantity = 2;
        $parentSku = $product['sku'];

        $selectedConfigurableOptionsQuery = $this->generateSuperAttributesUIDQuery(0, 0);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $quantity,
            $selectedConfigurableOptionsQuery
        );

        $response =  $this->graphQlMutation($query);

        self::assertEquals(
            'You need to choose options for your item.',
            $response['addProductsToCart']['user_errors'][0]['message']
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddProductIfQuantityIsNotAvailable()
    {
        $product = $this->getConfigurableProductInfo();
        $parentSku = $product['sku'];
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $valueIndex = $product['configurable_options'][0]['values'][1]['value_index'];

        $selectedConfigurableOptionsQuery = $this->generateSuperAttributesUIDQuery($attributeId, $valueIndex);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            2000,
            $selectedConfigurableOptionsQuery
        );

        $response = $this->graphQlMutation($query);

        self::assertEquals(
            'The requested qty is not available',
            $response['addProductsToCart']['user_errors'][0]['message']
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddNonExistentConfigurableProductParentToCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSku = 'configurable_no_exist';

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            1,
            ''
        );

        $response = $this->graphQlMutation($query);

        self::assertEquals(
            'Could not find a product with SKU "configurable_no_exist"',
            $response['addProductsToCart']['user_errors'][0]['message']
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_zero_qty_first_child.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testOutOfStockVariationToCart()
    {
        $product = $this->getConfigurableProductInfo();
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $valueIndex = $product['configurable_options'][0]['values'][0]['value_index'];
        $parentSku = $product['sku'];

        $configurableOptionsQuery = $this->generateSuperAttributesUIDQuery($attributeId, $valueIndex);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            1,
            $configurableOptionsQuery
        );

        $response = $this->graphQlMutation($query);

        $expectedErrorMessages = [
            'There are no source items with the in stock status',
            'This product is out of stock.'
        ];
        $this->assertContains(
            $response['addProductsToCart']['user_errors'][0]['message'],
            $expectedErrorMessages
        );
    }

    /**
     * @param string $maskedQuoteId
     * @param string $parentSku
     * @param int $quantity
     * @param string $selectedOptionsQuery
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        string $parentSku,
        int $quantity,
        string $selectedOptionsQuery
    ): string {
        return <<<QUERY
mutation {
    addProductsToCart(
        cartId:"{$maskedQuoteId}"
        cartItems: [
            {
                sku: "{$parentSku}"
                quantity: $quantity
                {$selectedOptionsQuery}
            }
        ]
    ) {
        cart {
            items {
                id
                quantity
                product {
                    sku
                }
                ... on ConfigurableCartItem {
                    configurable_options {
                        id
                        option_label
                        value_id
                        value_label
                    }
                }
            }
        },
        user_errors {
            message
        }
    }
}
QUERY;
    }

    /**
     * Returns information about testable configurable product retrieved from GraphQl query
     *
     * @return array
     * @throws Exception
     */
    private function getConfigurableProductInfo(): array
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        return current($searchResponse['products']['items']);
    }

    /**
     * Returns GraphQl query for fetching configurable product information
     *
     * @param string $term
     * @return string
     */
    private function getFetchProductQuery(string $term): string
    {
        return <<<QUERY
{
  products(
    search:"{$term}"
    pageSize:1
  ) {
    items {
      sku
      ... on ConfigurableProduct {
        configurable_options {
          attribute_id
          attribute_code
          id
          label
          position
          product_id
          use_default
          values {
            default_label
            label
            store_label
            use_default_value
            value_index
          }
        }
      }
    }
  }
}
QUERY;
    }
}
