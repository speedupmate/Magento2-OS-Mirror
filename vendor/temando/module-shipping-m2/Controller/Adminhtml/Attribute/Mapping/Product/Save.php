<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Controller\Adminhtml\Attribute\Mapping\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Temando\Shipping\Model\Attribute\Mapping\Product;
use Temando\Shipping\Model\Attribute\Mapping\ProductInterface;
use Temando\Shipping\Model\ResourceModel\Attribute\Mapping\ProductRepository;

/**
 * Temando Product Attribute Mapping Save
 *
 * @package Temando\Shipping\Controller
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Save extends Action
{
    const ADMIN_RESOURCE = 'Temando_Shipping::product';

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * Save constructor.
     * @param Context $context
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Unable to save product attribute mapping.'));
            return $resultRedirect;
        }

        $rows = $this->getRequest()->getParams();

        if (array_key_exists('mapping', $rows) && is_array($rows['mapping'])) {
            foreach ($rows['mapping'] as $nodePath => $mapping) {
                if (preg_match('/^NEW_[0-9]*/', $nodePath)) {
                    $nodePathId = $mapping['id'];

                    if (!$nodePathId || !preg_match('/^[0-9a-zA-Z_\-.]{1,255}$/', $nodePathId)) {
                        $this->messageManager->addErrorMessage(
                            __('Could not save product attribute mapping, invalid node path.')
                        );
                        return $resultRedirect;
                    }

                    $description = $mapping['description'];
                    $mappingAttributeId = $mapping['mapped_attribute'];

                    $data = [
                        ProductInterface::NODE_PATH_ID => $nodePathId,
                        ProductInterface::DESCRIPTION => $description,
                        ProductInterface::MAPPED_ATTRIBUTE_ID => $mappingAttributeId,
                        ProductInterface::IS_DEFAULT => 0
                    ];
                } else {
                    $regex = sprintf(
                        '/^(%s\.)+(%s\.)*/',
                        Product::NODE_PATH_PREFIX,
                        Product::NODE_PATH_CUSTOM_ATTRIBUTES_PREFIX
                    );

                    $nodePathId = preg_replace($regex, '', $nodePath);
                    $mappingAttributeId = $mapping['mapped_attribute'];

                    $data = [
                        ProductInterface::NODE_PATH_ID => $nodePathId,
                        ProductInterface::MAPPED_ATTRIBUTE_ID => $mappingAttributeId
                    ];
                }

                try {
                    $this->productRepository->save($data);
                } catch (CouldNotSaveException $e) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Could not save product attribute mapping for attribute %1. %2',
                            $data['node_path_id'],
                            $e->getMessage()
                        )
                    );
                    return $resultRedirect;
                }
            }
        }

        $this->messageManager->addSuccessMessage(__('Product attribute mappings saved.'));
        return $resultRedirect;
    }
}
