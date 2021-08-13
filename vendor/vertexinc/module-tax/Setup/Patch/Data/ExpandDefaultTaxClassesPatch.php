<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ResourceModel\TaxClass;

/**
 * Expands the default tax class types for easier mapping in Vertex
 */
class ExpandDefaultTaxClassesPatch implements DataPatchInterface
{
    /** @var TaxClass */
    private $taxClassResource;

    public function __construct(TaxClass $taxClassResource)
    {
        $this->taxClassResource = $taxClassResource;
    }

    public function apply(): void
    {
        $adapter = $this->taxClassResource->getConnection();
        $taxClassTable = $this->taxClassResource->getMainTable();

        $existingClassNames = array_map(
            static function ($result) {
                return $result['class_name'];
            },
            $adapter->query($adapter->select()->from($taxClassTable))->fetchAll()
        );

        $data = array_filter(
            [
                [
                    'class_name' => 'Refund Adjustments',
                    'class_type' => TaxClassManagementInterface::TYPE_PRODUCT
                ],
                [
                    'class_name' => 'Gift Options',
                    'class_type' => TaxClassManagementInterface::TYPE_PRODUCT
                ],
                [
                    'class_name' => 'Order Gift Wrapping',
                    'class_type' => TaxClassManagementInterface::TYPE_PRODUCT
                ],
                [
                    'class_name' => 'Item Gift Wrapping',
                    'class_type' => TaxClassManagementInterface::TYPE_PRODUCT
                ],
                [
                    'class_name' => 'Printed Gift Card',
                    'class_type' => TaxClassManagementInterface::TYPE_PRODUCT
                ],
                [
                    'class_name' => 'Reward Points',
                    'class_type' => TaxClassManagementInterface::TYPE_PRODUCT
                ]
            ],
            static function ($newClass) use ($existingClassNames) {
                return !in_array($newClass['class_name'], $existingClassNames, false);
            }
        );

        $adapter->insertMultiple($taxClassTable, $data);
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
