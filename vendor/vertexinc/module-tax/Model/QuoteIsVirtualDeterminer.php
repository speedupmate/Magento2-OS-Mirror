<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model;

use Magento\Tax\Api\Data\QuoteDetailsInterface;

class QuoteIsVirtualDeterminer
{
    /**
     * Determine whether a quote is virtual or not
     *
     * This determination is made by whether or not the quote has a shipping
     * item
     */
    public function isVirtual(QuoteDetailsInterface $quoteDetails): bool
    {
        $items = $quoteDetails->getItems();
        foreach ($items as $item) {
            if (!$item->getExtensionAttributes()->getIsVirtual()) {
                return false;
            }
        }
        return true;
    }
}
