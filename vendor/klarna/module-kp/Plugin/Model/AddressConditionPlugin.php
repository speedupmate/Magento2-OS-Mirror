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
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Rule\Condition\Address;

class AddressConditionPlugin
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
     * @param Address $address
     * @param mixed $validatedValue
     * @return mixed
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function beforeValidateAttribute(Address $address, $validatedValue)
    {
        if ($this->config->klarnaEnabled() &&
            in_array($validatedValue, $this->getQuotePaymentMethods())
        ) {
            $validatedValue = Kp::METHOD_CODE;
        }

        return $validatedValue;
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
