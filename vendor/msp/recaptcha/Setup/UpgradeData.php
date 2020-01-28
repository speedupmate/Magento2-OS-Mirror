<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_ReCaptcha
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace MSP\ReCaptcha\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * UpgradeData constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Move config from srcPath to dstPath
     * @param ModuleDataSetupInterface $setup
     * @param string $srcPath
     * @param string $dstPath
     */
    private function moveConfig(ModuleDataSetupInterface $setup, $srcPath, $dstPath): void
    {
        $value = $this->scopeConfig->getValue($srcPath);

        if (is_array($value)) {
            foreach (array_keys($value) as $k) {
                $this->moveConfig($setup, $srcPath . '/' . $k, $dstPath . '/' . $k);
            }
        } else {
            $connection = $setup->getConnection();
            $configData = $setup->getTable('core_config_data');
            $connection->update($configData, ['path' => $dstPath], 'path=' . $connection->quote($srcPath));
        }
    }

    /**
     * Upgrade to 1.1.0
     * @param ModuleDataSetupInterface $setup
     */
    private function upgradeTo010100(ModuleDataSetupInterface $setup): void
    {
        $this->moveConfig(
            $setup,
            'msp_securitysuite/recaptcha',
            'msp_securitysuite_recaptcha/general'
        );
    }

    /**
     * Upgrade to 1.2.0
     * @param ModuleDataSetupInterface $setup
     */
    private function upgradeTo010200(ModuleDataSetupInterface $setup): void
    {
        $this->moveConfig(
            $setup,
            'msp_securitysuite_recaptcha/general/enabled_frontend',
            'msp_securitysuite_recaptcha/frontend/enabled'
        );
        $this->moveConfig(
            $setup,
            'msp_securitysuite_recaptcha/general/enabled_backend',
            'msp_securitysuite_recaptcha/backend/enabled'
        );
    }

    /**
     * Upgrade to 1.6.0
     * @param ModuleDataSetupInterface $setup
     */
    private function upgradeTo010600(ModuleDataSetupInterface $setup): void
    {
        $this->moveConfig(
            $setup,
            'msp_securitysuite_recaptcha/frontend/type',
            'msp_securitysuite_recaptcha/general/type'
        );
    }

    /**
     * @inheritDoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $this->upgradeTo010100($setup);
        }

        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            $this->upgradeTo010200($setup);
        }

        if (version_compare($context->getVersion(), '1.6.0') < 0) {
            $this->upgradeTo010600($setup);
        }

        $setup->endSetup();
    }
}
