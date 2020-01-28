<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Controller\Adminhtml\Attribute\Mapping\Product;

use Magento\Framework\Controller\ResultFactory;
use Temando\Shipping\Controller\Adminhtml\Activation\AbstractRegisteredAction;

/**
 * Temando Product Attribute Mapping
 *
 * @package Temando\Shipping\Controller
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Edit extends AbstractRegisteredAction
{
    const ADMIN_RESOURCE = 'Temando_Shipping::product';

    /**
     * Map product attributes to platform attributes
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Product Attribute Mapping'));
        return $resultPage;
    }
}
