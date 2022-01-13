<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

namespace Vertex\Tax\Model\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Multishipping\Controller\Checkout\AddressesPost;
use Magento\Multishipping\Controller\Checkout\Overview;
use Magento\Multishipping\Controller\Checkout\OverviewPost;
use Magento\Multishipping\Controller\Checkout\ShippingPost;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ErrorMessageDisplayState;

/**
 * Turn on error messages during Multishipping
 *
 * Intercepted classes:
 *
 * @see AddressesPost - Intercepted class
 * @see ShippingPost - Intercepted class
 * @see Overview - Intercepted class
 * @see OverviewPost - Intercepted class
 */
class MultishippingErrorMessageSupport
{
    /** @var Config */
    private $config;

    /** @var ErrorMessageDisplayState */
    private $messageDisplayState;

    /**
     * @param ErrorMessageDisplayState $messageDisplayState
     * @param Config $config
     */
    public function __construct(
        ErrorMessageDisplayState $messageDisplayState,
        Config $config
    ) {
        $this->messageDisplayState = $messageDisplayState;
        $this->config = $config;
    }

    /**
     * Turn on error messages
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) $subject required for interceptor
     * @see AddressesPost::execute() - Intercepted method
     * @see ShippingPost::execute() - Intercepted method
     * @see Overview::execute() - Intercepted method
     * @see OverviewPost::execute() - Intercepted method
     */
    public function beforeExecute(ActionInterface $subject)
    {
        if ($this->config->isVertexActive()) {
            $this->messageDisplayState->enable();
        }
    }
}
