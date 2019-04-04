<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Block\Adminhtml\Activation;

use Magento\Backend\Block\Template;
use Magento\Framework\Exception\LocalizedException;

/**
 * Activation Notice Layout Block
 *
 * @package Temando\Shipping\Block
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 *
 * @api
 */
class Notice extends Template
{
    /**
     * Update the page title depending on where user is coming from,
     *
     * @return void
     */
    protected function _construct()
    {
        try {
            $layout = $this->getLayout();
        } catch (LocalizedException $exception) {
            $this->_logger->error($exception->getLogMessage(), ['exception' => $exception]);

            parent::_construct();
            return;
        }

        /** @var $menuBlock \Magento\Backend\Block\Menu */
        $menuBlock = $layout->getBlock('menu');
        if ($menuBlock) {
            $itemId = 'Temando_Shipping::shipping';

            $menuBlock->setData('active', $itemId);
            $menuItems = $menuBlock->getMenuModel()->getParentItems($itemId);
            $menuItems[]= $menuBlock->getMenuModel()->get($itemId);
            foreach ($menuItems as $item) {
                /** @var $item \Magento\Backend\Model\Menu\Item */
                $this->pageConfig->getTitle()->prepend($item->getTitle());
            }
        }

        $subject = $this->getRequest()->getParam('subject');
        switch ($subject) {
            // merchant account configuration
            case 'carrier':
                $this->pageConfig->getTitle()->prepend(__('Shipping Partners'));
                $this->assign('subject', __('carriers'));
                break;
            case 'dispatch':
                $this->pageConfig->getTitle()->prepend(__('Dispatches'));
                $this->assign('subject', __('dispatches'));
                break;
            case 'location':
                $this->pageConfig->getTitle()->prepend(__('Locations'));
                $this->assign('subject', __('shipping locations'));
                break;
            case 'packaging':
                $this->pageConfig->getTitle()->prepend(__('Packaging'));
                $this->assign('subject', __('packaging types'));
                break;
            // shipment creation
            case 'shipment':
                $this->pageConfig->getTitle()->prepend(__('Returns'));
                $this->assign('subject', __('RMA shipments'));
                break;
            case 'batch':
                $this->pageConfig->getTitle()->prepend(__('Batches'));
                $this->assign('subject', __('batches'));
                break;
            // settings configuration
            case 'advanced':
                $this->pageConfig->getTitle()->prepend(__('Advanced Settings'));
                $this->assign('subject', __('advanced settings'));
                break;
            case 'checkout':
                $this->pageConfig->getTitle()->prepend(__('Checkout View Settings'));
                $this->assign('subject', __('checkout options'));
                break;
            default:
                $this->pageConfig->getTitle()->prepend(__('Magento Shipping'));
                $this->assign('subject', __('Magento Shipping'));
        }

        parent::_construct();
    }

    /**
     * Get the current context, i.e. where the user was forwarded from.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->_viewVars['subject'];
    }

    /**
     * Obtain module configuration section URL.
     *
     * @return string
     */
    public function getConfigUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit', [
            'section' => 'carriers',
            '_fragment' => 'carriers_temando-link',
        ]);
    }
}
