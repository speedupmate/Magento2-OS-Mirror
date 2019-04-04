<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Plugin;

use Vertex\Tax\Model\Calculation\VertexCalculator;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Prepare tax service errors.
 *
 * During admin order management, tax calculation errors must be given where possible.
 * This plugin transfers those error messages to the message block before it is rendered.
 */
class OrderCreateTotalsPlugin
{
    /** @var ManagerInterface */
    private $messageManager;

    /** @var Registry */
    private $registry;

    /** @var boolean A state flag to ensure that errors are only dispatched once. */
    private $hasNotified = false;

    /**
     * @param ManagerInterface $messageManager
     * @param Registry         $registry
     */
    public function __construct(ManagerInterface $messageManager, Registry $registry)
    {
        $this->messageManager = $messageManager;
        $this->registry = $registry;
    }

    /**
     * Resend data from the message manager to the target message block pre-render.
     *
     * @param  Totals $subject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateMessageBlock(Totals $context)
    {
        $block = $context->getLayout()->getBlock('message');

        if ($block instanceof BlockInterface) {
            $block->addMessages($this->messageManager->getMessages(true));
        }
    }

    /**
     * Add tax service errors after totals collection.
     *
     * @param  Totals $subject
     * @param  array  $results
     * @return array
     */
    public function afterGetTotals(Totals $subject, array $results)
    {
        if (!$this->hasNotified) {
            $error = $this->registry->registry(VertexCalculator::VERTEX_CALCULATION_ERROR);

            if (!empty($error)) {
                $this->updateMessageBlock($subject);

                $this->hasNotified = true;
            }
        }

        return $results;
    }
}
