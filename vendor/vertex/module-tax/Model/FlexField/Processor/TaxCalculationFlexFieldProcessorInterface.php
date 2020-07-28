<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

/**
 * Processes a Flex fields for Quotes and Orders
 *
 * @api
 * @since 3.2.0
 */
interface TaxCalculationFlexFieldProcessorInterface extends ProcessorInterface
{
    /**
     * Retrieve value from Quote attribute
     *
     * @param QuoteDetailsItemInterface $item
     * @param $attributeCode
     * @param string|null $fieldType
     * @param string|int|null $fieldId
     * @return string|int|null
     */
    public function getValueFromQuote(
        QuoteDetailsItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    );
}
