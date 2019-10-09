<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Processes a Flex fields for Invoices, Orders and Creditmemos
 *
 * @api
 * @since 3.2.0
 */
interface InvoiceFlexFieldProcessorInterface extends ProcessorInterface
{
    /**
     * Retrieve value from Invoice attribute
     *
     * @param InvoiceItemInterface $item
     * @param $attributeCode
     * @param string|null $fieldType
     * @param string|int|null $fieldId
     * @return string|int|null
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null);

    /**
     * Retrieve value from Order attribute
     *
     * @param OrderItemInterface $item
     * @param $attributeCode
     * @param string|null $fieldType
     * @param string|int|null $fieldId
     * @return string|int|null
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null);

    /**
     * Retrieve value from Creditmemo attribute
     *
     * @param CreditmemoItemInterface $item
     * @param $attributeCode
     * @param string|null $fieldType
     * @param string|int|null $fieldId
     * @return string|int|null
     */
    public function getValueFromCreditmemo(
        CreditmemoItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    );
}
