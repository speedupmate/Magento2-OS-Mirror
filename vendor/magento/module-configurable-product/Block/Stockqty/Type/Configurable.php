<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Stockqty\Type;

use Magento\Catalog\Model\Product;

/**
 * Product stock qty block for configurable product type
 */
class Configurable extends \Magento\CatalogInventory\Block\Stockqty\Composite
{
    /**
     * Retrieve child products
     *
     * @return Product[]
     */
    protected function _getChildProducts()
    {
        return $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());
    }
}
