<?php
/**
 * This file is part of the Klarna Kp module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class RemoveHtmlTag implements DataPatchInterface, PatchVersionInterface
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
        $this->removeStrongHtmlTag();
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
        return '5.4.5';
    }

    /**
     * Remove the html tag 'strong' from the additional information of the payments
     */
    private function removeStrongHtmlTag()
    {
        $values = [
            '<strong>',
            '<\/strong>'
        ];
        foreach ($values as $value) {
            $manipulation = new \Zend_Db_Expr("replace(`additional_information`, '$value', '')");
            $this->moduleDataSetup->getConnection()
                ->update(
                    $this->moduleDataSetup->getTable('sales_order_payment'),
                    ['additional_information' => $manipulation],
                    "`method` = 'klarna_kp'"
                );
        }
    }
}
