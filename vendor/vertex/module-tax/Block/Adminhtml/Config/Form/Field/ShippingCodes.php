<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Shipping\Model\Config;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Vertex\Exception\ConfigurationException;
use Vertex\Mapper\LineItemMapperInterface;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;

/**
 * Renders a list of shipping codes to be used as a reference when setting up Vertex
 */
class ShippingCodes extends Field
{
    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /** @var Config */
    private $shippingConfig;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /**
     * @param Context $context
     * @param Config $shippingConfig
     * @param GroupRepositoryInterface $groupRepository
     * @param MapperFactoryProxy $mapperFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $shippingConfig,
        GroupRepositoryInterface $groupRepository,
        MapperFactoryProxy $mapperFactory,
        array $data = []
    ) {
        $this->shippingConfig = $shippingConfig;
        $this->scopeConfig = $context->getScopeConfig();
        $this->groupRepository = $groupRepository;
        $this->mapperFactory = $mapperFactory;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve the mapper class if defined
     *
     * @return LineItemMapperInterface|bool
     */
    private function getMapper()
    {
        try {
            return $this->mapperFactory->getForClass(LineItemMapperInterface::class);
        } catch (\InvalidArgumentException $exception) {
            return false;
        } catch (ConfigurationException $e) {
            return false;
        }
    }

    /**
     * Retrieve the ID of the store
     *
     * @return int
     */
    private function getStoreId()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $websiteId = (int) $this->getRequest()->getParam('website', 0);

        if ($storeId === 0 && $websiteId > 0) {
            try {
                $groupId = $this->_storeManager->getWebsite($websiteId)->getDefaultGroupId();
                $group = $this->groupRepository->get($groupId);
                return $group->getDefaultStoreId();
            } catch (\Exception $e) {
                return 0;
            }
        }

        return $storeId;
    }

    /**
     * @inheritdoc
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $storeId = $this->getStoreId();

        $html = '<table cellspacing="0" class="data-grid"><thead>';
        $html .= '<tr><th class="data-grid-th">Shipping Method</th><th class="data-grid-th">Product Code</th></tr>';
        $html .= '</thead><tbody>';
        $methods = $this->shippingConfig->getActiveCarriers($storeId);

        /** @var LineItemMapperInterface $mapper */
        $mapper = $this->getMapper();
        $productCodeMaxLength = 0;
        if ($mapper) {
            $productCodeMaxLength = $mapper->getProductCodeMaxLength();
        }

        foreach ($methods as $carrierCode => $carrier) {
            if ($carrierMethods = $carrier->getAllowedMethods()) {

                $title = $this->scopeConfig->getValue(
                    "carriers/$carrierCode/title",
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );

                if (!$title) {
                    $title = $carrierCode;
                }

                $html .= '<tr><th class="data-grid-th"   colspan="2">' . $this->escapeHtml($title) . '</th></tr>';

                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $carrierCode . '_' . $methodCode;

                    if ($productCodeMaxLength) {
                        $code = mb_substr($code, 0, $productCodeMaxLength);
                    }

                    $html .= '<tr class="" >';
                    $html .= '<td class="label"  style="padding:1rem;" >' . $this->escapeHtml($method) . ': </td>';
                    $html .= '<td class="value" style="padding:1rem;" > ' . $this->escapeHtml($code) . '</td>';
                    $html .= '</tr>';
                }
            }
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
