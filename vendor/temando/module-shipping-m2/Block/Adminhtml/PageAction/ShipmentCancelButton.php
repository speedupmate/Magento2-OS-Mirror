<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Block\Adminhtml\PageAction;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\UrlInterface;
use Temando\Shipping\Model\Shipment\ShipmentProviderInterface;

/**
 * Action Button to Cancel Shipment Action
 *
 * @api
 * @package Temando\Shipping\Block
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentCancelButton extends Button
{
    /**
     * @var ShipmentProviderInterface
     */
    private $shipmentProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param ShipmentProviderInterface $shipmentProvider
     * @param UrlInterface $urlBuilder
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        ShipmentProviderInterface $shipmentProvider,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->shipmentProvider = $shipmentProvider;
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $data);
    }

    /**
     * Append confirm component to button
     *
     * @return string
     */
    public function getAfterHtml()
    {
        $shipment = $this->shipmentProvider->getShipment();
        $salesShipment = $this->shipmentProvider->getSalesShipment();
        if (!$shipment || !$salesShipment) {
            return $this->getData('after_html');
        }

        $url = $this->urlBuilder->getUrl('temando/shipment/cancel', [
            'shipment_id' => $shipment->getShipmentId(),
            'sales_shipment_id' => $salesShipment->getEntityId(),
        ]);

        $this->jsLayout = [
            'title' => $this->escapeHtml($this->getData('label')),
            'message' => $this->escapeHtml(__('Are you sure you want to cancel this shipment?')),
            'url' => $this->escapeUrl($url),
            'loader' => true,
        ];

        $confirmComponent = <<<HTML
<script type="text/x-magento-init">
{
    "#{$this->getData('id')}": {
        "Temando_Shipping/js/modal/confirm": {$this->getJsLayout()}
    }
}
</script>
HTML;

        return $this->getData('after_html') . $confirmComponent;
    }

    /**
     * Add button data
     *
     * @return string
     */
    protected function _toHtml()
    {
        $shipment = $this->shipmentProvider->getShipment();
        if (!$shipment->isCancelable()) {
            return '';
        }

        $this->setData('label', __('Cancel Shipment'));
        $this->setData('class', 'cancel');
        $this->setData('id', 'cancel');

        return parent::_toHtml();
    }
}
