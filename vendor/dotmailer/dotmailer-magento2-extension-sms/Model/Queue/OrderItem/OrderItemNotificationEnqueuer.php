<?php

namespace Dotdigitalgroup\Sms\Model\Queue\OrderItem;

use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterfaceFactory;
use Dotdigitalgroup\Sms\Api\SmsOrderRepositoryInterface;
use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;
use Dotdigitalgroup\Sms\Model\Config\TransactionalSms;
use Magento\Store\Model\StoreManagerInterface;

class OrderItemNotificationEnqueuer
{
    const SMS_ENABLED = ConfigInterface::XML_PATH_TRANSACTIONAL_SMS_ENABLED;

    /**
     * @var SmsOrderInterfaceFactory
     */
    private $smsOrderInterfaceFactory;

    /**
     * @var SmsOrderRepositoryInterface
     */
    private $smsOrderRepositoryInterface;

    /**
     * @var TransactionalSms
     */
    private $transactionalSms;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * AbstractQueueManager constructor.
     * @param SmsOrderInterfaceFactory $smsOrderInterfaceFactory
     * @param SmsOrderRepositoryInterface $smsOrderRepositoryInterface
     * @param TransactionalSms $transactionalSms
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SmsOrderInterfaceFactory $smsOrderInterfaceFactory,
        SmsOrderRepositoryInterface $smsOrderRepositoryInterface,
        TransactionalSms $transactionalSms,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->smsOrderInterfaceFactory = $smsOrderInterfaceFactory;
        $this->smsOrderRepositoryInterface = $smsOrderRepositoryInterface;
        $this->transactionalSms = $transactionalSms;
    }

    /**
     * @param $order
     * @param $additionalData
     * @param $smsConfigPath
     * @param $smsType
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function queue($order, $additionalData, $smsConfigPath, $smsType)
    {
        $storeId = $this->storeManager->getStore()->getId();

        if (!$this->transactionalSms->isSmsEnabled($storeId) ||
            !$this->transactionalSms->isSmsTypeEnabled($storeId, $smsConfigPath)) {
            return;
        }

        $orderId = (int) $order->getId();

        $smsOrder = $this->smsOrderInterfaceFactory
            ->create()
            ->setOrderId($orderId)
            ->setStoreId($storeId)
            ->setWebsiteId($this->storeManager->getWebsite()->getId())
            ->setTypeId($smsType)
            ->setStatus(0)
            ->setPhoneNumber($order->getShippingAddress()->getTelephone())
            ->setEmail($order->getCustomerEmail())
            ->setAdditionalData($additionalData);

        $this->smsOrderRepositoryInterface
            ->save($smsOrder);
    }
}
