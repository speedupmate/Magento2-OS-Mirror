<?php

namespace Dotdigitalgroup\Email\Block\Recommended;

class Push extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $priceHelper;

    /**
     * @var \Dotdigitalgroup\Email\Helper\Recommended
     */
    public $recommnededHelper;

    /**
     * @var \Dotdigitalgroup\Email\Model\ResourceModel\Catalog
     */
    public $catalog;

    /**
     * Push constructor.
     *
     * @param \Dotdigitalgroup\Email\Model\ResourceModel\Catalog $catalog
     * @param \Dotdigitalgroup\Email\Helper\Data                        $helper
     * @param \Magento\Framework\Pricing\Helper\Data                    $priceHelper
     * @param \Dotdigitalgroup\Email\Helper\Recommended                 $recommended
     * @param \Magento\Catalog\Block\Product\Context                    $context
     * @param array                                                     $data
     */
    public function __construct(
        \Dotdigitalgroup\Email\Model\ResourceModel\Catalog $catalog,
        \Dotdigitalgroup\Email\Helper\Data $helper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Dotdigitalgroup\Email\Helper\Recommended $recommended,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper            = $helper;
        $this->catalog    = $catalog;
        $this->recommnededHelper = $recommended;
        $this->priceHelper       = $priceHelper;
    }

    /**
     * Get the products to display for table.
     *
     * @return mixed
     */
    public function getLoadedProductCollection()
    {
        $params = $this->getRequest()->getParams();
        if (! isset($params['code']) || ! $this->helper->isCodeValid($params['code'])) {
            $this->helper->log('Product push no valid code is set');
            return [];
        }

        $mode = $this->getRequest()->getActionName();
        $limit = $this->recommnededHelper->getDisplayLimitByMode($mode);
        $productIds = $this->recommnededHelper->getProductPushIds();
        $productCollection = $this->catalog->getProductCollectionFromIds($productIds, $limit);

        //important check the salable product in template
        return $productCollection;
    }

    /**
     * Display  type mode.
     *
     * @return mixed|string
     */
    public function getMode()
    {
        return $this->recommnededHelper->getDisplayType();
    }

    /**
     * @param null|string|bool|int|\Magento\Store\Api\Data\StoreInterface $store
     *
     * @return mixed
     */
    public function getTextForUrl($store)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore($store);

        return $store->getConfig(
            \Dotdigitalgroup\Email\Helper\Config::XML_PATH_CONNECTOR_DYNAMIC_CONTENT_LINK_TEXT
        );
    }
}
