<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Setup;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Filesystem\Driver\File as Filesystem;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Temando\Shipping\Model\Attribute\Mapping\ProductInterface as ProductAttributeMappingInterface;
use Temando\Shipping\Model\Source\Packaging;
use Temando\Shipping\Model\Source\PackagingType;
use Temando\Shipping\Model\ResourceModel\Product\Attribute\Source\Country;

/**
 * Data setup for use during installation / upgrade
 *
 * @package Temando\Shipping\Setup
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class SetupData
{
    const ATTRIBUTE_CODE_LENGTH = 'ts_dimensions_length';
    const ATTRIBUTE_CODE_WIDTH = 'ts_dimensions_width';
    const ATTRIBUTE_CODE_HEIGHT = 'ts_dimensions_height';
    const ATTRIBUTE_CODE_PACKAGING_TYPE = 'ts_packaging_type';
    const ATTRIBUTE_CODE_PACKAGING_ID = 'ts_packaging_id';
    const PICKUP_ORDER_TEMPLATE = 'order_pickup_new.html';
    const PICKUP_ORDER_GUEST_TEMPLATE = 'order_pickup_new_guest.html';
    const ATTRIBUTE_CODE_HS_CODE = 'ts_hs_code';
    const ATTRIBUTE_CODE_COUNTRY_OF_ORIGIN = 'ts_country_of_origin';

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Template factory
     *
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var TemplateResource
     */
    private $templateResource;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var Filesystem
     */
    private $fileSystemDriver;

    /**
     * SetupData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param TemplateFactory $templateFactory
     * @param TemplateResource $templateResource
     * @param Reader $moduleReader
     * @param Filesystem $fileSystemDriver
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        TemplateFactory $templateFactory,
        TemplateResource $templateResource,
        Reader $moduleReader,
        Filesystem $fileSystemDriver
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->templateFactory = $templateFactory;
        $this->templateResource = $templateResource;
        $this->moduleReader = $moduleReader;
        $this->fileSystemDriver = $fileSystemDriver;
    }

    /**
     * Get email template directory.
     *
     * @return string
     */
    private function getDirectory()
    {
        $viewDir = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
            'Temando_Shipping'
        );

        return $viewDir . '/frontend/email/';
    }

    /**
     * Get pickup order email contents for registered orders.
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getEmailTemplate()
    {
        $viewDir = $this->getDirectory();
        $templateContent = $this->fileSystemDriver->fileGetContents($viewDir . self::PICKUP_ORDER_TEMPLATE);

        return $templateContent;
    }

    /**
     * Get pickup order email contents for guest orders.
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getEmailTemplateForGuest()
    {
        $viewDir = $this->getDirectory();
        $templateContent = $this->fileSystemDriver->fileGetContents($viewDir . self::PICKUP_ORDER_GUEST_TEMPLATE);

        return $templateContent;
    }

    /**
     * Add dimension attributes. Need to be editable on store level due to the
     * weight unit (that dimensions unit is derived from) is configurable on
     * store level.
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function addDimensionAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE_LENGTH, [
            'type' => 'decimal',
            'label' => 'Item Length',
            'input' => 'text',
            'required' => false,
            'class' => 'not-negative-amount',
            'sort_order' => 65,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'user_defined' => false,
            'apply_to' => Type::TYPE_SIMPLE
        ]);

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE_WIDTH, [
            'type' => 'decimal',
            'label' => 'Item Width',
            'input' => 'text',
            'required' => false,
            'class' => 'not-negative-amount',
            'sort_order' => 66,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'user_defined' => false,
            'apply_to' => Type::TYPE_SIMPLE
        ]);

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE_HEIGHT, [
            'type' => 'decimal',
            'label' => 'Item Height',
            'input' => 'text',
            'required' => false,
            'class' => 'not-negative-amount',
            'sort_order' => 67,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'user_defined' => false,
            'apply_to' => Type::TYPE_SIMPLE
        ]);
    }

    /**
     * Updates Shipping Dimension Attributes to Configurable Product Types
     *
     * @param ModuleDataSetupInterface $setup
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function updateDimensionAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->updateAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE_LENGTH,
            'apply_to',
            implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE])
        );

        $eavSetup->updateAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE_WIDTH,
            'apply_to',
            implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE])
        );

        $eavSetup->updateAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE_HEIGHT,
            'apply_to',
            implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE])
        );
    }

    /**
     * Add new Pickup Order Email Template to DB.
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function addPickupOrderEmailTemplate()
    {
        $template = $this->templateFactory->create();
        $template->setTemplateCode('New Pickup Order');
        $template->setTemplateText($this->getEmailTemplate());
        $template->setTemplateType(TemplateTypesInterface::TYPE_HTML);
        $template->setTemplateSubject(
            '{{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}}'
        );
        $template->setOrigTemplateCode('sales_email_order_template');
        // @codingStandardsIgnoreLine
        $template->setOrigTemplateVariables('{"var formattedBillingAddress|raw":"Billing Address","var order.getEmailCustomerNote()":"Email Order Note","var order.increment_id":"Order Id","layout handle=\"sales_email_order_items\" order=$order area=\"frontend\"":"Order Items Grid","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var shipping_msg":"Shipping message"}');

        $this->templateResource->save($template);
    }

    /**
     * Add New Order Pickup Email Template.
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function addPickupOrderGuestEmailTemplate()
    {
        $template = $this->templateFactory->create();
        $template->setTemplateCode('New Pickup Order For Guest');
        $template->setTemplateText($this->getEmailTemplateForGuest());
        $template->setTemplateType(TemplateTypesInterface::TYPE_HTML);
        $template->setTemplateSubject(
            '{{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}}'
        );
        $template->setOrigTemplateCode('sales_email_order_guest_template');
        // @codingStandardsIgnoreLine
        $template->setOrigTemplateVariables('{"var formattedBillingAddress|raw":"Billing Address","var order.getEmailCustomerNote()":"Email Order Note","var order.getBillingAddress().getName()":"Guest Customer Name","var order.getCreatedAtFormatted(2)":"Order Created At (datetime)","var order.increment_id":"Order Id","layout handle=\"sales_email_order_items\" order=$order":"Order Items Grid","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var shipping_msg":"Shipping message"}');

        $this->templateResource->save($template);
    }

    /**
     * Add packaging attributes.
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function addPackagingAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE_PACKAGING_TYPE, [
            'type' => 'varchar',
            'label' => 'Packaging Type',
            'input' => 'select',
            'source' => PackagingType::class,
            'required' => false,
            'sort_order' => 70,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'user_defined' => false,
            'apply_to' =>  implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE]),
        ]);

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE_PACKAGING_ID, [
            'type' => 'varchar',
            'label' => 'Packaging Name',
            'input' => 'select',
            'source' => Packaging::class,
            'required' => false,
            'sort_order' => 71,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'user_defined' => false,
            'apply_to' =>  implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE]),
        ]);
    }

    /**
     * Create the international shipping product attributes.
     *
     * @param ModuleDataSetupInterface $setup
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function addInternationalShippingProductAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE_HS_CODE, [
            'type' => 'varchar',
            'label' => 'HS Code',
            'input' => 'text',
            'required' => false,
            'sort_order' => 100,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'user_defined' => false,
            'apply_to' =>  implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE])
        ]);

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE_COUNTRY_OF_ORIGIN, [
            'type' => 'varchar',
            'label' => 'Country of Origin',
            'input' => 'select',
            'required' => false,
            'sort_order' => 101,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'user_defined' => false,
            'apply_to' =>  implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE]),
            'source' => Country::class
        ]);
    }

    /**
     * Populate Product Attribute Mapping Table with data.
     *
     * @param ModuleDataSetupInterface $setup
     */
    public function addMappedProductAttributes(ModuleDataSetupInterface $setup)
    {
        /**
         * Prepare database for install
         */
        $setup->startSetup();
        $table = $setup->getTable(SetupSchema::TABLE_PRODUCT_ATTRIBUTE_MAPPING);

        $columns = [
            ProductAttributeMappingInterface::NODE_PATH_ID,
            ProductAttributeMappingInterface::LABEL,
            ProductAttributeMappingInterface::DESCRIPTION,
            ProductAttributeMappingInterface::MAPPED_ATTRIBUTE_ID,
            ProductAttributeMappingInterface::IS_DEFAULT
        ];

        $data = [
            [
                'origin.address.countryCode',
                'Country of origin',
                'Country of origin. Field Validation: Country as a valid ISO 3166-1 alpha-2 country code.',
                self::ATTRIBUTE_CODE_COUNTRY_OF_ORIGIN,
                true
            ],
            [
                'manufacture.address.countryCode',
                'Country of manufacture',
                'Country of manufacture. Field Validation: Country as a valid ISO 3166-1 alpha-2 country code.',
                null,
                true
            ],
            [
                'classificationCodes.eccn',
                'ECCN',
                'Export Control Classification Number. Field Validation: 5 maximum characters.',
                null,
                true
            ],
            [
                'classificationCodes.scheduleBInfo',
                'Schedule B Info',
                'Code for exporting goods out of the United States. Field Validation: 15 maximum characters.',
                null,
                true
            ],
            [
                'classificationCodes.hsCode',
                'HS Code',
                'Harmonized Commodity Description and Coding System. Field Validation: Between 2 and 5 Characters.',
                self::ATTRIBUTE_CODE_HS_CODE,
                true
            ],
            [
                'composition',
                'Composition',
                'Materials product is composed of',
                null,
                true
            ]
        ];

        $setup->getConnection()->insertArray($table, $columns, $data);

        /**
         * Prepare database after install
         */
        $setup->endSetup();
    }
}
