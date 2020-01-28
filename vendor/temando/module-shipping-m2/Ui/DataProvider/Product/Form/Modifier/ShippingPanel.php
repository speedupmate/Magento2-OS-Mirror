<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Fieldset;
use Temando\Shipping\Model\Config\ModuleConfigInterface;
use Temando\Shipping\Model\Source\PackagingType;
use Temando\Shipping\Setup\SetupData;

/**
 * Product Form Shipping Fieldset Modifier
 *
 * @package Temando\Shipping\Ui
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShippingPanel extends AbstractModifier
{
    const SHIPPING_PANEL = 'ts_shipping';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * ShippingPanel constructor.
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param ModuleConfigInterface $moduleConfig
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        ModuleConfigInterface $moduleConfig
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Add a new fieldset configuration.
     *
     * @param mixed[] $meta
     * @return mixed[]
     */
    private function addShippingPanel(array $meta): array
    {
        $groupMeta = [
            self::SHIPPING_PANEL => [
                'children' => [],
                'arguments'=> [
                    'data' => [
                        'config' => [
                            'label' => __('Shipping'),
                            'collapsible' => true,
                            'componentType' => Fieldset::NAME,
                            'dataScope' => self::DATA_SCOPE_PRODUCT,
                            'sortOrder' => $this->getNextGroupSortOrder(
                                $meta,
                                'gift-options',
                                90
                            ),
                        ],
                    ]
                ],
            ],
        ];

        $meta = array_merge($meta, $groupMeta);
        return $meta;
    }

    /**
     * Extend field properties.
     *
     * - Display configured unit of measure in form input field.
     *
     * @param mixed[] $meta
     * @return mixed[]
     */
    private function addDimensionsUom(array $meta): array
    {
        $attributeCodes = [
            10 => SetupData::ATTRIBUTE_CODE_LENGTH,
            20 => SetupData::ATTRIBUTE_CODE_WIDTH,
            30 => SetupData::ATTRIBUTE_CODE_HEIGHT,
        ];

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->locator->getStore();
        $weightUnit = $store->getConfig(DirectoryHelper::XML_PATH_WEIGHT_UNIT);
        $attributeMeta = [
            'validation' => [
                'validate-zero-or-greater' => true
            ],
            'additionalClasses' => 'admin__field-small',
            'addafter' => ($weightUnit === 'kgs') ? 'cm' : 'in',
        ];

        foreach ($attributeCodes as $attributeCode) {
            // update attribute
            $path = $this->arrayManager->findPath($attributeCode, $meta, null, 'children');
            $configPath = $path . static::META_CONFIG_PATH;

            $meta = $this->arrayManager->merge($configPath, $meta, $attributeMeta);
        }

        return $meta;
    }

    /**
     * Extend field properties.
     *
     * - Toggle the "packaging" visibility based on the "packaging type" selection.
     * - Toggle validation of width, length, height, weight based on the "packaging type" selection.
     *
     * @param mixed[] $meta
     * @return mixed[]
     */
    private function addPackagingTypeDependencies(array $meta): array
    {
        $packagingCode = SetupData::ATTRIBUTE_CODE_PACKAGING_ID;
        $widthCode = SetupData::ATTRIBUTE_CODE_WIDTH;
        $lengthCode = SetupData::ATTRIBUTE_CODE_LENGTH;
        $heightCode = SetupData::ATTRIBUTE_CODE_HEIGHT;
        $weightCode = ProductAttributeInterface::CODE_WEIGHT;

        $shippingPanel = implode('.', ['product_form', 'product_form', static::SHIPPING_PANEL]);
        $generalPanel = implode('.', ['product_form', 'product_form', static::DEFAULT_GENERAL_PANEL]);

        $packaging = sprintf('%s.%s%s.%s', $shippingPanel, static::CONTAINER_PREFIX, $packagingCode, $packagingCode);
        $width = sprintf('%s.%s%s.%s', $shippingPanel, static::CONTAINER_PREFIX, $widthCode, $widthCode);
        $length = sprintf('%s.%s%s.%s', $shippingPanel, static::CONTAINER_PREFIX, $lengthCode, $lengthCode);
        $height = sprintf('%s.%s%s.%s', $shippingPanel, static::CONTAINER_PREFIX, $heightCode, $heightCode);
        $weight = sprintf('%s.%s%s.%s', $generalPanel, static::CONTAINER_PREFIX, $weightCode, $weightCode);

        // define actions to be performed when packaging type changes.
        // NOTE: this is lengthy due to a core bug. once resolved, "required" and "validate" callbacks can be removed.
        $switcherConfig = [
            'enabled' => true,
            'rules' => [
                [
                    'value' => PackagingType::PACKAGING_TYPE_NONE,
                    'actions' => [
                        [
                            'target' => $packaging,
                            'callback' => 'hide',
                        ],
                        [
                            'target' => $packaging,
                            'callback' => 'clear',
                        ],
                        [
                            'target' => $width,
                            'callback' => 'hide',
                        ],
                        [
                            'target' => $width,
                            'callback' => 'clear',
                        ],
                        [
                            'target' => $width,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', false],
                        ],
                        [
                            'target' => $width,
                            'callback' => 'required',
                            'params' => [false],
                        ],
                        [
                            'target' => $width,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $length,
                            'callback' => 'hide',
                        ],
                        [
                            'target' => $length,
                            'callback' => 'clear',
                        ],
                        [
                            'target' => $length,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', false],
                        ],
                        [
                            'target' => $length,
                            'callback' => 'required',
                            'params' => [false],
                        ],
                        [
                            'target' => $length,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $height,
                            'callback' => 'hide',
                        ],
                        [
                            'target' => $height,
                            'callback' => 'clear',
                        ],
                        [
                            'target' => $height,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', false],
                        ],
                        [
                            'target' => $height,
                            'callback' => 'required',
                            'params' => [false],
                        ],
                        [
                            'target' => $height,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $weight,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', false],
                        ],
                        [
                            'target' => $weight,
                            'callback' => 'required',
                            'params' => [false],
                        ],
                        [
                            'target' => $weight,
                            'callback' => 'validate',
                        ],
                    ],
                ],
                [
                    'value' => PackagingType::PACKAGING_TYPE_PACKED,
                    'actions' => [
                        [
                            'target' => $packaging,
                            'callback' => 'hide',
                        ],
                        [
                            'target' => $packaging,
                            'callback' => 'clear',
                        ],
                        [
                            'target' => $width,
                            'callback' => 'show',
                        ],
                        [
                            'target' => $width,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', true],
                        ],
                        [
                            'target' => $width,
                            'callback' => 'required',
                            'params' => [true],
                        ],
                        [
                            'target' => $width,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $length,
                            'callback' => 'show',
                        ],
                        [
                            'target' => $length,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', true],
                        ],
                        [
                            'target' => $length,
                            'callback' => 'required',
                            'params' => [true],
                        ],
                        [
                            'target' => $length,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $height,
                            'callback' => 'show',
                        ],
                        [
                            'target' => $height,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', true],
                        ],
                        [
                            'target' => $height,
                            'callback' => 'required',
                            'params' => [true],
                        ],
                        [
                            'target' => $height,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $weight,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', true],
                        ],
                        [
                            'target' => $weight,
                            'callback' => 'required',
                            'params' => [true],
                        ],
                        [
                            'target' => $weight,
                            'callback' => 'validate',
                        ],
                    ],
                ],
                [
                    'value' => PackagingType::PACKAGING_TYPE_ASSIGNED,
                    'actions' => [
                        [
                            'target' => $packaging,
                            'callback' => 'show',
                        ],
                        [
                            'target' => $packaging,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', true],
                        ],
                        [
                            'target' => $packaging,
                            'callback' => 'required',
                            'params' => [true],
                        ],
                        [
                            'target' => $packaging,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $width,
                            'callback' => 'hide',
                        ],
                        [
                            'target' => $width,
                            'callback' => 'clear',
                        ],
                        [
                            'target' => $width,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', false],
                        ],
                        [
                            'target' => $width,
                            'callback' => 'required',
                            'params' => [false],
                        ],
                        [
                            'target' => $width,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $length,
                            'callback' => 'hide',
                        ],
                        [
                            'target' => $length,
                            'callback' => 'clear',
                        ],
                        [
                            'target' => $length,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', false],
                        ],
                        [
                            'target' => $length,
                            'callback' => 'required',
                            'params' => [false],
                        ],
                        [
                            'target' => $length,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $height,
                            'callback' => 'hide',
                        ],
                        [
                            'target' => $height,
                            'callback' => 'clear',
                        ],
                        [
                            'target' => $height,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', false],
                        ],
                        [
                            'target' => $height,
                            'callback' => 'required',
                            'params' => [false],
                        ],
                        [
                            'target' => $height,
                            'callback' => 'validate',
                        ],
                        [
                            'target' => $weight,
                            'callback' => 'setValidation',
                            'params' => ['required-entry', false],
                        ],
                        [
                            'target' => $weight,
                            'callback' => 'required',
                            'params' => [false],
                        ],
                        [
                            'target' => $weight,
                            'callback' => 'validate',
                        ],
                    ],
                ],
            ],
        ];

        $path = $this->arrayManager->findPath(SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE, $meta, null, 'children');
        $configPath = $path . static::META_CONFIG_PATH;

        $meta = $this->arrayManager->merge($configPath, $meta, ['switcherConfig' => $switcherConfig]);

        return $meta;
    }

    /**
     * Move product attributes from default fieldset to shipping fieldset.
     *
     * - Set sort order.
     * - Hide packaging attributes if API connection is not set up.
     *
     * @param mixed[] $meta
     * @return mixed[]
     */
    private function moveAttributes(array $meta): array
    {
        $attributeCodes = [
            2 => SetupData::ATTRIBUTE_CODE_COUNTRY_OF_ORIGIN,
            6 => SetupData::ATTRIBUTE_CODE_HS_CODE,
            10 => SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE,
            20 => SetupData::ATTRIBUTE_CODE_LENGTH,
            30 => SetupData::ATTRIBUTE_CODE_WIDTH,
            40 => SetupData::ATTRIBUTE_CODE_HEIGHT,
            50 => SetupData::ATTRIBUTE_CODE_PACKAGING_ID
        ];

        foreach ($attributeCodes as $sortOrder => $attributeCode) {
            // update attribute container
            $containerMeta = ['sortOrder' => $sortOrder];

            $containerName = static::CONTAINER_PREFIX . $attributeCode;
            $path = $this->arrayManager->findPath($containerName, $meta);
            $configPath = $path . static::META_CONFIG_PATH;
            $meta = $this->arrayManager->merge($configPath, $meta, $containerMeta);

            // update attribute
            $visible = in_array(
                $attributeCode,
                [
                    SetupData::ATTRIBUTE_CODE_LENGTH,
                    SetupData::ATTRIBUTE_CODE_WIDTH,
                    SetupData::ATTRIBUTE_CODE_HEIGHT
                ]
            );
            $attributeMeta = [
                'source' => self::SHIPPING_PANEL,
                'sortOrder' => $sortOrder,
                'dataScope' => $attributeCode,
                'visible' => $visible || $this->moduleConfig->isRegistered()
            ];

            $path = $this->arrayManager->findPath($attributeCode, $meta, null, 'children');
            $configPath = $path . static::META_CONFIG_PATH;
            $meta = $this->arrayManager->merge($configPath, $meta, $attributeMeta);

            // move attribute
            $containerName = static::CONTAINER_PREFIX . $attributeCode;
            $path = $this->arrayManager->findPath($containerName, $meta);
            $targetPath = str_replace(self::DEFAULT_GENERAL_PANEL, self::SHIPPING_PANEL, $path);
            $meta = $this->arrayManager->move($path, $targetPath, $meta);
        }

        return $meta;
    }

    /**
     * Extend product attributes' meta data.
     *
     * @param mixed[] $meta
     * @return mixed[]
     */
    public function modifyMeta(array $meta): array
    {
        $meta = $this->addShippingPanel($meta);
        $meta = $this->addDimensionsUom($meta);
        $meta = $this->addPackagingTypeDependencies($meta);
        $meta = $this->moveAttributes($meta);

        return $meta;
    }

    /**
     * Modify form data.
     *
     * @param mixed[] $data
     * @return mixed[]
     */
    public function modifyData(array $data): array
    {
        return $data;
    }
}
