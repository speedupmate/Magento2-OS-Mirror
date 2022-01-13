<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Plugin\Model;

use Klarna\Core\Model\Config;
use Klarna\Kp\Model\Payment\Kp;
use Klarna\Kp\Model\QuoteRepository;
use Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\PaymentMethod;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;

class PaymentMethodPlugin
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @param Config          $config
     * @param Session         $session
     * @param QuoteRepository $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        Config $config,
        Session $session,
        QuoteRepository $quoteRepository
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Replaces detailed payment method names with generic kp key
     *
     * @param PaymentMethod $method
     * @param array         $result
     * @return array
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterGenerateFilterText(PaymentMethod $method, array $result): array
    {
        if (!$this->config->klarnaEnabled()) {
            return $result;
        }

        $handledFilterTextParts = [];
        foreach ($result as $filterTextPart) {
            if ($this->isPaymentMethod($filterTextPart)) {
                $filterTextPart = $this->replacePaymentMethod($filterTextPart);
            }
            $handledFilterTextParts[] = $filterTextPart;
        }
        return $handledFilterTextParts;
    }

    /**
     * Checks if input is a payment method
     *
     * @param string $input
     * @return bool
     */
    private function isPaymentMethod(string $input): bool
    {
        return strpos($input, 'quote_address:payment_method') === 0;
    }

    /**
     * Replaces payment methods saved in klarna quote with the kp key
     *
     * @param string $input
     * @return string
     */
    private function replacePaymentMethod(string $input): string
    {
        return str_replace(
            $this->getQuotePaymentMethods(),
            Kp::METHOD_CODE,
            $input
        );
    }

    /**
     * Returns payment methods saved in klarna quote (initially returned by klarna api)
     *
     * @return array
     */
    private function getQuotePaymentMethods(): array
    {
        $quoteId = $this->session->getQuoteId();
        if ($quoteId === null) {
            return [];
        }

        try {
            return $this->quoteRepository->getActiveByQuoteId($quoteId)->getPaymentMethods();
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }
}
