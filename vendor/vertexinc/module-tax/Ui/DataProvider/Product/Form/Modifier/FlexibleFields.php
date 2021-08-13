<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Ui\DataProvider\Product\Form\Modifier;

use Exception;
use Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\FlexField\CustomOptionsSourceFactory;
use Vertex\Tax\Model\Registry\ProductEditScopeRegistry;
use Vertex\Tax\Model\ResourceModel\CustomOptionFlexibleField;

/**
 * Adds "Vertex Flexible Field" input to Product Customizable Options
 */
class FlexibleFields extends AbstractModifier
{
    const FIELD_FLEX_FIELD = 'vertex_flex_field';

    /** @var ArrayManager */
    private $arrayManager;

    /** @var Config */
    private $config;

    /** @var CustomOptionsSourceFactory */
    private $customOptionsSourceFactory;

    /** @var CustomOptionFlexibleField */
    private $flexFieldResource;

    /** @var LocatorInterface */
    private $locator;

    /** @var ExceptionLogger */
    private $logger;

    /**
     * @param ArrayManager $arrayManager
     * @param Config $config
     * @param LocatorInterface $locator
     * @param CustomOptionsSourceFactory $customOptionsSourceFactory
     * @param ExceptionLogger $logger
     * @param CustomOptionFlexibleField $flexFieldResource
     */
    public function __construct(
        ArrayManager $arrayManager,
        Config $config,
        LocatorInterface $locator,
        CustomOptionsSourceFactory $customOptionsSourceFactory,
        ExceptionLogger $logger,
        CustomOptionFlexibleField $flexFieldResource
    ) {
        $this->arrayManager = $arrayManager;
        $this->config = $config;
        $this->locator = $locator;
        $this->logger = $logger;
        $this->customOptionsSourceFactory = $customOptionsSourceFactory;
        $this->flexFieldResource = $flexFieldResource;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data): array
    {
        if (!$this->isActive()) {
            return $data;
        }

        foreach ($data as &$datum) {
            if (!isset($datum['product']['options']) || !is_array($datum['product']['options'])) {
                continue;
            }

            $optionIds = array_map(
                static function ($option) {
                    return $option['option_id'];
                },
                $datum['product']['options']
            );

            $customOptionFlexFields = $this->flexFieldResource->loadForOptions(
                $optionIds,
                $this->getWebsiteId()
            );

            foreach ($datum['product']['options'] as &$option) {
                $option['default_vertex_flex_field'] = '';
                $option['vertex_flex_field_is_default'] = true;
                $option['vertex_flex_field'] = '';

                $optionId = $option['option_id'];

                if (isset($customOptionFlexFields[$optionId])
                    && $customOptionFlexField = $customOptionFlexFields[$optionId]) {
                    $option['vertex_flex_field_is_default'] = (int)$customOptionFlexField->getWebsiteId() === 0;
                    $option['vertex_flex_field'] = $customOptionFlexFields[$option['option_id']]->getFlexFieldId();
                }
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta): array
    {
        if (!$this->isActive()) {
            return $meta;
        }

        $websiteCode = $this->getWebsiteId();

        $meta = $this->addVertexComment($meta);
        $meta = $this->addVertexField($meta, ScopeInterface::SCOPE_WEBSITE, $websiteCode);

        return $meta;
    }

    /**
     * Add additional information about Vertex to the customizable options header
     *
     * @param array $meta
     * @return array
     */
    private function addVertexComment(array $meta): array
    {
        $headerCommentPath = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME . '/children/'
            . CustomOptions::CONTAINER_HEADER_NAME
            . '/arguments/data/config/content';

        $comment = $this->arrayManager->get($headerCommentPath, $meta);
        return $this->arrayManager->replace(
            $headerCommentPath,
            $meta,
            $comment . '<p>' . __('Custom Options may only be mapped to Vertex Flexible Fields configured ' .
                'with a source of "Custom Options"') . '</p>'
        );
    }

    /**
     * Add the Vertex Flexible Field select box to the customizable options
     *
     * @param array $meta
     * @param string $scopeType
     * @param string $scopeCode
     * @return array Meta
     */
    private function addVertexField(array $meta, $scopeType, $scopeCode): array
    {
        $taxCalculationOptions = $this->customOptionsSourceFactory
            ->create(['scopeType' => $scopeType, 'scopeCode' => $scopeCode])
            ->toOptionArray();

        $service = [];
        $imports = [];
        $defaultValue = '';
        if ($this->getWebsiteId() !== 0) {
            $service = [
                'template' => 'Magento_Catalog/form/element/helper/custom-option-service',
            ];
            $imports = [
                'isUseDefault' => '${ $.provider }:${ $.parentScope }.vertex_flex_field_is_default',
                'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
            ];
            $defaultValue = 'unmapped';
        }

        return $this->arrayManager->merge(
            CustomOptions::GROUP_CUSTOM_OPTIONS_NAME . '/children/' .
            CustomOptions::GRID_OPTIONS_NAME . '/children/' .
            'record/children/' .
            CustomOptions::CONTAINER_OPTION . '/children/' .
            CustomOptions::CONTAINER_COMMON_NAME . '/children',
            $meta,
            [
                static::FIELD_FLEX_FIELD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Vertex Flexible Field'),
                                'scopeLabel' => '[WEBSITE]',
                                'additionalClasses' => 'admin__field-vertex-scope',
                                'componentType' => Field::NAME,
                                'formElement' => Select::NAME,
                                'component' => 'Vertex_Tax/js/form/element/custom-option-flex-field-select',
                                'dataScope' => static::FIELD_FLEX_FIELD,
                                'dataType' => Text::NAME,
                                'sortOrder' => 35,
                                'filterBy' => [
                                    'target' => '${ $.provider }:${ $.parentScope }.type',
                                    'field' => 'type',
                                ],
                                'imports' => $imports,
                                'service' => $service,
                                'options' => array_merge(
                                    [['value' => $defaultValue, 'label' => __('Unmapped')]],
                                    $taxCalculationOptions
                                ),
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * Retrieve the website id for the currently edited product
     *
     * @return int|null
     */
    private function getWebsiteId()
    {
        try {
            $product = $this->locator->getProduct();
            if (method_exists($product, 'getStore') && $product->getStore() instanceof Store) {
                return (int)$product->getStore()->getWebsiteId();
            }
            return 0;
        } catch (Exception $e) {
            $this->logger->warning($e);
        }
        return null;
    }

    /**
     * Retrieve whether or not Vertex is enabled for the active scope
     *
     * @return bool
     */
    private function isActive(): bool
    {
        return $this->config->isVertexActive($this->getWebsiteId(), ScopeInterface::SCOPE_WEBSITE);
    }
}
