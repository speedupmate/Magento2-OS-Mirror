<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\ResourceModel\Product\Attribute\Source;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Temando Product Attribute Country Source
 *
 * @package Temando\Shipping\Controller
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Country extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var CollectionFactory
     */
    protected $countryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Country constructor.
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param CollectionFactory $countryFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        CollectionFactory $countryFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->countryFactory = $countryFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->countryFactory->create()->loadByStore(
                $this->getStoreId()
            )->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return int|null
     */
    private function getStoreId()
    {
        try {
            return $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return null;
        }
    }
}
