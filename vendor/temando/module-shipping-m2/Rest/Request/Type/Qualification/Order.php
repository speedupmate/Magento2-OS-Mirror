<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request\Type\Qualification;

use Temando\Shipping\Rest\Request\Type\ExtensibleTypeAttribute;
use Temando\Shipping\Rest\Request\Type\ExtensibleTypeProcessor;
use Temando\Shipping\Rest\Request\Type\Generic\MonetaryValue;
use Temando\Shipping\Rest\Request\Type\Order\CustomAttributes;
use Temando\Shipping\Rest\Request\Type\Order\Customer;
use Temando\Shipping\Rest\Request\Type\Order\OrderItem;
use Temando\Shipping\Rest\Request\Type\Order\Recipient;

/**
 * Temando API Qualification Order Request Type
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Order implements \JsonSerializable
{
    /**
     * @var string
     */
    private $createdAt;

    /**
     * @var string
     */
    private $lastModifiedAt;

    /**
     * @var string
     */
    private $sourceName;

    /**
     * @var string
     */
    private $sourceReference;

    /**
     * Billing address
     *
     * @var Customer
     */
    private $customer;

    /**
     * Shipping address
     *
     * @var Recipient
     */
    private $recipient;

    /**
     * @var OrderItem[]
     */
    private $items;

    /**
     * @var MonetaryValue
     */
    private $total;

    /**
     * @var CustomAttributes
     */
    private $customAttributes;

    /**
     * @var ExtensibleTypeAttribute[]
     */
    private $additionalAttributes = [];

    /**
     * Order constructor.
     * @param string $createdAt
     * @param string $lastModifiedAt
     * @param string $sourceName
     * @param string $sourceReference
     * @param MonetaryValue $total
     * @param Customer $customer
     * @param Recipient $recipient
     * @param OrderItem[] $items
     * @param CustomAttributes $customAttributes
     */
    public function __construct(
        $createdAt,
        $lastModifiedAt,
        $sourceName,
        $sourceReference,
        MonetaryValue $total,
        Customer $customer,
        Recipient $recipient,
        array $items,
        $customAttributes
    ) {
        $this->createdAt = $createdAt;
        $this->lastModifiedAt = $lastModifiedAt;
        $this->sourceName = $sourceName;
        $this->sourceReference = $sourceReference;
        $this->total = $total;
        $this->customer = $customer;
        $this->recipient = $recipient;
        $this->items = $items;
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
        $order = [
            'type' => 'order',
            'attributes' => [
                'createdAt' => $this->createdAt,
                'lastModifiedAt' => $this->lastModifiedAt,
                'source' => [
                    'name' => $this->sourceName,
                    'reference' => $this->sourceReference,
                ],
                'customer' => $this->customer,
                'deliverTo' => $this->recipient,
                'items' => $this->items,
                'total' => $this->total,
                'customAttributes' => $this->customAttributes,
            ],
        ];

        foreach ($this->additionalAttributes as $additionalAttribute) {
            $order = ExtensibleTypeProcessor::addAttribute($order, $additionalAttribute);
        }

        return $order;
    }
}
