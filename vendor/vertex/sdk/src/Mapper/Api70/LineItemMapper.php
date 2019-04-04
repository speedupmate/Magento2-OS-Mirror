<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api70;

use Vertex\Data\LineItemInterface;
use Vertex\Mapper\Api60\LineItemMapper as LineItemMapper60;
use Vertex\Mapper\LineItemMapperInterface;

/**
 * API Level 70 implementation of {@see LineItemMapperInterface}
 */
class LineItemMapper implements LineItemMapperInterface
{
    /** @var LineItemMapper60 */
    private $parentMapper;

    /**
     * @param LineItemMapper60|null $parentMapper
     */
    public function __construct(LineItemMapper60 $parentMapper = null)
    {
        $this->parentMapper = $parentMapper ?: new LineItemMapper60(null, new CustomerMapper());
    }

    /**
     * @inheritDoc
     */
    public function build(\stdClass $map)
    {
        return $this->parentMapper->build($map);
    }

    /**
     * @inheritDoc
     */
    public function map(LineItemInterface $object)
    {
        return $this->parentMapper->map($object);
    }
}
