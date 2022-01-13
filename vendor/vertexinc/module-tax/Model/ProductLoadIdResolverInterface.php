<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Commodity Interface
 * @api
 */
interface ProductLoadIdResolverInterface
{
    /**
     * @param ProductInterface $product
     * @return int
     */
    public function execute(ProductInterface $product) : int;
}
