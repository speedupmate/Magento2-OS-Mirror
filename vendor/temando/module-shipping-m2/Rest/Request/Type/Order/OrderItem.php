<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request\Type\Order;

use Temando\Shipping\Rest\Request\Type\AttributeFilter;
use Temando\Shipping\Rest\Request\Type\ExtensibleTypeAttribute;
use Temando\Shipping\Rest\Request\Type\ExtensibleTypeInterface;
use Temando\Shipping\Rest\Request\Type\ExtensibleTypeProcessor;
use Temando\Shipping\Rest\Request\Type\Generic\Dimensions;
use Temando\Shipping\Rest\Request\Type\Generic\MonetaryValue;
use Temando\Shipping\Rest\Request\Type\Generic\Weight;
use Temando\Shipping\Rest\Request\Type\Order\OrderItem\ClassificationCodes;
use Temando\Shipping\Rest\Request\Type\Order\OrderItem\Manufacture;
use Temando\Shipping\Rest\Request\Type\Order\OrderItem\Origin;

/**
 * Temando API Order Item
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderItem implements \JsonSerializable, ExtensibleTypeInterface
{
    // ========== BASICS ========== //

    /**
     * @var int
     */
    private $productId;

    /**
     * @var int
     */
    private $qty;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string[]
     */
    private $categories;

    // ========== SIZE ========== //

    /**
     * The unit of measure that describes the product.
     *
     * Should be a value from Measures
     * @link https://www.ups.com/worldshiphelp/WS12/ENU/AppHelp/Codes/Unit_of_Measure_Codes_for_Invoices.htm.
     * Valid values: [
     *  'Bag',
     *  'Barrel',
     *  'Bolt',
     *  'Box',
     *  'Bunch',
     *  'Bundle',
     *  'Butt',
     *  'Canister',
     *  'Carton',
     *  'Case',
     *  'Centimeter',
     *  'Container',
     *  'Crate',
     *  'Cylinder',
     *  'Dozen',
     *  'Each',
     *  'Envelope',
     *  'Foot',
     *  'Kilogram',
     *  'Kilograms',
     *  'Liter',
     *  'Meter',
     *  'Number',
     *  'Package',
     *  'Packet',
     *  'Pair',
     *  'Pairs',
     *  'Pallet',
     *  'Piece',
     *  'Pieces',
     *  'Pound',
     *  'Proof Liter',
     *  'Roll',
     *  'Set',
     *  'Square Meter',
     *  'Square Yard',
     *  'Tube',
     *  'Yard'
     * ]
     *
     * @var string
     */
    private $unitOfMeasure;

    /**
     * @var Dimensions
     */
    private $dimensions;

    /**
     * @var Weight
     */
    private $weight;

    /**
     * @var MonetaryValue
     */
    private $monetaryValue;

    // ========== HANDLING ========== //

    /**
     * @var bool
     */
    private $isFragile;

    /**
     * @var bool
     */
    private $isVirtual;

    /**
     * @var bool
     */
    private $isPrePackaged;

    /**
     * @var string
     */
    private $packageId;

    /**
     * @var bool
     */
    private $canRotateVertical;

    // ========== EXPORT ========== //

    /**
     * @var Origin
     */
    private $origin;

    /**
     * @var Manufacture
     */
    private $manufacture;

    /**
     * @var ClassificationCodes
     */
    private $classificationCodes;

    /**
     * @var string
     */
    private $composition;

    /**
     * @var array
     */
    private $customAttributes;

    /**
     * @var ExtensibleTypeAttribute[]
     */
    private $additionalAttributes = [];

    /**
     * OrderItem constructor.
     * @param int $productId
     * @param int $qty
     * @param string $sku
     * @param string $name
     * @param string $description
     * @param string[] $categories
     * @param string $unitOfMeasure
     * @param Dimensions $dimensions
     * @param Weight $weight
     * @param MonetaryValue $monetaryValue
     * @param bool $isFragile
     * @param bool $isVirtual
     * @param bool $isPrePackaged
     * @param string $packageId
     * @param bool $canRotateVertical
     * @param Origin $origin
     * @param Manufacture $manufacture
     * @param ClassificationCodes $classificationCodes
     * @param string $composition
     * @param array $customAttributes
     */
    public function __construct(
        $productId,
        $qty,
        $sku,
        $name,
        $description,
        array $categories,
        $unitOfMeasure,
        Dimensions $dimensions,
        Weight $weight,
        MonetaryValue $monetaryValue,
        $isFragile,
        $isVirtual,
        $isPrePackaged,
        $packageId,
        $canRotateVertical,
        Origin $origin,
        Manufacture $manufacture,
        ClassificationCodes $classificationCodes,
        $composition,
        array $customAttributes
    ) {
        $this->productId = $productId;
        $this->qty = $qty;
        $this->sku = $sku;
        $this->name = $name;
        $this->description = $description;
        $this->categories = $categories;
        $this->unitOfMeasure = $unitOfMeasure;
        $this->dimensions = $dimensions;
        $this->weight = $weight;
        $this->monetaryValue = $monetaryValue;
        $this->isFragile = $isFragile;
        $this->isVirtual = $isVirtual;
        $this->isPrePackaged = $isPrePackaged;
        $this->packageId = $packageId;
        $this->canRotateVertical = $canRotateVertical;
        $this->origin = $origin;
        $this->manufacture = $manufacture;
        $this->classificationCodes = $classificationCodes;
        $this->composition = $composition;
        $this->customAttributes = $customAttributes;
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
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        $orderItem = [
            'product' => [
                'sku' => $this->sku,
                'name' => $this->name,
                'description' => $this->description,
                'categories' => $this->categories,
                'unitOfMeasure' => $this->unitOfMeasure,
                'dimensions' => $this->dimensions,
                'weight' => $this->weight,
                'monetaryValue' => $this->monetaryValue,
                'isFragile' => (bool) $this->isFragile,
                'isVirtual' => (bool) $this->isVirtual,
                'isPrePackaged' => (bool) $this->isPrePackaged,
                'packageId' => $this->packageId,
                'canRotateVertical' => $this->canRotateVertical,
                'origin' => $this->origin,
                'manufacture' => $this->manufacture,
                'classificationCodes' => $this->classificationCodes,
                'composition' => $this->composition,
                'customAttributes' => $this->customAttributes,
            ],
            'quantity' => (int) $this->qty,
        ];

        foreach ($this->additionalAttributes as $additionalAttribute) {
            $orderItem = ExtensibleTypeProcessor::addAttribute($orderItem, $additionalAttribute);
        }
        $orderItem = AttributeFilter::notEmpty($orderItem);

        return $orderItem;
    }
}
