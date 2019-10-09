<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\FlexField;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\ScopeInterface;
use Vertex\Tax\Model\Config;

/**
 * Provides the source for the flexible field mapping on a Product's Custom Options in the admin panel
 */
class CustomOptionsSource implements OptionSourceInterface
{
    const TYPE_NAMES = [
        FlexFieldProcessableAttribute::TYPE_NUMERIC => 'Numeric',
        FlexFieldProcessableAttribute::TYPE_DATE => 'Date',
        FlexFieldProcessableAttribute::TYPE_CODE => 'Code',
    ];

    /** @var Config */
    private $config;

    /** @var string|null */
    private $scopeCode;

    /** @var string|null */
    private $scopeType;

    /**
     * @param Config $config
     * @param string|null $scopeCode
     * @param string $scopeType
     */
    public function __construct(
        Config $config,
        $scopeCode = null,
        $scopeType =
        ScopeInterface::SCOPE_WEBSITE
    ) {
        $this->config = $config;
        $this->scopeCode = $scopeCode;
        $this->scopeType = $scopeType;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $fields = $this->config->getFlexFieldsList($this->scopeCode, $this->scopeType);

        /**
         * Custom Option types that can be mapped to a "code" flex field type
         *
         * @var string[]
         */
        $codeFields = [
            'field',
            'area',
            'drop_down',
            'radio',
            'date',
            'date_time',
            'time'
        ];

        /**
         * Custom Option types that can be mapped to a "date" flex field type
         *
         * @var string[]
         */
        $dateFields = [
            'date'
        ];

        /**
         * Provides the correct array based on the $pieces[1] variable
         *
         * @var array[]
         */
        $indexedType = [
            'code' => $codeFields,
            'date' => $dateFields,
            'numeric' => [],
        ];

        $availableFlexFields = [];
        foreach ($fields as $field) {
            $attributeCode = $field['field_source'];
            $fieldId = $field['field_id'];

            $pieces = explode('.', $attributeCode);
            if ($pieces[0] !== 'product_custom_option' || !isset($pieces[1])) {
                continue;
            }

            $availableFlexFields[] = [
                'value' => $pieces[1] . '.' . $fieldId,
                'label' => __(static::TYPE_NAMES[$pieces[1]] . ' Field #%1', $fieldId),
                'type' => $indexedType[$pieces[1]]
            ];
        }

        return $availableFlexFields;
    }
}
