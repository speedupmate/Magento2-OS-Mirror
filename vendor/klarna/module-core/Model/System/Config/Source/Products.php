<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Products implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value'         => "KP",
                "label"         => "Klarna Payment",
                "__disableTmpl" => true,
            ], [
                'value'         => "OM",
                "label"         => "Order Management",
                "__disableTmpl" => true,
            ], [
                'value'         => "OSM",
                "label"         => "On-Site Messaging",
                "__disableTmpl" => true,
            ], [
                'value'         => "GraphQL",
                "label"         => "GraphQL",
                "__disableTmpl" => true,
            ]
        ];
    }
}
