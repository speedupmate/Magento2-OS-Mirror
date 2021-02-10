<?php

namespace Dotdigitalgroup\Sms\Model\Message\Variable;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Resolver
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string[]
     */
    private $templateVariables = [
        'first_name',
        'last_name',
        'email',
        'store_name',
        'order_id',
        'order_status',
        'tracking_number',
        'tracking_carrier',
        'refund_amount'
    ];

    /**
     * Resolver constructor.
     * @param Logger $logger
     * @param SerializerInterface $serializer
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Logger $logger,
        SerializerInterface $serializer,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $variable
     * @param SmsOrderInterface $sms
     * @return string
     */
    public function resolve($variable, $sms)
    {
        if (!in_array($variable, $this->templateVariables)) {
            return '';
        }

        $method = $this->getMethodFromVariable($variable);
        return (string) $this->$method($sms);
    }

    /**
     * @param SmsOrderInterface $sms
     * @return string|null
     */
    private function getFirstName($sms)
    {
        $order = $this->orderRepository->get($sms->getOrderId());

        if ($order->getCustomerFirstname() === null) {
            return $order->getShippingAddress()->getFirstname();
        }

        return $order->getCustomerFirstname();
    }

    /**
     * @param SmsOrderInterface $sms
     * @return string|null
     */
    private function getLastName($sms)
    {
        $order = $this->orderRepository->get($sms->getOrderId());

        if ($order->getCustomerLastname() === null) {
            return $order->getShippingAddress()->getLastname();
        }

        return $order->getCustomerLastname();
    }

    /**
     * @param SmsOrderInterface $sms
     */
    private function getEmail($sms)
    {
        return $this->orderRepository->get($sms->getOrderId())
            ->getCustomerEmail();
    }

    /**
     * @param SmsOrderInterface $sms
     * @return string
     */
    private function getStoreName($sms)
    {
        $groupId = $this->storeManager->getStore($sms->getStoreId())->getStoreGroupId();
        return $this->storeManager->getGroup($groupId)->getName();
    }

    /**
     * @param SmsOrderInterface $sms
     * @return mixed
     */
    private function getOrderId($sms)
    {
        return $this->orderRepository->get($sms->getOrderId())
            ->getRealOrderId();
    }

    /**
     * @param SmsOrderInterface $sms
     * @return string
     */
    private function getOrderStatus($sms)
    {
        return $this->getAdditionalDataByKey($sms, 'orderStatus');
    }

    /**
     * @param SmsOrderInterface $sms
     * @return string
     */
    private function getTrackingNumber($sms)
    {
        return $this->getAdditionalDataByKey($sms, 'trackingNumber');
    }

    /**
     * @param SmsOrderInterface $sms
     * @return string
     */
    private function getTrackingCarrier($sms)
    {
        return $this->getAdditionalDataByKey($sms, 'trackingCarrier');
    }

    /**
     * @param SmsOrderInterface $sms
     * @return string
     */
    private function getRefundAmount($sms)
    {
        return $this->getAdditionalDataByKey($sms, 'creditMemoAmount');
    }

    /**
     * Transform a variable like 'first_name' into the method name 'getFirstName'.
     *
     * @param $variable
     * @return string
     */
    private function getMethodFromVariable($variable)
    {
        return 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $variable)));
    }

    /**
     * @param SmsOrderInterface $sms
     * @param string $key
     * @return string
     */
    private function getAdditionalDataByKey($sms, $key)
    {
        try {
            $additionalData = $this->serializer->unserialize(
                $sms->getAdditionalData()
            );
            return isset($additionalData[$key]) ?
                $additionalData[$key] :
                '';
        } catch (\InvalidArgumentException $e) {
            $this->logger->debug(
                'Could not unserialize ' . $key . ' for SMS id ' . $sms->getId(),
                [(string) $e]
            );
            return '';
        }
    }
}
