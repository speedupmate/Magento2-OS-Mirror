<?php
/**
 * This file is part of the Klarna KpGraphQl module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Plugin\Model\Resolver;

use Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods;
use Klarna\Kp\Model\Session;
use Klarna\Core\Helper\ConfigHelper;
use Magento\Store\Model\StoreManagerInterface;

class AvailablePaymentMethodsPlugin
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var ConfigHelper
     */
    private $configHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Session               $session
     * @param ConfigHelper          $configHelper
     * @param StoreManagerInterface $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(
        Session $session,
        ConfigHelper $configHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->session      = $session;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Modify results of resolve() call to apply the dynamic title for Klarna methods returned by API
     *
     * @param AvailablePaymentMethods $subject
     * @param array $list
     * @return array
     *
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterResolve(
        AvailablePaymentMethods $subject,
        array $list
    ): array {
        $store = $this->storeManager->getStore();
        if (!$this->configHelper->isPaymentConfigFlag('active', $store->getId())) {
            return $list;
        }

        $klarnaQuote = $this->session->getKlarnaQuote();
        if (!$klarnaQuote) {
            return $list;
        }

        $paymentCategories = json_decode(json_encode($klarnaQuote->getPaymentMethodInfo()), true);
        $paymentCategories = array_map(function ($paymentCategory) {
            return [
                'title' => $paymentCategory['name'],
                'code'  => 'klarna_' . $paymentCategory['identifier']
            ];
        }, $paymentCategories);
        $list = array_reverse(array_merge($list, $paymentCategories));
        $newList = [];
        foreach ($list as $method) {
            if (!in_array($method['code'], array_column($newList, 'code'))) {
                $newList[] = $method;
            }
        }
        return $newList;
    }
}
