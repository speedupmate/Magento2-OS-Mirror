<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Plugin\Model\Quote;

use Klarna\Core\Model\Config;
use Klarna\Kp\Model\QuoteRepository;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\SalesRule\Model\Quote\Discount;

class DiscountPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;
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
     * @param RequestInterface $request
     * @param Config           $config
     * @param Session          $session
     * @param QuoteRepository  $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        RequestInterface $request,
        Config $config,
        Session $session,
        QuoteRepository $quoteRepository
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->session = $session;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Sets the payment method in the address if it is set in the request
     *
     * @param Discount                    $subject
     * @param Quote                       $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total                       $total
     * @return array
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function beforeCollect(
        Discount $subject,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): array {
        if (!$this->config->klarnaEnabled()) {
            return [$quote, $shippingAssignment, $total];
        }

        $address = $shippingAssignment->getShipping()->getAddress();
        $paymentMethod = $this->getPaymentMethodFromRequest();

        if (!$address->getPaymentMethod()
            && $paymentMethod
            && in_array($paymentMethod, $this->getQuotePaymentMethods())) {
            $address->setPaymentMethod($paymentMethod);
        }

        return [$quote, $shippingAssignment, $total];
    }

    /**
     * Gets the payment method from the request
     *
     * @return string|null
     */
    private function getPaymentMethodFromRequest(): ?string
    {
        $content = json_decode($this->request->getContent(), true);
        if (isset($content['paymentMethod']['method'])) {
            return $content['paymentMethod']['method'];
        }
        return null;
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
