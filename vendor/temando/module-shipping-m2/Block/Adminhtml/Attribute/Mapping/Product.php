<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Block\Attribute\Mapping;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Temando Product Attribute Mapping
 *
 * @package Temando\Shipping\Controller
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Product extends Template
{
    /**
     * @var array
     */
    private $layoutProcessors;

    /**
     * Button constructor.
     * @param Context $context
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutProcessors = $layoutProcessors;
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout(): string
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return parent::getJsLayout();
    }
}
