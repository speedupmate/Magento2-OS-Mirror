<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Adds the type of the request to the build subject
 *
 * @deprecated Starting from Magento 2.2.11 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class RequestTypeBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            'payload_type' => $this->type,
        ];
    }
}
