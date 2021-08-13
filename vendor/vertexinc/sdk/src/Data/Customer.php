<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Data;

/**
 * {@inheritDoc}
 */
class Customer implements CustomerInterface
{
    /** @var AddressInterface */
    private $administrativeDestination;

    /** @var bool */
    private $business;

    /** @var string */
    private $code;

    /** @var AddressInterface */
    private $destination;

    /** @var string */
    private $taxClass;

    /** @var TaxRegistrationInterface */
    private $taxRegistrations = [];

    /**
     * @inheritdoc
     */
    public function getAdministrativeDestination()
    {
        return $this->administrativeDestination;
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @inheritdoc
     */
    public function getTaxClass()
    {
        return $this->taxClass;
    }

    /**
     * @inheritDoc
     */
    public function getTaxRegistrations()
    {
        return $this->taxRegistrations;
    }

    /**
     * @inheritdoc
     */
    public function isBusiness()
    {
        return $this->business;
    }

    /**
     * @inheritdoc
     */
    public function setAdministrativeDestination(AddressInterface $destination)
    {
        $this->administrativeDestination = $destination;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCode($customerCode)
    {
        $this->code = $customerCode;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDestination(AddressInterface $destination)
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setIsBusiness($isBusiness)
    {
        $this->business = $isBusiness;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTaxClass($taxClass)
    {
        $this->taxClass = $taxClass;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTaxRegistrations(array $registrations)
    {
        $this->taxRegistrations = $registrations;
        return $this;
    }
}
