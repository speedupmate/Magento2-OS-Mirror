<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Plugin;

use Magento\Customer\Block\Address\Edit;
use Vertex\AddressValidation\Block\Form\MessageRender;
use Vertex\AddressValidation\Model\Config;

class AddressMessagePlugin
{
    /** @var Config */
    private $config;

    /** @var string */
    private $matchString;

    public function __construct(
        Config $config,
        string $prependMatch = ''
    ) {
        $this->config = $config;
        $this->matchString = $prependMatch;
    }

    public function afterFetchView(Edit $subject, string $html): string
    {
        if (!$this->matchString || !$this->config->isAddressValidationEnabled()) {
            return $html;
        }

        $renderHtml = $this->getMessageRender($subject)->render();
        $start = strpos($html, $this->matchString);
        if (!$this->matchString || $start === false) {
            return $html . $renderHtml;
        }

        // Replace only the first occurrence
        return substr_replace($html, ($this->matchString . $renderHtml), $start, strlen($this->matchString));
    }

    private function getMessageRender(Edit $block) : MessageRender
    {
        /** @var MessageRender $render */
        $render = $block->getLayout()->createBlock(
            MessageRender::class,
            'vertex.address.validation.render',
            ['data' => [
                MessageRender::KEY_RENDER_HANDLE => 'validation_message',
                MessageRender::KEY_RENDER_BLOCK_NAME => 'vertex.address.validation'
            ]]
        );
        return $render;
    }
}
