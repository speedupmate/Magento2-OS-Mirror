<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Model\Api;

use Exception;
use Klarna\Core\Api\BuilderInterface;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Helper\KlarnaConfig;
use Klarna\Core\Model\Checkout\Orderline\AbstractLine;
use Klarna\Core\Model\Checkout\Orderline\Collector;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\Data\Address as CustomerAddress;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Url;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\AbstractModel;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Base class to generate API configuration
 *
 * @method Builder setShippingUnitPrice($integer)
 * @method int getShippingUnitPrice()
 * @method Builder setShippingTaxRate($integer)
 * @method int getShippingTaxRate()
 * @method Builder setShippingTotalAmount($integer)
 * @method int getShippingTotalAmount()
 * @method Builder setShippingTaxAmount($integer)
 * @method int getShippingTaxAmount()
 * @method Builder setShippingTitle($string)
 * @method string getShippingTitle()
 * @method Builder setShippingReference($integer)
 * @method int getShippingReference()
 * @method Builder setDiscountUnitPrice($integer)
 * @method int getDiscountUnitPrice()
 * @method Builder setDiscountTaxRate($integer)
 * @method int getDiscountTaxRate()
 * @method Builder setDiscountTotalAmount($integer)
 * @method int getDiscountTotalAmount()
 * @method Builder setDiscountTaxAmount($integer)
 * @method int getDiscountTaxAmount()
 * @method Builder setDiscountTitle($integer)
 * @method int getDiscountTitle()
 * @method Builder setDiscountReference($integer)
 * @method int getDiscountReference()
 * @method Builder setTaxUnitPrice($integer)
 * @method int getTaxUnitPrice()
 * @method Builder setTaxTotalAmount($integer)
 * @method int getTaxTotalAmount()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Builder extends DataObject implements BuilderInterface
{

    /**
     * @var string
     */
    public $prefix = '';
    /**
     * @var Collector
     */
    protected $orderLineCollector = null;
    /**
     * @var EventManager
     */
    protected $eventManager;
    /**
     * @var array
     */
    protected $orderLines = [];
    /**
     * @var AbstractModel|Quote
     */
    protected $object = null;
    /**
     * @var array
     */
    protected $request = [];
    /**
     * @var bool
     */
    protected $inRequestSet = false;
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    /**
     * @var Url
     */
    protected $url;
    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;
    /**
     * @var DateTime
     */
    protected $coreDate;
    /**
     * @var KlarnaConfig
     */
    protected $klarnaConfig;
    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;
    /**
     * @var Copy
     */
    private $objCopyService;
    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * Init
     *
     * @param EventManager      $eventManager
     * @param Collector         $collector
     * @param Url               $url
     * @param ConfigHelper      $configHelper
     * @param DirectoryHelper   $directoryHelper
     * @param DateTime          $coreDate
     * @param Copy              $objCopyService
     * @param AddressRegistry   $addressRegistry
     * @param KlarnaConfig      $klarnaConfig
     * @param DataObjectFactory $dataObjectFactory
     * @param array             $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EventManager $eventManager,
        Collector $collector,
        Url $url,
        ConfigHelper $configHelper,
        DirectoryHelper $directoryHelper,
        DateTime $coreDate,
        Copy $objCopyService,
        AddressRegistry $addressRegistry,
        KlarnaConfig $klarnaConfig,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->eventManager       = $eventManager;
        $this->orderLineCollector = $collector;
        $this->url                = $url;
        $this->configHelper       = $configHelper;
        $this->directoryHelper    = $directoryHelper;
        $this->coreDate           = $coreDate;
        $this->objCopyService     = $objCopyService;
        $this->addressRegistry    = $addressRegistry;
        $this->klarnaConfig       = $klarnaConfig;
        $this->dataObjectFactory  = $dataObjectFactory;
    }

    /**
     * Generate order body
     *
     * @param string $type
     * @return BuilderInterface
     * @throws LocalizedException
     */
    public function generateRequest($type = self::GENERATE_TYPE_CREATE)
    {
        $this->collectOrderLines($this->getObject()->getStore());
        return $this;
    }

    /**
     * Collect order lines
     *
     * @param StoreInterface $store
     * @return BuilderInterface
     * @throws LocalizedException
     */
    public function collectOrderLines(StoreInterface $store): self
    {
        /** @var AbstractLine $model */
        foreach ($this->getOrderLinesCollector()->getCollectors($store) as $model) {
            $model->collect($this);
        }

        return $this;
    }

    /**
     * Get totals collector model
     *
     * @return Collector
     */
    public function getOrderLinesCollector(): Collector
    {
        return $this->orderLineCollector;
    }

    /**
     * Get the object used to generate request
     *
     * @return AbstractModel|Quote
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set the object used to generate request
     *
     * @param AbstractModel|Quote $object
     * @return BuilderInterface
     */
    public function setObject($object): self
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get request
     *
     * @return array
     */
    abstract public function getRequest();

    /**
     * Set generated request
     *
     * @param array  $request
     * @param string $type
     * @return BuilderInterface
     */
    public function setRequest(array $request, $type = self::GENERATE_TYPE_CREATE): self
    {
        $this->request = $this->cleanNulls($request);

        if (!$this->inRequestSet) {
            $this->inRequestSet = true;
            $this->eventManager->dispatch(
                $this->prefix . "_builder_set_request_{$type}",
                [
                    'builder' => $this
                ]
            );

            $this->eventManager->dispatch(
                $this->prefix . '_builder_set_request',
                [
                    'builder' => $this
                ]
            );
            $this->inRequestSet = false;
        }

        return $this;
    }

    /**
     * Remove items that are not allowed to be null
     *
     * @param array $request
     * @return array
     */
    protected function cleanNulls(array $request): array
    {
        $disallowNulls = [
            'customer',
            'billing_address',
            'shipping_address',
            'external_payment_methods'
        ];
        foreach ($disallowNulls as $key) {
            if (empty($request[$key])) {
                unset($request[$key]);
            }
        }
        return $request;
    }

    /**
     * Get order lines as array
     *
     * @param StoreInterface $store
     * @param bool           $orderItemsOnly
     * @return array
     * @throws LocalizedException
     */
    public function getOrderLines(StoreInterface $store, $orderItemsOnly = false): array
    {
        /** @var AbstractLine $model */
        foreach ($this->getOrderLinesCollector()->getCollectors($store) as $model) {
            if ($model->isIsTotalCollector() && $orderItemsOnly) {
                continue;
            }

            $model->fetch($this);
        }

        return $this->orderLines;
    }

    /**
     * Add an order line
     *
     * @param array $orderLine
     * @return BuilderInterface
     */
    public function addOrderLine(array $orderLine): self
    {
        $this->orderLines[] = $orderLine;

        return $this;
    }

    /**
     * Remove all order lines
     *
     * @return BuilderInterface
     */
    public function resetOrderLines(): self
    {
        $this->orderLines = [];

        return $this;
    }

    /**
     * Get merchant references
     *
     * @param CartInterface $quote
     * @return DataObject
     */
    public function getMerchantReferences(CartInterface $quote): DataObject
    {
        $merchantReferences = $this->dataObjectFactory->create([
            'data' => [
                'merchant_reference_1' => $quote->getReservedOrderId(),
                'merchant_reference_2' => ''
            ]
        ]);

        $this->eventManager->dispatch(
            $this->prefix . '_merchant_reference_update',
            [
                'quote'                     => $quote,
                'merchant_reference_object' => $merchantReferences
            ]
        );
        return $merchantReferences;
    }

    /**
     * Get Terms URL
     *
     * @param StoreInterface $store
     * @param string         $configPath
     * @return mixed|string
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

    public function getTermsUrl(StoreInterface $store, string $configPath = 'terms_url')
    {
        return '';
    }

    /**
     * Populate prefill values
     *
     * @param array          $create
     * @param CartInterface  $quote
     * @param StoreInterface $store
     * @return mixed
     */
    public function prefill(array $create, CartInterface $quote, StoreInterface $store)
    {
        /**
         * Customer
         */
        $create['customer'] = $this->getCustomerData($quote);

        /**
         * Billing Address
         */
        $create['billing_address'] = $this->getAddressData($quote, Address::TYPE_BILLING);

        /**
         * Shipping Address
         */
        if (isset($create['billing_address'])
            && $this->configHelper->isCheckoutConfigFlag('separate_address', $store)
        ) {
            $create['shipping_address'] = $this->getAddressData($quote, Address::TYPE_SHIPPING);
        }
        return $create;
    }

    /**
     * Get customer details
     *
     * @param CartInterface $quote
     * @return array
     */
    public function getCustomerData(CartInterface $quote): ?array
    {
        if (!$quote->getCustomerIsGuest() && $quote->getCustomerDob()) {
            return [
                'date_of_birth' => $this->coreDate->date('Y-m-d', $quote->getCustomerDob())
            ];
        }

        return null;
    }

    /**
     * Auto fill user address details
     *
     * @param CartInterface $quote
     * @param string        $type
     *
     * @return array
     */
    protected function getAddressData(CartInterface $quote, $type = null): array
    {
        $result = [];
        if ($quote->getCustomerEmail()) {
            $result['email'] = $quote->getCustomerEmail();
        }

        $address = $quote->getShippingAddress();
        if ($type === Address::TYPE_BILLING || $quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        }

        return $this->processAddress($result, $quote, $address);
    }

    /**
     * @param array         $result
     * @param CartInterface $quote
     * @param Address       $address
     * @return array
     */
    private function processAddress(array $result, CartInterface $quote, Address $address = null): array
    {
        $resultObject = $this->dataObjectFactory->create(['data' => $result]);
        if ($address) {
            $address->explodeStreetAddress();
            $this->objCopyService->copyFieldsetToTarget(
                'sales_convert_quote_address',
                'to_klarna',
                $address,
                $resultObject
            );

            /*
             * Making sure the billing address' organization name is empty
             * during requests to prevent error when B2B is disabled
             */
            if ($this->shouldClearOrganizationName($quote, $address)) {
                $resultObject->setOrganizationName('');
            }
            if ($address->getCountryId() === 'US') {
                $resultObject->setRegion($address->getRegionCode());
            }
        }

        $street_address = $this->prepareStreetAddressArray($resultObject);
        $resultObject->setStreetAddress($street_address[0]);
        $resultObject->setData('street_address2', $street_address[1]);

        if (isset($result['email'])) {
            $resultObject->setEmail($result['email']);
        }

        return array_filter($resultObject->toArray());
    }

    /**
     * @param DataObject $resultObject
     * @return array
     */
    private function prepareStreetAddressArray(DataObject $resultObject): array
    {
        $street_address = $resultObject->getStreetAddress();
        if (!is_array($street_address)) {
            $street_address = [$street_address];
        }
        if (count($street_address) === 1) {
            $street_address[] = '';
        }
        return $street_address;
    }

    /**
     * Verifies if we should clear the organization name from the address object
     *
     * @param CartInterface $quote
     * @param Address       $address
     * @return bool
     */
    private function shouldClearOrganizationName(CartInterface $quote, Address $address): bool
    {
        $store = $quote->getStore();
        $b2bEnabled = $this->configHelper->isPaymentConfigFlag('enable_b2b', $store);
        $isBillingAddress = $address->getAddressType() === Address::TYPE_BILLING;

        return !$b2bEnabled && ($isBillingAddress || !$quote->getIsVirtual());
    }

    /**
     * @param array $items
     * @return BuilderInterface
     */
    public function setItems($items): self
    {
        $this->setData('items', $items);
        return $this;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->getData('items');
    }
}
