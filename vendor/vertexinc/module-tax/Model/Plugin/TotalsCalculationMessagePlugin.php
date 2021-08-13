<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\Data\TotalSegmentInterface;
use Magento\Quote\Model\Cart\TotalsConverter;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Vertex\Tax\Model\Calculator;
use Vertex\Tax\Model\Config;

/**
 * Add extension attribute 'vertex-messages' containing all vertex message from calculation
 *
 * @see TotalsConverter
 */
class TotalsCalculationMessagePlugin
{
    /** @var Config */
    private $config;

    /** @var LoggerInterface */
    private $logger;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        ManagerInterface $messageManager,
        Config $config,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Add any Vertex error messages to the tax totals data
     *
     * @see TotalsConverter::process()
     * @param TotalsConverter $subject
     * @param callable $super
     * @param Total[] $addressTotals
     * @return TotalSegmentInterface[]
     */
    public function aroundProcess(
        TotalsConverter $subject,
        callable $super,
        $addressTotals = []
    ) {
        // Allows forward compatibility with argument additions
        $arguments = func_get_args();
        array_splice($arguments, 0, 2);

        /** @var TotalSegmentInterface[] $totalSegment */
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $totalSegments = call_user_func_array($super, $arguments);

        $storeId = null;
        try {
            if ($currentStore = $this->storeManager->getStore()) {
                $storeId = $currentStore->getId();
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }

        if (!$this->config->isVertexActive($storeId) || !$this->config->isTaxCalculationEnabled($storeId)) {
            return $totalSegments;
        }

        if (!array_key_exists('tax', $addressTotals)) {
            return $totalSegments;
        }

        $taxes = $addressTotals['tax']->getData();
        if (!array_key_exists('full_info', $taxes)) {
            return $totalSegments;
        }

        $messageCollection = $this->messageManager->getMessages(true, Calculator::MESSAGE_KEY);
        if (!$messageCollection->getCount()) {
            return $totalSegments;
        }

        $attributes = $totalSegments['tax']->getExtensionAttributes();

        $attributes->setVertexTaxCalculationMessages(
            array_map(
                function (MessageInterface $message) {
                    return $message->getText();
                },
                $messageCollection->getItems()
            )
        );

        return $totalSegments;
    }
}
