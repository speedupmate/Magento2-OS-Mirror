<?php

namespace Dotdigitalgroup\Email\Observer\Customer;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Wishlist delete.
 */
class RemoveWishlist implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    private $helper;

    /**
     * @var \Dotdigitalgroup\Email\Model\ImporterFactory
     */
    private $importerFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * RemoveWishlist constructor.
     *
     * @param \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory
     * @param \Dotdigitalgroup\Email\Helper\Data $data
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory,
        \Dotdigitalgroup\Email\Helper\Data $data,
        StoreManagerInterface $storeManager
    ) {
        $this->importerFactory = $importerFactory;
        $this->helper          = $data;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
            $wishlist = $observer->getEvent()->getDataObject();
            $websiteId = $this->storeManager->getWebsite()->getId();
            $isEnabled = $this->helper->isEnabled($websiteId);
            $syncEnabled = $this->helper->getWebsiteConfig(
                \Dotdigitalgroup\Email\Helper\Config::XML_PATH_CONNECTOR_SYNC_WISHLIST_ENABLED,
                $websiteId
            );

            //create a queue item to remove single wishlist
            if ($isEnabled && $syncEnabled && $wishlist->getId()) {
                //register in queue with importer
                $this->importerFactory->create()->registerQueue(
                    \Dotdigitalgroup\Email\Model\Importer::IMPORT_TYPE_WISHLIST,
                    [$wishlist->getId()],
                    \Dotdigitalgroup\Email\Model\Importer::MODE_SINGLE_DELETE,
                    $websiteId
                );
            }
        } catch (\Exception $e) {
            $this->helper->debug((string)$e, []);
        }
    }
}
