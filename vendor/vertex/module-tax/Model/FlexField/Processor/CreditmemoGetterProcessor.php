<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All Rights Reserved.
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\ValueExtractor;

/**
 * @inheritDoc
 */
class CreditmemoGetterProcessor implements InvoiceFlexFieldProcessorInterface
{
    const BLOCK_LIST = [
        'getItems',
        'getComments',
        'getExtensionAttributes',
        'getParentId',
        'getEntityId'
    ];
    const DATE_FIELDS = [
        'getCreatedAt',
        'getUpdatedAt'
    ];
    const PREFIX = 'creditmemo';

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var ValueExtractor */
    private $valueExtractor;

    /** @var CreditmemoRepositoryInterface */
    private $creditmemoRepository;

    /**
     * @param AttributeExtractor $attributeExtractor
     * @param ValueExtractor $valueExtractor
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        ValueExtractor $valueExtractor,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->valueExtractor = $valueExtractor;
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        return array_merge(
            $this->attributeExtractor->extract(
                CreditmemoInterface::class,
                static::PREFIX,
                'Creditmemo',
                static::class,
                array_merge(static::DATE_FIELDS, static::BLOCK_LIST)
            ),
            $this->attributeExtractor->extractDateFields(
                static::PREFIX,
                static::DATE_FIELDS,
                'Creditmemo',
                static::class
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
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
     * Retrieve value from Creditmemo attribute
     *
     * Provides a workaround to extract data from the Creditmemo,
     * since object CreditmemoItem received is not yet saved
     * causing `getParentId` and it's `Creditmemo` being unable to retrieve
     *
     * @param CreditmemoItemInterface $item
     * @param $attributeCode
     * @param string|null $fieldType
     * @param string|null $fieldId
     * @return int|string|null
     */
    public function getValueFromCreditmemo(
        CreditmemoItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        try {
            if (method_exists($item, 'getCreditmemo')) {
                /** @var CreditmemoInterface */
                $creditmemo = $item->getCreditmemo();

                if ($creditmemo instanceof CreditmemoInterface) {
                    return $this->valueExtractor->extract(
                        $creditmemo,
                        $attributeCode,
                        static::PREFIX,
                        static::DATE_FIELDS
                    );
                }
            }

            return null;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
