<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\State;
use Vertex\Tax\Model\Cache\Type;
use Magento\Framework\App\DeploymentConfig;

/**
 * Data Upgrade Script
 *
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var StateInterface */
    private $cacheState;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var TypeListInterface */
    private $cacheTypeList;

    /** @var Config */
    private $eavConfig;

    /** @var DeploymentConfig */
    private $deploymentConfig;

    /**
     * @param Config $eavConfig
     * @param AttributeRepositoryInterface $attributeRepository
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface $cacheState
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Config $eavConfig,
        AttributeRepositoryInterface $attributeRepository,
        TypeListInterface $cacheTypeList,
        StateInterface $cacheState,
        DeploymentConfig $deploymentConfig
    ) {
        $this->eavConfig = $eavConfig;
        $this->attributeRepository = $attributeRepository;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheState = $cacheState;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '100.1.0') < 0) {
            $this->migrateCustomAttributeToExtensionAttribute($setup);
            $this->deleteCustomAttribute();
        }
        if (version_compare($context->getVersion(), '100.2.1') < 0) {
            $this->migrateVertexCalculationSetting($setup);
        }
        if (version_compare($context->getVersion(), '100.5.1') < 0) {
            $this->enableCacheType($setup);
        }
    }

    /**
     * Deletes the "customer_code" custom attribute, if created
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function deleteCustomAttribute()
    {
        $attribute = $this->getEntityAttribute(Customer::ENTITY, 'customer_code');
        if (!$attribute) {
            return;
        }
        $this->attributeRepository->delete($attribute);
    }

    /**
     * Enable the cache type if undefined in the config
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function enableCacheType(ModuleDataSetupInterface $setup)
    {
        $statuses = $this->deploymentConfig->getConfigData(State::CACHE_KEY);

        if (!array_key_exists(Type::TYPE_IDENTIFIER, $statuses)) {
            $this->cacheState->setEnabled(Type::TYPE_IDENTIFIER, true);
            $this->cacheState->persist();
        }
    }

    /**
     * Retrieve an entity attribute
     *
     * @param string $entity
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|void
     * @throws LocalizedException
     */
    private function getEntityAttribute($entity, $attributeCode)
    {
        if (method_exists($this->eavConfig, 'getEntityAttributes')) {
            $attributes = $this->eavConfig->getEntityAttributes($entity);
            if (!isset($attributes[$attributeCode])) {
                return;
            }

            return $attributes[$attributeCode];
        }

        $attributeCodes = $this->eavConfig->getEntityAttributeCodes($entity);
        if (!in_array($attributeCode, $attributeCodes)) {
            return;
        }

        return $this->eavConfig->getAttribute($entity, $attributeCode);
    }

    /**
     * Perform migration of custom attributes to extension attributes
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function migrateCustomAttributeToExtensionAttribute(ModuleDataSetupInterface $setup)
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
            function ($rawResult) {
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

    /**
     * Remove any user settings where VERTEX was the tax calculation mode
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function migrateVertexCalculationSetting(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->delete(
            $setup->getTable('core_config_data'),
            [
                'path = ?' => 'tax/calculation/algorithm',
                'value IN (?)' => ['VERTEX_UNIT_BASE_CALCULATION', 'VERTEXSMB_UNIT_BASE_CALCULATION']
            ]
        );
        $this->cacheTypeList->invalidate('CONFIG');
    }
}
