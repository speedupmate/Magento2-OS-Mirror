<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Customer\Block\Widget\Taxvat;
use Magento\Framework\View\Element\BlockFactory;
use Vertex\Tax\Block\Customer\Widget\TaxCountry;
use Vertex\Tax\Model\Config;

/**
 * Includes an extra country field rendered after VAT number
 */
class TaxvatWidgetHtml
{
    /** @var BlockFactory */
    private $blockFactory;

    /** @var Config */
    private $config;

    public function __construct(
        BlockFactory $blockFactory,
        Config $config
    ) {
        $this->blockFactory = $blockFactory;
        $this->config = $config;
    }

    /**
     * Update the content of returned HTML to include the country field
     */
    public function afterToHtml(Taxvat $subject, string $result): string
    {
        if ($this->config->isVertexActive()) {
            $block = $this->blockFactory->createBlock(TaxCountry::class);
            $result = $result . $block->toHtml();
        }

        return $result;
    }
}
