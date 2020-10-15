<?php

namespace Dotdigitalgroup\Chat\CustomerData;

use Dotdigitalgroup\Chat\Model\Config;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Dotdigitalgroup\Email\Helper\Data;

class Chat implements SectionSourceInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Chat constructor.
     * @param Config $config
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        Data $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSectionData()
    {
        return [
            'isEnabled' => $this->config->isChatEnabled(),
            'apiSpaceId' => $this->config->getApiSpaceId(),
            'customerId' => $this->getCustomerId(),
            'profileEndpoint' => $this->getEndpointWithStoreCode(),
            'cookieName' => Config::COOKIE_CHAT_PROFILE,
        ];
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getEndpointWithStoreCode()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB, true)
            . Config::MAGENTO_PROFILE_CALLBACK_ROUTE;
    }

    /**
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerId()
    {
        if ($customer = $this->config->getSession()->getQuote()->getCustomer()) {
            return $customer->getId();
        }
        return null;
    }
}
