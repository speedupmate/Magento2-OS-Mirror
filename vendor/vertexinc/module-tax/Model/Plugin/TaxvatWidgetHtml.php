<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Customer\Block\Widget\Taxvat;
use Magento\Framework\View\Element\BlockFactory;
use Vertex\Tax\Block\Customer\Widget\TaxCountry;
use Vertex\Tax\Model\Config;

/**
 * Includes an extra country field rendered after VAT number
 *
 * @see Taxvat
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
     *
     * @see Taxvat::toHtml()
     */
    public function afterToHtml(Taxvat $subject, string $result): string
    {
        if ($this->config->isVertexActive()) {
            $block = $this->blockFactory->createBlock(TaxCountry::class);
            $result .= $block->toHtml();
        }

        return $result;
    }
}
