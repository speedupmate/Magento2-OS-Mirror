<?php

namespace Dotdigitalgroup\EmailGraphQl\Model\Resolver;

use Dotdigitalgroup\Email\Helper\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class EasyEmailCaptureCheckout implements ResolverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $isEmailCaptureEnabled =  $this->scopeConfig->getValue(
            Config::XML_PATH_CONNECTOR_EMAIL_CAPTURE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getWebsite()->getId()
        );

        return [
            'is_enabled' => $isEmailCaptureEnabled,
        ];
    }
}
