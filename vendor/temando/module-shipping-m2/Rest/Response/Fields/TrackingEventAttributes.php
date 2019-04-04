<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Fields;

/**
 * Temando API Tracking Event Resource Object Attributes
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class TrackingEventAttributes
{
    /**
     * @var string
     */
    private $integrationId;

    /**
     * @var string
     */
    private $trackingReference;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $rawEvent;

    /**
     * @var string
     */
    private $rawStatus;

    /**
     * @var string
     */
    private $occurredAt;

    /**
     * @var string
     */
    private $createdAt;

    /**
     * @return string
     */
    public function getIntegrationId()
    {
        return $this->integrationId;
    }

    /**
     * @param string $integrationId
     * @return void
     */
    public function setIntegrationId($integrationId)
    {
        $this->integrationId = $integrationId;
    }

    /**
     * @return string
     */
    public function getTrackingReference()
    {
        return $this->trackingReference;
    }

    /**
     * @param string $trackingReference
     * @return void
     */
    public function setTrackingReference($trackingReference)
    {
        $this->trackingReference = $trackingReference;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getRawEvent()
    {
        return $this->rawEvent;
    }

    /**
     * @param string $rawEvent
     * @return void
     */
    public function setRawEvent($rawEvent)
    {
        $this->rawEvent = $rawEvent;
    }

    /**
     * @return string
     */
    public function getRawStatus()
    {
        return $this->rawStatus;
    }

    /**
     * @param string $rawStatus
     * @return void
     */
    public function setRawStatus($rawStatus)
    {
        $this->rawStatus = $rawStatus;
    }

    /**
     * @return string
     */
    public function getOccurredAt()
    {
        return $this->occurredAt;
    }

    /**
     * @param string $occurredAt
     * @return void
     */
    public function setOccurredAt($occurredAt)
    {
        $this->occurredAt = $occurredAt;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
