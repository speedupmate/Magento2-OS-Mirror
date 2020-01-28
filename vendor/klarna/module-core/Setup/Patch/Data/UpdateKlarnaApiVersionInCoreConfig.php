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

class UpdateKlarnaApiVersionInCoreConfig implements DataPatchInterface, PatchVersionInterface
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
        $this->updateKlarnaApiVersion();
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
        return '4.0.7';
    }

    /**
     * Updating the Klarna api version
     */
    private function updateKlarnaApiVersion()
    {
        $configTable = $this->moduleDataSetup->getTable('core_config_data');
        $this->moduleDataSetup
            ->getConnection()
            ->forUpdate(
                "INSERT INTO ($configTable) (`scope`, `scope_id`, `path`, `value`) "
                . "SELECT 'websites', `scope_id`, `path`, `value` FROM `($configTable)` AS `b` "
                . "WHERE `path`='klarna/api/api_version' AND `scope`='stores' "
                . "ON DUPLICATE KEY UPDATE `value`=`b`.`value`;"
            );

        $this->moduleDataSetup
            ->getConnection()
            ->delete($configTable, "`path`='klarna/api/api_version' AND `scope`='stores'");
    }
}
