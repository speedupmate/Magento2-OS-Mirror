<?php
/**
 * This file is part of the Klarna Onsitemessaging module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Onsitemessaging\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PlacementIds implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'credit-promotion-standard',    'label' => __('Credit Promotion - Standard')],
            ['value' => 'credit-promotion-small',       'label' => __('Credit Promotion - Small')],
            ['value' => 'credit-promotion-badge',       'label' => __('Credit Promotion - Badge')],
            ['value' => 'sidebar-promotion-auto-size',  'label' => __('Sidebar Promotion - Auto-Size')],
            ['value' => 'top-strip-promotion-standard', 'label' => __('Top Strip Promotion - Standard')],
            ['value' => 'homepage-promotion-tall',      'label' => __('Homepage Promotion - Tall')],
            ['value' => 'homepage-promotion-box',       'label' => __('Homepage Promotion - Box')],
            ['value' => 'homepage-promotion-wide',      'label' => __('Homepage Promotion - Wide')],
            ['value' => 'other',                        'label' => __('Other')]
        ];
    }
}
