<?php

namespace Dotdigitalgroup\ChatGraphQl\Model\Resolver;

use Dotdigitalgroup\Chat\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;

class ChatData implements ResolverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeInterface
     */
    private $scopeConfig;

    /**
     * ChatData constructor.
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Config $config,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return [
            'is_enabled' => $this->config->isChatEnabled(),
            'api_space_id' => $this->config->getApiSpaceId(),
            'cookie_name' => Config::COOKIE_CHAT_PROFILE,
        ];
    }
}
