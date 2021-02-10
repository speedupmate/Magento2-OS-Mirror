<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Plugin;

use Closure;
use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Vertex\Tax\Model\Calculator;
use Vertex\Tax\Model\VertexUsageDeterminer;

/**
 * Handle tax calculation through Vertex
 */
class TaxCalculationPlugin
{
    /** @var Calculator */
    private $calculator;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var VertexUsageDeterminer */
    private $usageDeterminer;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Calculator $calculator
     * @param VertexUsageDeterminer $usageDeterminer
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Calculator $calculator,
        VertexUsageDeterminer $usageDeterminer
    ) {
        $this->storeManager = $storeManager;
        $this->calculator = $calculator;
        $this->usageDeterminer = $usageDeterminer;
    }

    /**
     * Use Vertex to calculate tax if it can be used
     *
     * @see TaxCalculationInterface::calculateTax()
     * @param TaxCalculationInterface $subject
     * @param Closure $super
     * @param QuoteDetailsInterface $quoteDetails
     * @param string|null $storeId
     * @param bool $round
     * @return TaxDetailsInterface
     * @throws NoSuchEntityException
     * @throws InvalidArgumentException
     */
    public function aroundCalculateTax(
        TaxCalculationInterface $subject,
        Closure $super,
        QuoteDetailsInterface $quoteDetails,
        $storeId = null,
        $round = true
    ) {
        $storeId = $this->getStoreId($storeId);
        if (!$this->useVertex($quoteDetails, $storeId, true)) {
            return $super($quoteDetails, $storeId, $round);
        }

        return $this->calculator->calculateTax($quoteDetails, $storeId, (bool)$round);
    }

    /**
     * Retrieve current Store ID
     *
     * @param string|null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getStoreId($storeId)
    {
        return $storeId ?: $this->storeManager->getStore()->getStoreId();
    }

    /**
     * Determine whether or not to use Vertex
     *
     * We make this determination based on the UsageDeterminer result as well as whether or not any items on the
     * quote actually have a price.
     *
     * @param QuoteDetailsInterface $quoteDetails
     * @param string|null $storeId
     * @param bool $checkCalculation
     * @return bool
     */
    private function useVertex(QuoteDetailsInterface $quoteDetails, $storeId, $checkCalculation = false)
    {
        return $this->usageDeterminer->shouldUseVertex(
            $storeId,
            $quoteDetails->getShippingAddress(),
            $quoteDetails->getCustomerId() === null ? null : (int)$quoteDetails->getCustomerId(),
            $checkCalculation
        );
    }
}
