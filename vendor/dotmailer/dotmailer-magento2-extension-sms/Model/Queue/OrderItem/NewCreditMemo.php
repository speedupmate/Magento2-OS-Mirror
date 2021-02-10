<?php

namespace Dotdigitalgroup\Sms\Model\Queue\OrderItem;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\Data\CreditMemoData;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;

class NewCreditMemo extends AbstractOrderItem
{
    /**
     * @var string
     */
    protected $smsType = ConfigInterface::SMS_TYPE_NEW_CREDIT_MEMO;

    /**
     * @var int
     */
    protected $smsConfigPath  = ConfigInterface::XML_PATH_SMS_NEW_CREDIT_MEMO_ENABLED;

    /**
     * @var PriceCurrencyInterface
     */
    private $currencyInterface;

    /**
     * NewCreditMemo constructor.
     * @param OrderItemNotificationEnqueuer $orderItemNotificationEnqueuer
     * @param SerializerInterface $serializer
     * @param Logger $logger
     * @param PriceCurrencyInterface $currencyInterface
     * @param CreditMemoData $additionalData
     */
    public function __construct(
        OrderItemNotificationEnqueuer $orderItemNotificationEnqueuer,
        SerializerInterface $serializer,
        Logger $logger,
        PriceCurrencyInterface $currencyInterface,
        CreditMemoData $additionalData
    ) {
        $this->currencyInterface = $currencyInterface;
        parent::__construct($orderItemNotificationEnqueuer, $serializer, $logger, $additionalData);
    }

    /**
     * @param $order
     * @param $creditMemo
     */
    public function buildAdditionalData($order, $creditMemo)
    {
        $this->order = $order;
        $this->additionalData->orderStatus = $order->getStatus();

        $this->additionalData->creditMemoAmount = $this->currencyInterface->format(
            $creditMemo->getGrandTotal(),
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $creditMemo->getStoreId(),
            $creditMemo->getOrderCurrencyCode()
        );

        return $this;
    }
}
