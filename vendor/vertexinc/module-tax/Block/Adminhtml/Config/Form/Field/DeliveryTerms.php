<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Vertex\Tax\Model\Config\Source\DeliveryTerm;

/**
 * HTML select for Delivery Terms
 */
class DeliveryTerms extends Select
{
    /** @var DeliveryTerm */
    private $deliveryTerms;

    /**
     * @param DeliveryTerm $deliveryTerms
     * @param Context $context
     * @param array $data
     */
    public function __construct(DeliveryTerm $deliveryTerms, Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->deliveryTerms = $deliveryTerms;
    }

    /**
     * Get Delivery Terms options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->deliveryTerms->toOptionArray();
    }

    /**
     * Set Name
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setData('name', $value);
    }
}
