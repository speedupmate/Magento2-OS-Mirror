<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Vertex\Tax\Model\ResourceModel\CustomOptionFlexibleField as ResourceModel;

/**
 * Model for storage of custom option mappings for products
 */
class CustomOptionFlexibleField extends AbstractModel
{
    const FIELD_FLEX_FIELD_ID = ResourceModel::FIELD_FLEX_FIELD_IDENTIFIER;
    const FIELD_OPTION_ID = ResourceModel::FIELD_OPTION_ID;
    const FIELD_WEBSITE_ID = ResourceModel::FIELD_WEBSITE_ID;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Retrieve the flexfield identifier
     *
     * @return string|null A string describing the type and ID of the flex field this option is mapped to
     */
    public function getFlexFieldId()
    {
        return $this->getData(static::FIELD_FLEX_FIELD_ID);
    }

    /**
     * Retrieve the product's Option ID
     *
     * @return int|null ID of a product option flexfields are mapped to
     */
    public function getOptionId()
    {
        return $this->getData(static::FIELD_OPTION_ID);
    }

    /**
     * Retrieve the website ID
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->getData(static::FIELD_WEBSITE_ID);
    }

    /**
     * Set the flexfield identifier
     *
     * @param string|null $flexFieldId
     * @return CustomOptionFlexibleField
     */
    public function setFlexFieldId($flexFieldId = null): CustomOptionFlexibleField
    {
        return $this->setData(static::FIELD_FLEX_FIELD_ID, $flexFieldId);
    }

    /**
     * Set the product's Option ID
     *
     * @param int $optionId
     * @return CustomOptionFlexibleField
     */
    public function setOptionId($optionId): CustomOptionFlexibleField
    {
        return $this->setData(static::FIELD_OPTION_ID, $optionId);
    }

    /**
     * Set the website ID
     *
     * @param int|null $websiteId
     * @return CustomOptionFlexibleField
     */
    public function setWebsiteId($websiteId): CustomOptionFlexibleField
    {
        return $this->setData(static::FIELD_WEBSITE_ID, $websiteId);
    }
}
