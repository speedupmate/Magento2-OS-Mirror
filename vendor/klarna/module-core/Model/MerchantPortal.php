<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Model;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Api\OrderInterface as KlarnaOrder;
use Magento\Sales\Api\Data\OrderInterface as MageOrder;
use Magento\Store\Api\Data\StoreInterface;

class MerchantPortal
{
    const MERCHANT_PORTAL_US = 'https://us.portal.klarna.com/orders/';
    const MERCHANT_PORTAL_EU = 'https://eu.portal.klarna.com/orders/';
    const MERCHANT_PORTAL_OC = 'https://oc.portal.klarna.com/orders/';

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @param ConfigHelper $configHelper
     * @codeCoverageIgnore
     */
    public function __construct(ConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * Get Merchant Portal link for order
     *
     * @param MageOrder   $mageOrder
     * @param KlarnaOrder $klarnaOrder
     * @return string
     */
    public function getOrderMerchantPortalLink(MageOrder $mageOrder, KlarnaOrder $klarnaOrder): string
    {
        $store      = $mageOrder->getStore();
        $merchantId = $this->configHelper->getApiConfig('merchant_id', $store);

        $url = $this->getMerchantPortalUrl($store);

        $merchantIdArray = explode("_", $merchantId);
        $url            .= "merchants/" . $merchantIdArray[0] . "/orders/" . $klarnaOrder->getKlarnaOrderId();
        return $url;
    }

    /**
     * Getting back the merchant portal url
     *
     * @param StoreInterface $store
     * @return string
     */
    private function getMerchantPortalUrl(StoreInterface $store): string
    {
        $apiVersion = $this->configHelper->getApiConfig('api_version', $store);

        switch ($apiVersion) {
            case 'na':
            case 'kp_na':
                return self::MERCHANT_PORTAL_US;
            case 'kp_eu':
                return self::MERCHANT_PORTAL_EU;
            case 'kp_oc':
                return self::MERCHANT_PORTAL_OC;
            default:
                return self::MERCHANT_PORTAL_EU;
        }
    }
}
