<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Default implementation of {@see ProductLoadIdResolverInterface}
 */
class TypicalProductLoadIdResolver implements ProductLoadIdResolverInterface
{
    /**
     * Get the product load Id
     *
     * @param ProductInterface $product
     * @return int
     */
    public function execute(ProductInterface $product): int
    {
        return (int) $product->getId();
    }
}
