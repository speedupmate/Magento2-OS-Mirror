<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api70;

use Vertex\Data\CustomerInterface;
use Vertex\Mapper\Api60\CustomerMapper as CustomerMapper60;
use Vertex\Mapper\CustomerMapperInterface;

/**
 * API Level 70 implementation of {@see CustomerMapperInterface}
 */
class CustomerMapper implements CustomerMapperInterface
{
    /** @var CustomerMapper60 */
    private $parentMapper;

    /**
     * @param CustomerMapper60|null $parentMapper
     */
    public function __construct(CustomerMapper60 $parentMapper = null)
    {
        $this->parentMapper = $parentMapper ?: new CustomerMapper60(null, null, new TaxRegistrationMapper());
    }

    /**
     * @inheritdoc
     */
    public function build(\stdClass $map)
    {
        return $this->parentMapper->build($map);
    }

    /**
     * @inheritdoc
     */
    public function map(CustomerInterface $object)
    {
        return $this->parentMapper->map($object);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerTaxClassNameMaxLength()
    {
        return $this->parentMapper->getCustomerTaxClassNameMaxLength();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerTaxClassNameMinLength()
    {
        return $this->parentMapper->getCustomerTaxClassNameMinLength();
    }

    /**
     * @inheritDoc
     */
    public function validateCustomerTaxClassName($fieldName)
    {
        return $this->parentMapper->validateCustomerTaxClassName($fieldName);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerCodeMaxLength()
    {
        return $this->parentMapper->getCustomerCodeMaxLength();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerCodeMinLength()
    {
        return $this->parentMapper->getCustomerTaxClassNameMinLength();
    }

    /**
     * @inheritDoc
     */
    public function validateCustomerCode($fieldValue)
    {
        return $this->parentMapper->validateCustomerCode($fieldValue);
    }
}
