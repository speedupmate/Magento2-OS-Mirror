<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\AbstractSimpleObjectBuilder;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Sales\Api\Data\OrderInterface;
use Temando\Shipping\Model\Checkout\RateRequest\Extractor;

/**
 * Temando Order Custom Attributes Builder
 *
 * @package Temando\Shipping\Model
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CustomAttributesInterfaceBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @var Extractor
     */
    private $rateRequestExtractor;

    /**
     * @var GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @param ObjectFactory $objectFactory
     * @param Extractor $rateRequestExtractor
     * @param GroupRepositoryInterface $customerGroupRepository
     */
    public function __construct(
        ObjectFactory $objectFactory,
        Extractor $rateRequestExtractor,
        GroupRepositoryInterface $customerGroupRepository
    ) {
        $this->rateRequestExtractor = $rateRequestExtractor;
        $this->customerGroupRepository = $customerGroupRepository;

        parent::__construct($objectFactory);
    }

    /**
     * @param RateRequest $rateRequest
     * @return void
     */
    public function setRateRequest(RateRequest $rateRequest)
    {
        try {
            $quote = $this->rateRequestExtractor->getQuote($rateRequest);

            $store = $quote->getStore();
            $customerGroup = $this->customerGroupRepository->getById($quote->getCustomerGroupId());

            $this->_set(CustomAttributesInterface::STORE_CODE, $store->getCode());
            $this->_set(CustomAttributesInterface::CUSTOMER_GROUP_CODE, $customerGroup->getCode());
        } catch (LocalizedException $e) {
            $this->_set(CustomAttributesInterface::STORE_CODE, null);
            $this->_set(CustomAttributesInterface::CUSTOMER_GROUP_CODE, null);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order
     * @return void
     */
    public function setOrder(OrderInterface $order)
    {
        try {
            $store = $order->getStore();

            $customerGroup = $this->customerGroupRepository->getById($order->getCustomerGroupId());

            $this->_set(CustomAttributesInterface::STORE_CODE, $store->getCode());
            $this->_set(CustomAttributesInterface::CUSTOMER_GROUP_CODE, $customerGroup->getCode());
        } catch (LocalizedException $e) {
            $this->_set(CustomAttributesInterface::STORE_CODE, null);
            $this->_set(CustomAttributesInterface::CUSTOMER_GROUP_CODE, null);
        }
    }
}
