<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request\Type\Order;

use Temando\Shipping\Rest\Request\Type\AttributeFilter;
use Temando\Shipping\Rest\Request\Type\ExtensibleTypeAttribute;
use Temando\Shipping\Rest\Request\Type\ExtensibleTypeInterface;
use Temando\Shipping\Rest\Request\Type\ExtensibleTypeProcessor;

/**
 * The Temando Order Custom Attributes entity
 *
 * @package Temando\Shipping\Rest
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CustomAttributes implements \JsonSerializable, ExtensibleTypeInterface
{
    /**
     * @var string
     */
    private $storeCode;

    /**
     * @var string
     */
    private $customerGroupCode;

    /**
     * @var ExtensibleTypeAttribute[]
     */
    private $additionalAttributes = [];

    /**
     * Custom Attributes constructor.
     * @param string $storeCode
     * @param string $customerGroupCode
     */
    public function __construct(
        $storeCode,
        $customerGroupCode
    ) {
        $this->storeCode = $storeCode;
        $this->customerGroupCode = $customerGroupCode;
    }

    /**
     * Add further dynamic request attributes to the request type.
     *
     * @param ExtensibleTypeAttribute $attribute
     * @return void
     */
    public function addAdditionalAttribute(ExtensibleTypeAttribute $attribute)
    {
        $this->additionalAttributes[$attribute->getAttributeId()] = $attribute;
    }

    /**
     * @return mixed[]|string[][]
     */
    public function jsonSerialize()
    {
        $customAttributes = [
            'storeCode' => $this->storeCode,
            'customerGroupCode' => $this->customerGroupCode
        ];

        foreach ($this->additionalAttributes as $additionalAttribute) {
            $customAttributes = ExtensibleTypeProcessor::addAttribute($customAttributes, $additionalAttribute);
        }
        $customAttributes = AttributeFilter::notEmpty($customAttributes);

        return $customAttributes;
    }
}
