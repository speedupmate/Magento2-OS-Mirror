<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class MigrateCustomerCodeFromEav implements DataPatchInterface, PatchVersionInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var Config */
    private $eavConfig;

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Config $eavConfig,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavConfig = $eavConfig;
        $this->attributeRepository = $attributeRepository;
    }

    public function apply(): void
    {
        $setup = $this->moduleDataSetup;
        $this->migrateCustomAttributeToExtensionAttribute($setup);
        $this->deleteCustomAttribute();
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public static function getVersion(): string
    {
        return '100.1.0';
    }

    /**
     * Deletes the "customer_code" custom attribute, if created
     *
     * @throws LocalizedException
     * @throws StateException
     */
    private function deleteCustomAttribute(): void
    {
        $attribute = $this->getEntityAttribute(Customer::ENTITY, 'customer_code');
        if ($attribute) {
            $this->attributeRepository->delete($attribute);
        }
    }

    /**
     * Retrieve an entity attribute
     */
    private function getEntityAttribute(string $entity, string $attributeCode): ?AbstractAttribute
    {
        return $this->eavConfig->getEntityAttributes($entity)[$attributeCode] ?? null;
    }

    /**
     * Perform migration of custom attributes to extension attributes
     */
    private function migrateCustomAttributeToExtensionAttribute(ModuleDataSetupInterface $setup): void
    {
        $db = $setup->getConnection();
        $attribute = $this->getEntityAttribute(Customer::ENTITY, 'customer_code');
        if (!$attribute) {
            return;
        }

        $select = $db->select()
            ->from($setup->getTable('customer_entity_varchar'), ['entity_id', 'value'])
            ->where('attribute_id = ?', $attribute->getId());

        $results = array_map(
            static function ($rawResult) {
                return [
                    'customer_id' => $rawResult['entity_id'],
                    'customer_code' => $rawResult['value'],
                ];
            },
            $db->fetchAll($select)
        );

        if (!count($results)) {
            return;
        }

        $db->insertMultiple(
            $setup->getTable('vertex_customer_code'),
            $results
        );
    }
}
