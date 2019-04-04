<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Init module data
 *
 * @package Temando\Shipping\Setup
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SetupData
     */
    private $installer;

    /**
     * @var BookmarkCleaner
     */
    private $bookmarkCleaner;

    /**
     * UpgradeData constructor.
     * @param SetupData $installer
     * @param BookmarkCleaner $bookmarkCleaner
     */
    public function __construct(SetupData $installer, BookmarkCleaner $bookmarkCleaner)
    {
        $this->installer = $installer;
        $this->bookmarkCleaner = $bookmarkCleaner;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // beware, this is the version we are upgrading from, not to!
        $moduleVersion = $context->getVersion();

        if (version_compare($moduleVersion, '1.2.0', '<')) {
            $this->installer->addDimensionAttributes($setup);
        }

        if (version_compare($moduleVersion, '1.4.0', '<')) {
            $this->installer->addPickupOrderEmailTemplate();
            $this->installer->addPickupOrderGuestEmailTemplate();
        }

        if (version_compare($moduleVersion, '1.5.0', '<')) {
            $this->bookmarkCleaner->resetPickupGrid();
            $this->bookmarkCleaner->resetOrderPickupGrid();
        }
    }
}
