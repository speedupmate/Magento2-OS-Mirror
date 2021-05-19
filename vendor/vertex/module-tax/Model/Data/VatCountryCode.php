<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Vertex\Tax\Model\ResourceModel\VatCountryCode as ResourceModel;

/**
 * Model for storage of the Vertex Vat Country Code
 *
 * This model is used as the implementation for the vertex_vat_country_code extension attribute on the
 * @see \Magento\Sales\Api\Data\OrderAddressInterface
 */
class VatCountryCode extends AbstractModel
{
    /** @var string */
    const FIELD_ID = ResourceModel::FIELD_ID;

    /** @var string */
    const FIELD_CODE = ResourceModel::FIELD_CODE;

    /** @var string */
    const EXTENSION_ATTRIBUTE_CODE = 'vertex_vat_country_code';

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Get Vertex Vat Country Code
     *
     * @return string|null
     */
    public function getVatCountryCode(): ?string
    {
        return $this->getData(static::FIELD_CODE);
    }

    /**
     * Get Address ID
     *
     * @return int
     */
    public function getAddressId(): int
    {
        return (int) $this->getId();
    }

    /**
     * Set Vertex Vat Country Code
     *
     * @param string $customerCountry
     * @return $this
     */
    public function setVatCountryCode($customerCountry)
    {
        return $this->setData(static::FIELD_CODE, $customerCountry);
    }

    /**
     * Set Address ID
     *
     * @param int $addressId
     * @return $this
     */
    public function setAddressId($addressId)
    {
        return $this->setId($addressId);
    }
}
