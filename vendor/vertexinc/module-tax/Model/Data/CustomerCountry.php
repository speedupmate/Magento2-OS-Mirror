<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Vertex\Tax\Model\ResourceModel\CustomerCountry as ResourceModel;

/**
 * Model for storage of the Vertex Customer Country
 *
 * This model is used as the implementation for the vertex_customer_country extension attribute on the
 * {@see \Magento\Customer\Api\Data\CustomerInterface}
 */
class CustomerCountry extends AbstractModel
{
    /** @var string */
    const FIELD_ID = ResourceModel::FIELD_ID;

    /** @var string */
    const FIELD_CODE = ResourceModel::FIELD_CODE;

    /** @var string */
    const EXTENSION_ATTRIBUTE_CODE = 'vertex_customer_country';

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Get Vertex Customer Country
     *
     * @return string|null
     */
    public function getCustomerCountry(): ?string
    {
        return $this->getData(static::FIELD_CODE);
    }

    /**
     * Get Customer ID
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int) $this->getId();
    }

    /**
     * Set Vertex Customer Country
     *
     * @param string $customerCountry
     * @return $this
     */
    public function setCustomerCountry($customerCountry)
    {
        return $this->setData(static::FIELD_CODE, $customerCountry);
    }

    /**
     * Set Customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setId($customerId);
    }
}
