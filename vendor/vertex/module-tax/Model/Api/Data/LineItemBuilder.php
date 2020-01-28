<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Data\LineItemInterfaceFactory;
use Vertex\Exception\ConfigurationException;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Api\Utility\PriceForTax;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

/**
 * Builds a {@see LineItemInterface} for use with the Vertex SDK
 */
class LineItemBuilder
{
    /** @var LineItemInterfaceFactory */
    private $factory;

    /** @var FlexFieldBuilder */
    private $flexFieldBuilder;

    /** @var TaxClassNameRepository */
    private $taxClassNameRepository;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /**
     * @var PriceForTax
     */
    private $priceForTaxCalculation;

    /**
     * @param TaxClassNameRepository $taxClassNameRepository
     * @param LineItemInterfaceFactory $factory
     * @param \Vertex\Tax\Model\Api\Data\FlexFieldBuilder $flexFieldBuilder
     * @param StringUtils $stringUtil
     * @param MapperFactoryProxy $mapperFactory
     * @param PriceForTax $priceForTaxCalculation
     */
    public function __construct(
        TaxClassNameRepository $taxClassNameRepository,
        LineItemInterfaceFactory $factory,
        FlexFieldBuilder $flexFieldBuilder,
        StringUtils $stringUtil,
        MapperFactoryProxy $mapperFactory,
        PriceForTax $priceForTaxCalculation
    ) {
        $this->taxClassNameRepository = $taxClassNameRepository;
        $this->factory = $factory;
        $this->flexFieldBuilder = $flexFieldBuilder;
        $this->stringUtilities = $stringUtil;
        $this->mapperFactory = $mapperFactory;
        $this->priceForTaxCalculation = $priceForTaxCalculation;
    }

    /**
     * Build a {@see LineItemInterface} from a {@see QuoteDetailsItemInterface}
     *
     * @param QuoteDetailsItemInterface $item
     * @param int|null $qtyOverride
     * @param null $scopeCode
     * @return LineItemInterface
     * @throws ConfigurationException
     */
    public function buildFromQuoteDetailsItem(QuoteDetailsItemInterface $item, $qtyOverride = null, $scopeCode = null)
    {
        $lineItem = $this->createLineItem();
        $lineMapper = $this->mapperFactory->getForClass(LineItemInterface::class, $scopeCode);

        $sku = $item->getExtensionAttributes() !== null
            ? $item->getExtensionAttributes()->getVertexProductCode()
            : null;

        if ($sku !== null) {
            $lineItem->setProductCode(
                $this->stringUtilities->substr($sku, 0, $lineMapper->getProductCodeMaxLength())
            );
        }

        $taxClassId = $item->getTaxClassKey() && $item->getTaxClassKey()->getType() === TaxClassKeyInterface::TYPE_ID
            ? $item->getTaxClassKey()->getValue()
            : $item->getTaxClassId();

        $taxClassName = $this->taxClassNameRepository->getById($taxClassId);

        $lineItem->setProductClass(
            $this->stringUtilities->substr($taxClassName, 0, $lineMapper->getProductTaxClassNameMaxLength())
        );

        $quantity = (float)($qtyOverride ?: $item->getQuantity());

        $lineItem->setQuantity($quantity);
        $lineItem->setUnitPrice($item->getUnitPrice());

        $unitPrice = $item->getUnitPrice();
        $priceForTax = $this->priceForTaxCalculation
            ->getPriceForTaxCalculationFromQuoteItem($item, $unitPrice);

        $rowTotal = $priceForTax * $quantity;

        $lineItem->setExtendedPrice($rowTotal - $item->getDiscountAmount());
        $lineItem->setLineItemId($item->getCode());

        $lineItem->setFlexibleFields($this->flexFieldBuilder->buildAllFromQuoteDetailsItem($item));

        return $lineItem;
    }

    /**
     * Create a {@see LineItemInterface}
     *
     * @return LineItemInterface
     */
    private function createLineItem()
    {
        return $this->factory->create();
    }
}
