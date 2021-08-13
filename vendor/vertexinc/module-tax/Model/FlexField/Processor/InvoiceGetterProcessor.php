<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All Rights Reserved.
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\ValueExtractor;

/**
 * @inheritDoc
 */
class InvoiceGetterProcessor implements InvoiceFlexFieldProcessorInterface
{
    const BLOCK_LIST = [
        'getExtensionAttributes',
        'getItems',
        'getComments'
    ];
    const DATE_FIELDS = [
        'getCreatedAt',
        'getUpdatedAt'
    ];
    const PREFIX = 'invoice';

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var ValueExtractor */
    private $valueExtractor;

    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    /**
     * @param AttributeExtractor $attributeExtractor
     * @param ValueExtractor $valueExtractor
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        ValueExtractor $valueExtractor,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->valueExtractor = $valueExtractor;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        return array_merge(
            $this->attributeExtractor->extract(
                InvoiceInterface::class,
                static::PREFIX,
                'Invoice',
                static::class,
                array_merge(static::DATE_FIELDS, static::BLOCK_LIST)
            ),
            $this->attributeExtractor->extractDateFields(
                static::PREFIX,
                static::DATE_FIELDS,
                'Invoice',
                static::class
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getValueFromCreditmemo(
        CreditmemoItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return null;
    }

    /**
     * Retrieves attribute value for the given Invoice Item
     * @param InvoiceItemInterface $item
     * @param $attributeCode
     * @param string|null $fieldType
     * @param string|null $fieldId
     * @return int|string|void|null
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        try {
            $invoice = $this->invoiceRepository->get($item->getParentId());
            return $this->valueExtractor->extract($invoice, $attributeCode, static::PREFIX, static::DATE_FIELDS);
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }
}
