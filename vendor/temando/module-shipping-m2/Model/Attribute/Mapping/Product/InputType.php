<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Attribute\Mapping\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Process product attribute mapping input type
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class InputType
{
    const ALLOWED_TYPES = [
        'text',
        'textarea',
        'texteditor',
        'date',
        'boolean',
        'multiselect',
        'select'
    ];

    /**
     * Is the attribute allowed to be mapped.
     *
     * @param ProductAttributeInterface $attribute
     * @return bool
     */
    public function isAllowed(ProductAttributeInterface $attribute): bool
    {
        return in_array($attribute->getFrontendInput(), self::ALLOWED_TYPES);
    }
}
