<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Plugin;

use Magento\Framework\App\ActionInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ErrorMessageDisplayState;

/**
 * Turn on error messages during Multishipping
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
     */
    public function beforeExecute(ActionInterface $subject)
    {
        if ($this->config->isVertexActive()) {
            $this->messageDisplayState->enable();
        }
    }
}
