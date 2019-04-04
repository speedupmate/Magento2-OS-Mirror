<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Services\Quote;

use Vertex\Data\CustomerInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Data\SellerInterface;

/**
 * {@inheritDoc}
 */
class Request implements RequestInterface
{
    /** @var CustomerInterface */
    private $customer;

    /** @var string */
    private $deliveryTerm;

    /** @var \DateTimeInterface */
    private $documentDate;

    /** @var string */
    private $documentNumber;

    /** @var LineItemInterface[] */
    private $lineItems = [];

    /** @var string */
    private $locationCode;

    /** @var bool */
    private $returnAssistedParameters;

    /** @var SellerInterface */
    private $seller;

    /** @var string */
    private $transactionId;

    /** @var string */
    private $transactionType;

    /** @var string */
    private $currencyCode;

    /**
     * @inheritdoc
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryTerm()
    {
        return $this->deliveryTerm;
    }

    /**
     * @inheritdoc
     */
    public function getDocumentDate()
    {
        return $this->documentDate;
    }

    /**
     * @inheritdoc
     */
    public function getDocumentNumber()
    {
        return $this->documentNumber;
    }

    /**
     * @inheritdoc
     */
    public function getLineItems()
    {
        return $this->lineItems;
    }

    /**
     * @inheritdoc
     */
    public function getLocationCode()
    {
        return $this->locationCode;
    }

    /**
     * @inheritdoc
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * @inheritdoc
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @inheritdoc
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Retrieve whether or not the response is set to return assisted parameters
     *
     * @return bool|null
     */
    public function isSetToReturnAssistedParameters()
    {
        return $this->returnAssistedParameters;
    }

    /**
     * @inheritdoc
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @inheritdoc
     */
    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryTerm($deliveryTerm)
    {
        $this->deliveryTerm = $deliveryTerm;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDocumentDate($documentDate)
    {
        $this->documentDate = $documentDate;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDocumentNumber($documentNumber)
    {
        $this->documentNumber = $documentNumber;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLineItems(array $lineItems)
    {
        foreach ($lineItems as $lineItem) {
            if (!($lineItem instanceof LineItemInterface)) {
                throw new \InvalidArgumentException('Must be an array of LineItemInterface');
            }
        }
        $this->lineItems = $lineItems;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLocationCode($locationCode)
    {
        $this->locationCode = $locationCode;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSeller(SellerInterface $seller)
    {
        $this->seller = $seller;
        return $this;
    }

    /**
     * Set whether or not the response should return assisted parameters
     *
     * @param bool $returnAssistedParameters
     * @return RequestInterface
     */
    public function setShouldReturnAssistedParameters($returnAssistedParameters)
    {
        $this->returnAssistedParameters = (bool)$returnAssistedParameters;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }
}
