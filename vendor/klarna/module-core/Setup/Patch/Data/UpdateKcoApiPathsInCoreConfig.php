<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateKcoApiPathsInCoreConfig implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->updateKlarnaKcoApiPathKeys();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * Updating the Klarna api path keys
     */
    private function updateKlarnaKcoApiPathKeys()
    {
        $configTable = $this->moduleDataSetup->getTable('core_config_data');
        $oldKeys = [
            'payment/klarna_kco/merchant_id',
            'payment/klarna_kco/shared_secret',
            'payment/klarna_kco/api_version',
            'payment/klarna_kco/test_mode',
            'payment/klarna_kco/debug',
        ];
        $newKeys = [
            'klarna/api/merchant_id',
            'klarna/api/shared_secret',
            'klarna/api/api_version',
            'klarna/api/test_mode',
            'klarna/api/debug',
        ];

        foreach ($oldKeys as $id => $oldKey) {
            $newKey = $newKeys[$id];
            $this->moduleDataSetup
                ->getConnection()
                ->update($configTable, ['path' => $newKey], "`path`='{$oldKey}'");
        }

        $keys = '\'' . implode('\',\'', $oldKeys) . '\'';
        $keys = str_replace('klarna_kco', 'klarna_kp', $keys);
        $this->moduleDataSetup
            ->getConnection()
            ->delete($configTable, "`path` in ({$keys})");
    }
}
