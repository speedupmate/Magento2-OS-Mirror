<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\Layout\AbstractTotalsProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Vertex\Tax\Model\Calculator;
use Vertex\Tax\Model\Config;

/**
 * When processing the layout, ensure the Vertex message block is located directly beneath the tax total block
 */
class SortTaxMessageProcessor extends AbstractTotalsProcessor implements LayoutProcessorInterface
{
    /** @var ArrayManager */
    private $arrayManager;

    /** @var Config */
    private $config;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config,
        StoreManagerInterface $storeManager,
        ArrayManager $arrayManager
    ) {
        parent::__construct($scopeConfig);
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     */
    public function process($jsLayout)
    {
        $storeId = null;
        if ($currentStore = $this->storeManager->getStore()) {
            $storeId = $currentStore->getId();
        }

        // remove vertex-messages if disabled
        if (!$this->config->isVertexActive($storeId) || !$this->config->isTaxCalculationEnabled($storeId)) {
            $pathsToRemove = $this->arrayManager->findPaths(Calculator::MESSAGE_KEY, $jsLayout);
            foreach ($pathsToRemove as $path) {
                $this->arrayManager->remove($path, $jsLayout);
            }

            return $jsLayout;
        }

        $configData = $this->scopeConfig->getValue('sales/totals_sort');
        $newPos = $configData['tax'] + 1;
        $findMessagesPaths = $this->arrayManager->findPaths(Calculator::MESSAGE_KEY, $jsLayout);
        foreach ($findMessagesPaths as $path) {
            $parentPath = rtrim($path, '/' . Calculator::MESSAGE_KEY);
            $parent = $this->arrayManager->get($parentPath, $jsLayout);
            $parent[Calculator::MESSAGE_KEY]['sortOrder'] = (string) $newPos;
            $jsLayout = $this->arrayManager->set($parentPath, $jsLayout, $this->sortTotals($parent));
        }

        return $jsLayout;
    }
}
