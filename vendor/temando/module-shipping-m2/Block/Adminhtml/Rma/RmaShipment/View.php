<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Block\Adminhtml\Rma\RmaShipment;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Rma\Api\Data\RmaInterface;
use Temando\Shipping\Model\ResourceModel\Rma\RmaAccess;

/**
 * RMA Shipment View
 *
 * @package Temando\Shipping\Block
 * @author  Rhodri Davies <rhodri.davies@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 *
 * @api
 */
class View extends Container
{
    /**
     * @var RmaAccess
     */
    private $rmaAccess;

    /**
     * View constructor.
     * @param Context $context
     * @param RmaAccess $rmaAccess
     * @param mixed[] $data
     */
    public function __construct(Context $context, RmaAccess $rmaAccess, array $data = [])
    {
        $this->rmaAccess = $rmaAccess;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Container
     */
    protected function _prepareLayout()
    {
        $rma = $this->rmaAccess->getCurrentRma();
        if (!$rma) {
            return parent::_prepareLayout();
        }

        $dispatchCreateUrl = $this->getUrl('temando/rma_shipment/dispatch', [
            'rma_id' => $rma->getEntityId(),
            'ext_shipment_id' => $this->rmaAccess->getCurrentRmaShipment()->getShipmentId()
        ]);

        $this->addButton('temando_dispatch_return_shipment', [
            'label' => __('Dispatch Shipment'),
            'class' => 'primary',
            'onclick' => sprintf("setLocation('%s')", $dispatchCreateUrl)
        ]);

        return parent::_prepareLayout();
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        // append all child blocks
        $html.= $this->getChildHtml();

        return $html;
    }
}
