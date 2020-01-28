<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Quote;

use \Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;

/**
 * Class ChildrenValidationLocator
 *
 * Used to determine necessity to validate rule on item's children that may depends on product type
 */
class ChildrenValidationLocator
{
    /**
     * @var array
     */
    private $productTypeChildrenValidationMap;

    /**
     * @param array $productTypeChildrenValidationMap
     * <pre>
     * [
     *      'ProductType1' => true,
     *      'ProductType2' => false
     * ]
     * </pre>
     */
    public function __construct(
        array $productTypeChildrenValidationMap = []
    ) {
        $this->productTypeChildrenValidationMap = $productTypeChildrenValidationMap;
    }

    /**
     * Checks necessity to validate rule on item's children
     *
     * @param QuoteItem $item
     * @return bool
     */
    public function isChildrenValidationRequired(QuoteItem $item): bool
    {
        $type = $item->getProduct()->getTypeId();
        if (isset($this->productTypeChildrenValidationMap[$type])) {
            return (bool)$this->productTypeChildrenValidationMap[$type];
        }
        return true;
    }
}
