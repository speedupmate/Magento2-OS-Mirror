<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Model\Adminhtml\Source;

/**
 * Authorize.net Environment Dropdown source
 *
 * @deprecated Starting from Magento 2.2.11 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class Environment implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var string
     */
    private static $environmentProduction = 'production';

    /**
     * @var string
     */
    private static $environmentSandbox = 'sandbox';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::$environmentSandbox,
                'label' => __('Sandbox'),
            ],
            [
                'value' => self::$environmentProduction,
                'label' => __('Production'),
            ],
        ];
    }
}
