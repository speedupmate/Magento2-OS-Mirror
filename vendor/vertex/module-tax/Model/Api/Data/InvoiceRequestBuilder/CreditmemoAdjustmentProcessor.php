<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Data\LineItemInterfaceFactory;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

/**
 * Processes positive and negative adjustments added to a creditmemo
 */
class CreditmemoAdjustmentProcessor implements CreditmemoProcessorInterface
{
    /** @var TaxClassNameRepository */
    private $classNameRepository;

    /** @var Config */
    private $config;

    /** @var LineItemInterfaceFactory */
    private $lineItemFactory;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /**
     * @param LineItemInterfaceFactory $lineItemFactory
     * @param Config $config
     * @param TaxClassNameRepository $classNameRepository
     * @param StringUtils $stringUtils
     * @param MapperFactoryProxy $mapperFactory
     */
    public function __construct(
        LineItemInterfaceFactory $lineItemFactory,
        Config $config,
        TaxClassNameRepository $classNameRepository,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory
    ) {
        $this->lineItemFactory = $lineItemFactory;
        $this->config = $config;
        $this->classNameRepository = $classNameRepository;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * @inheritdoc
     */
    public function process(RequestInterface $request, CreditmemoInterface $creditmemo)
    {
        $lineItems = $request->getLineItems();

        $adjustmentPositive = $creditmemo->getBaseAdjustmentPositive(); // additional refund
        $adjustmentNegative = $creditmemo->getBaseAdjustmentNegative(); // fee

        $storeCode = $creditmemo->getstoreId();
        $lineItemMapper = $this->mapperFactory->getForClass(LineItemInterface::class, $storeCode);

        if ($adjustmentPositive > 0) {
            $lineItem = $this->lineItemFactory->create();
            $lineItem->setUnitPrice(-1 * $adjustmentPositive);
            $lineItem->setExtendedPrice(-1 * $adjustmentPositive);
            $lineItem->setQuantity(1);
            $lineItem->setProductCode(
                $this->stringUtilities->substr(
                    $this->config->getCreditmemoAdjustmentPositiveCode($storeCode),
                    0,
                    $lineItemMapper->getProductCodeMaxLength()
                )
            );
            $lineItem->setProductClass(
                $this->stringUtilities->substr(
                    $this->classNameRepository->getById(
                        $this->config->getCreditmemoAdjustmentPositiveClass($storeCode)
                    ),
                    0,
                    $lineItemMapper->getProductTaxClassNameMaxLength()
                )
            );

            $lineItems[] = $lineItem;
        }

        if ($adjustmentNegative > 0) {
            $lineItem = $this->lineItemFactory->create();
            $lineItem->setUnitPrice($adjustmentNegative);
            $lineItem->setExtendedPrice($adjustmentNegative);
            $lineItem->setQuantity(1);
            $lineItem->setProductCode(
                $this->stringUtilities->substr(
                    $this->config->getCreditmemoAdjustmentFeeCode($storeCode),
                    0,
                    $lineItemMapper->getProductCodeMaxLength()
                )
            );
            $lineItem->setProductClass(
                $this->stringUtilities->substr(
                    $this->classNameRepository->getById(
                        $this->config->getCreditmemoAdjustmentFeeClass($storeCode)
                    ),
                    0,
                    $lineItemMapper->getProductTaxClassNameMaxLength()
                )
            );

            $lineItems[] = $lineItem;
        }

        $request->setLineItems($lineItems);
        return $request;
    }
}
