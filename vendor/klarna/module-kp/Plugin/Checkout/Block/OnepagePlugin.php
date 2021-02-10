<?php
/**
 * This file is part of the Klarna Kp module
 *
 * (c) Klarna AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Plugin\Checkout\Block;

use Klarna\Kp\Model\Payment\Kp;
use Klarna\Kp\Model\Session as KlarnaKpSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;

/**
 * This onepage checkout block run before or after specific actions of the magento onepage checkout block
 *
 */
class OnepagePlugin
{
    /**
     * @var KlarnaKpSession
     */
    private $kpSession;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param KlarnaKpSession       $kpSession
     * @param ScopeConfigInterface  $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        KlarnaKpSession $kpSession,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager
    ) {
        $this->kpSession = $kpSession;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Initialize Klarna Payment session before get js layout
     *
     * @param \Magento\Checkout\Block\Onepage $subject
     * @return array
     * @throws \Klarna\Core\Exception
     * @throws \Klarna\Core\Model\Api\Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetJsLayout(\Magento\Checkout\Block\Onepage $subject)
    {
        $store = $this->storeManager->getStore();
        if ($this->config->isSetFlag(
            sprintf('payment/%s/active', Kp::METHOD_CODE),
            ScopeInterface::SCOPE_STORES,
            $store
        )) {
            try {
                $this->kpSession->init();
            } catch (KlarnaApiException $e) {
                return [];
            }
        }
        return [];
    }
}
