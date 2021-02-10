<?php

namespace Dotdigitalgroup\Sms\Model\Queue\OrderItem;

use Dotdigitalgroup\Email\Logger\Logger;
use Magento\Framework\Serialize\SerializerInterface;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\Data\OrderData;
use Magento\Sales\Api\Data\OrderInterface;

abstract class AbstractOrderItem
{
    /**
     * @var string
     */
    protected $smsConfigPath;

    /**
     * @var int
     */
    protected $smsType;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var OrderData
     */
    protected $additionalData;

    /**
     * @var OrderItemNotificationEnqueuer
     */
    private $orderItemNotificationEnqueuer;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * AbstractOrderItem constructor.
     * @param OrderItemNotificationEnqueuer $orderItemNotificationEnqueuer
     * @param SerializerInterface $serializer
     * @param Logger $logger
     * @param OrderData $additionalData
     */
    public function __construct(
        OrderItemNotificationEnqueuer $orderItemNotificationEnqueuer,
        SerializerInterface $serializer,
        Logger $logger,
        OrderData $additionalData
    ) {
        $this->orderItemNotificationEnqueuer = $orderItemNotificationEnqueuer;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->additionalData = $additionalData;
    }

    /**
     * @param $order
     * @param $additionalData
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function queue()
    {
        $this->orderItemNotificationEnqueuer
            ->queue(
                $this->order,
                $this->serialiseData(),
                $this->smsConfigPath,
                $this->smsType
            );
    }

    /**
     * @return string
     */
    private function serialiseData()
    {
        try {
            return $this->serializer->serialize($this->additionalData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->debug((string) $e);
            return '';
        }
    }
}
