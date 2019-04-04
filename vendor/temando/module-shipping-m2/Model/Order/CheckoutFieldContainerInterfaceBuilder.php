<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order;

use Magento\Framework\Api\AbstractSimpleObjectBuilder;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressExtensionInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Sales\Api\Data\OrderAddressExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Temando\Shipping\Model\Checkout\Attribute\CheckoutFieldInterface;
use Temando\Shipping\Model\Checkout\Attribute\CheckoutFieldInterfaceFactory;
use Temando\Shipping\Model\Checkout\Schema\CheckoutFieldsSchema;
use Temando\Shipping\Model\Shipping\RateRequest\Extractor;

/**
 * Temando Order Checkout Field Container Builder
 *
 * @package  Temando\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class CheckoutFieldContainerInterfaceBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @var Extractor
     */
    private $rateRequestExtractor;

    /**
     * @var CheckoutFieldsSchema
     */
    private $schema;

    /**
     * @var CheckoutFieldInterfaceFactory
     */
    private $fieldFactory;

    /**
     * @param ObjectFactory $objectFactory
     * @param Extractor $rateRequestExtractor
     * @param CheckoutFieldsSchema $schema
     * @param CheckoutFieldInterfaceFactory $fieldFactory
     */
    public function __construct(
        ObjectFactory $objectFactory,
        Extractor $rateRequestExtractor,
        CheckoutFieldsSchema $schema,
        CheckoutFieldInterfaceFactory $fieldFactory
    ) {
        $this->rateRequestExtractor = $rateRequestExtractor;
        $this->schema = $schema;
        $this->fieldFactory = $fieldFactory;

        parent::__construct($objectFactory);
    }

    /**
     * @param string[][] $checkoutAttributes
     * @return CheckoutFieldInterface[]
     */
    private function getCheckoutFieldsFromArray(array $checkoutAttributes)
    {
        $availableFields = $this->schema->getFields();

        $checkoutFields = array_map(function (array $checkoutAttribute) use ($availableFields) {
            /** @var \Temando\Shipping\Model\Checkout\Schema\CheckoutField $fieldDefinition */
            $fieldDefinition = $availableFields[$checkoutAttribute['attribute_code']];

            $checkoutField = $this->fieldFactory->create(['data' => [
                CheckoutFieldInterface::FIELD_ID => $checkoutAttribute['attribute_code'],
                CheckoutFieldInterface::VALUE => $checkoutAttribute['value'],
                CheckoutFieldInterface::ORDER_PATH => $fieldDefinition->getOrderPath(),
            ]]);

            return $checkoutField;
        }, $checkoutAttributes);

        return $checkoutFields;
    }

    /**
     * @param \Magento\Framework\Api\AttributeInterface[] $checkoutAttributes
     * @return CheckoutFieldInterface[]
     */
    private function getCheckoutFieldsFromAttributes(array $checkoutAttributes)
    {
        $availableFields = $this->schema->getFields();

        $checkoutFields = array_map(function (AttributeInterface $checkoutAttribute) use ($availableFields) {
            /** @var \Temando\Shipping\Model\Checkout\Schema\CheckoutField $fieldDefinition */
            $fieldDefinition = $availableFields[$checkoutAttribute->getAttributeCode()];

            $checkoutField = $this->fieldFactory->create(['data' => [
                CheckoutFieldInterface::FIELD_ID => $checkoutAttribute->getAttributeCode(),
                CheckoutFieldInterface::VALUE => $checkoutAttribute->getValue(),
                CheckoutFieldInterface::ORDER_PATH => $fieldDefinition->getOrderPath(),
            ]]);

            return $checkoutField;
        }, $checkoutAttributes);

        return $checkoutFields;
    }

    /**
     * Set value as selected during checkout (rate request)
     *
     * For some reason the shipping method management turns the well defined
     * extension attribute into an untyped array. Dealing with it here.
     *
     * @see \Magento\Quote\Model\ShippingMethodManagement::getShippingMethods
     * @see \Magento\Quote\Model\ShippingMethodManagement::extractAddressData
     *
     * @param RateRequest $rateRequest
     * @return void
     * @throws LocalizedException
     */
    public function setRateRequest(RateRequest $rateRequest)
    {
        try {
            $shippingAddress = $this->rateRequestExtractor->getShippingAddress($rateRequest);
            $extensionAttributes = $shippingAddress->getExtensionAttributes();
            if ($extensionAttributes instanceof AddressExtensionInterface) {
                $checkoutFields = $this->getCheckoutFieldsFromAttributes($extensionAttributes->getCheckoutFields());
            } elseif (is_array($extensionAttributes) && isset($extensionAttributes['checkout_fields'])) {
                $checkoutFields = $this->getCheckoutFieldsFromArray($extensionAttributes['checkout_fields']);
            } else {
                $checkoutFields = [];
            }
        } catch (LocalizedException $e) {
            // detailed address data unavailable
            $checkoutFields = [];
        }

        $this->_set(CheckoutFieldContainerInterface::FIELDS, $checkoutFields);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order
     * @return void
     * @throws LocalizedException
     */
    public function setOrder(OrderInterface $order)
    {
        $shippingAddress = $order->getShippingAddress();

        $extensionAttributes = $shippingAddress->getExtensionAttributes();
        if ($extensionAttributes instanceof OrderAddressExtensionInterface) {
            $checkoutFields = $this->getCheckoutFieldsFromAttributes($extensionAttributes->getCheckoutFields());
        } elseif (is_array($extensionAttributes) && isset($extensionAttributes['checkout_fields'])) {
            $checkoutFields = $this->getCheckoutFieldsFromArray($extensionAttributes['checkout_fields']);
        } else {
            $checkoutFields = [];
        }

        $this->_set(CheckoutFieldContainerInterface::FIELDS, $checkoutFields);
    }
}
