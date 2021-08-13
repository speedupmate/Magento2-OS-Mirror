<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

/**
 * Factory for {@see \Collator}
 */
class CollatorFactory
{
    /**
     * Create an instance of {@see \Collator}
     */
    public function create(string $locale): \Collator
    {
        return new \Collator($locale);
    }
}
