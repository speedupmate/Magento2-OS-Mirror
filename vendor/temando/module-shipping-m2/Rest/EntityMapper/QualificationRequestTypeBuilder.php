<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Temando\Shipping\Model\Checkout\Attribute\CheckoutFieldInterface;
use Temando\Shipping\Model\Order\CustomAttributesInterface;
use Temando\Shipping\Model\Order\OrderBillingInterface;
use Temando\Shipping\Model\Order\OrderItemInterface;
use Temando\Shipping\Model\Order\OrderRecipientInterface;
use Temando\Shipping\Model\OrderInterface;
use Temando\Shipping\Rest\Request\Type\ExtensibleTypeAttribute;
use Temando\Shipping\Rest\Request\Type\ExtensibleTypeAttributeFactory;
use Temando\Shipping\Rest\Request\Type\Generic\AddressFactory;
use Temando\Shipping\Rest\Request\Type\Generic\DimensionsFactory;
use Temando\Shipping\Rest\Request\Type\Generic\MonetaryValueFactory;
use Temando\Shipping\Rest\Request\Type\Generic\WeightFactory;
use Temando\Shipping\Rest\Request\Type\Order\CustomAttributes;
use Temando\Shipping\Rest\Request\Type\Order\CustomAttributesFactory;
use Temando\Shipping\Rest\Request\Type\Order\Customer;
use Temando\Shipping\Rest\Request\Type\Order\CustomerFactory;
use Temando\Shipping\Rest\Request\Type\Order\OrderItem;
use Temando\Shipping\Rest\Request\Type\Order\OrderItem\ClassificationCodesFactory;
use Temando\Shipping\Rest\Request\Type\Order\OrderItem\ManufactureFactory;
use Temando\Shipping\Rest\Request\Type\Order\OrderItem\OriginFactory;
use Temando\Shipping\Rest\Request\Type\Order\OrderItemFactory;
use Temando\Shipping\Rest\Request\Type\Order\Recipient;
use Temando\Shipping\Rest\Request\Type\Order\RecipientFactory;
use Temando\Shipping\Rest\Request\Type\Qualification\GeoAddress;
use Temando\Shipping\Rest\Request\Type\Qualification\GeoAddressFactory;
use Temando\Shipping\Rest\Request\Type\Qualification\Order;
use Temando\Shipping\Rest\Request\Type\Qualification\OrderFactory;
use Temando\Shipping\Rest\Request\Type\QualificationRequestType;
use Temando\Shipping\Rest\Request\Type\QualificationRequestTypeFactory;

/**
 * Prepare the request type for order qualification at the Temando platform.
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class QualificationRequestTypeBuilder
{
    /**
     * @var QualificationRequestTypeFactory
     */
    private $requestTypeFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var RecipientFactory
     */
    private $recipientFactory;

    /**
     * @var OrderItemFactory
     */
    private $orderItemFactory;

    /**
     * @var OriginFactory
     */
    private $originFactory;

    /**
     * @var ManufactureFactory
     */
    private $manufactureFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var ClassificationCodesFactory
     */
    private $classificationCodesFactory;

    /**
     * @var DimensionsFactory
     */
    private $dimensionsFactory;

    /**
     * @var MonetaryValueFactory
     */
    private $monetaryValueFactory;

    /**
     * @var WeightFactory
     */
    private $weightFactory;

    /**
     * @var GeoAddressFactory
     */
    private $geoAddressFactory;

    /**
     * @var ExtensibleTypeAttributeFactory
     */
    private $attributeFactory;

    /**
     * @var CustomAttributesFactory
     */
    private $customAttributesFactory;

    /**
     * OrderRequestTypeBuilder constructor.
     * @param QualificationRequestTypeFactory $requestTypeFactory
     * @param OrderFactory $orderFactory
     * @param CustomerFactory $customerFactory
     * @param RecipientFactory $recipientFactory
     * @param OrderItemFactory $orderItemFactory
     * @param OriginFactory $originFactory
     * @param ManufactureFactory $manufactureFactory
     * @param AddressFactory $addressFactory
     * @param ClassificationCodesFactory $classificationCodesFactory
     * @param DimensionsFactory $dimensionsFactory
     * @param MonetaryValueFactory $monetaryValueFactory
     * @param WeightFactory $weightFactory
     * @param GeoAddressFactory $geoAddressFactory
     * @param ExtensibleTypeAttributeFactory $attributeFactory
     * @param CustomAttributesFactory $customAttributesFactory
     */
    public function __construct(
        QualificationRequestTypeFactory $requestTypeFactory,
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory,
        RecipientFactory $recipientFactory,
        OrderItemFactory $orderItemFactory,
        OriginFactory $originFactory,
        ManufactureFactory $manufactureFactory,
        AddressFactory $addressFactory,
        ClassificationCodesFactory $classificationCodesFactory,
        DimensionsFactory $dimensionsFactory,
        MonetaryValueFactory $monetaryValueFactory,
        WeightFactory $weightFactory,
        GeoAddressFactory $geoAddressFactory,
        ExtensibleTypeAttributeFactory $attributeFactory,
        CustomAttributesFactory $customAttributesFactory
    ) {
        $this->requestTypeFactory = $requestTypeFactory;
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
        $this->recipientFactory = $recipientFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->originFactory = $originFactory;
        $this->manufactureFactory =  $manufactureFactory;
        $this->addressFactory = $addressFactory;
        $this->classificationCodesFactory = $classificationCodesFactory;
        $this->dimensionsFactory = $dimensionsFactory;
        $this->monetaryValueFactory = $monetaryValueFactory;
        $this->weightFactory = $weightFactory;
        $this->geoAddressFactory = $geoAddressFactory;
        $this->attributeFactory = $attributeFactory;
        $this->customAttributesFactory = $customAttributesFactory;
    }

    /**
     * Prepare additional request attributes as derived from checkout fields
     * definition and the values added in checkout.
     *
     * @param CheckoutFieldInterface[] $checkoutFields
     * @return ExtensibleTypeAttribute[]
     */
    private function getAdditionalAttributes(array $checkoutFields)
    {
        $additionalAttributes = [];
        foreach ($checkoutFields as $checkoutField) {
            // convert json query path into a stack of hierarchy levels
            $path = explode('/', $checkoutField->getOrderPath());
            array_shift($path); // remove leading hash symbol
            array_shift($path); // remove root "data" from path
            $additionalAttribute = $this->attributeFactory->create([
                'attributeId' => $checkoutField->getFieldId(),
                'value'       => $checkoutField->getValue(),
                'dataPath'    => $path,
            ]);

            $additionalAttributes[$additionalAttribute->getAttributeId()] = $additionalAttribute;
        }

        return $additionalAttributes;
    }

    /**
     * Build customer request type from order billing address entity.
     *
     * @param OrderBillingInterface $billingAddress
     * @return Customer
     */
    private function getCustomerType(OrderBillingInterface $billingAddress)
    {
        $customerType = $this->customerFactory->create([
            'organisationName' => $billingAddress->getCompany(),
            'lastname' => $billingAddress->getLastname(),
            'firstname' => $billingAddress->getFirstname(),
            'email' => $billingAddress->getEmail(),
            'phoneNumber' => $billingAddress->getPhone(),
            'faxNumber' => $billingAddress->getFax(),
            'nationalId' => $billingAddress->getNationalId(),
            'taxId' => $billingAddress->getTaxId(),
            'street' => $billingAddress->getStreet(),
            'countryCode' => $billingAddress->getCountryCode(),
            'administrativeArea' => $billingAddress->getRegion(),
            'postalCode' => $billingAddress->getPostalCode(),
            'locality' => $billingAddress->getCity(),
        ]);

        return $customerType;
    }

    /**
     * Build recipient request type from order shipping address entity.
     *
     * @param OrderRecipientInterface $recipient
     * @return Recipient
     */
    private function getRecipientType(OrderRecipientInterface $recipient)
    {
        $recipientType = $this->recipientFactory->create([
            'organisationName' => $recipient->getCompany(),
            'lastname' => $recipient->getLastname(),
            'firstname' => $recipient->getFirstname(),
            'email' => $recipient->getEmail(),
            'phoneNumber' => $recipient->getPhone(),
            'faxNumber' => $recipient->getFax(),
            'nationalId' => $recipient->getNationalId(),
            'taxId' => $recipient->getTaxId(),
            'street' => (array) $recipient->getStreet(),
            'countryCode' => $recipient->getCountryCode(),
            'administrativeArea' => $recipient->getRegion(),
            'postalCode' => $recipient->getPostalCode(),
            'locality' => $recipient->getCity(),
        ]);

        return $recipientType;
    }

    /**
     * @param CustomAttributesInterface $customAttributes
     * @return CustomAttributes
     */
    private function getCustomAttributesType(CustomAttributesInterface $customAttributes)
    {
        $customAttributesType = $this->customAttributesFactory->create([
            'storeCode' => $customAttributes->getStoreCode(),
            'customerGroupCode' => $customAttributes->getCustomerGroupCode()
        ]);

        return $customAttributesType;
    }

    /**
     * @param OrderItemInterface[] $orderItems
     * @return OrderItem[]
     */
    private function getItemTypes(array $orderItems)
    {
        $itemTypes = [];

        foreach ($orderItems as $orderItem) {
            $itemType = $this->orderItemFactory->create([
                'productId' => $orderItem->getProductId(),
                'qty' => $orderItem->getQty(),
                'sku' => $orderItem->getSku(),
                'name' => $orderItem->getName(),
                'description' => $orderItem->getDescription(),
                'categories' => $orderItem->getCategories(),
                'weight' => $this->weightFactory->create([
                    'value' => $orderItem->getWeight(),
                    'unitOfMeasurement' => $orderItem->getWeightUom(),
                ]),
                'unitOfMeasure' => '',
                'dimensions' => $this->dimensionsFactory->create([
                    'length' => $orderItem->getLength(),
                    'width' => $orderItem->getWidth(),
                    'height' => $orderItem->getHeight(),
                    'unit' => $orderItem->getDimensionsUom(),
                ]),
                'monetaryValue' => $this->monetaryValueFactory->create([
                    'amount' => $orderItem->getAmount(),
                    'currency' => $orderItem->getCurrency(),
                ]),
                'isFragile' => $orderItem->isFragile(),
                'isVirtual' => $orderItem->isVirtual(),
                'isPrePackaged' => $orderItem->isPrePackaged(),
                'packageId' => $orderItem->getPackageId(),
                'canRotateVertical' => $orderItem->canRotateVertically(),
                'origin' => $this->originFactory->create([
                    'address' => $this->addressFactory->create([
                        'countryCode' => $orderItem->getCountryOfOrigin(),
                    ]),
                ]),
                'manufacture' => $this->manufactureFactory->create([
                    'address' => $this->addressFactory->create([
                        'countryCode' => $orderItem->getCountryOfManufacture(),
                    ]),
                ]),
                'classificationCodes' => $this->classificationCodesFactory->create([
                    'eccn' => $orderItem->getEccn(),
                    'scheduleBinfo' => $orderItem->getScheduleBinfo(),
                    'hsCode' => $orderItem->getHsCode(),
                ]),
                'composition' => $orderItem->getComposition(),
                'customAttributes' => $orderItem->getCustomAttributes(),
            ]);
            $itemTypes[] = $itemType;
        }

        return $itemTypes;
    }

    /**
     * @param OrderInterface $order
     * @return Order
     */
    private function getOrderType(OrderInterface $order)
    {
        $orderType = $this->orderFactory->create([
            'createdAt' => date_create($order->getCreatedAt())->format('c'),
            'lastModifiedAt' => date_create($order->getLastModifiedAt())->format('c'),
            'sourceName' => 'Magento',
            'sourceReference' => $order->getSourceReference(),
            'total' => $this->monetaryValueFactory->create([
                'amount' => $order->getAmount(),
                'currency' => $order->getCurrency(),
            ]),
            'customer' => $this->getCustomerType($order->getBilling()),
            'recipient' => $this->getRecipientType($order->getRecipient()),
            'items' => $this->getItemTypes($order->getOrderItems()),
            'customAttributes' => $this->getCustomAttributesType($order->getCustomAttributes()),
        ]);

        return $orderType;
    }

    /**
     * @param OrderInterface $order
     * @return GeoAddress|null
     */
    private function getGeoAddressType(OrderInterface $order)
    {
        if (!$order->getCollectionPointSearchRequest()) {
            return null;
        }

        $geoAddressType = $this->geoAddressFactory->create([
            'postalCode' => $order->getCollectionPointSearchRequest()->getPostcode(),
            'countryCode' => $order->getCollectionPointSearchRequest()->getCountryId(),
        ]);

        return $geoAddressType;
    }

    /**
     * Create order request type from order entity.
     *
     * @param OrderInterface $order
     * @return QualificationRequestType
     */
    public function build(OrderInterface $order)
    {
        $orderType = $this->getOrderType($order);
        $geoAddressType = $this->getGeoAddressType($order);

        // internal types (as is)
        $checkoutFields = $order->getCheckoutFields();
        // request types (prepared for API usage)
        $additionalAttributes = $this->getAdditionalAttributes($checkoutFields);
        foreach ($additionalAttributes as $additionalAttribute) {
            $orderType->addAdditionalAttribute($additionalAttribute);
        }

        $requestType = $this->requestTypeFactory->create([
            'order' => $orderType,
            'geoAddress' => $geoAddressType,
        ]);

        return $requestType;
    }
}
