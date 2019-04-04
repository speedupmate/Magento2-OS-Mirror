<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api70;

use Vertex\Mapper\Api60\QuoteResponseMapper as QuoteResponseMapper60;
use Vertex\Mapper\QuoteResponseMapperInterface;
use Vertex\Services\Quote\ResponseInterface;

/**
 * API Level 70 implementation of {@see QuoteResponseMapperInterface}
 */
class QuoteResponseMapper implements QuoteResponseMapperInterface
{
    /** @var QuoteResponseMapper60 */
    private $parentMapper;

    /**
     * @param QuoteResponseMapper60 $parentMapper
     */
    public function __construct(QuoteResponseMapper60 $parentMapper = null)
    {
        $this->parentMapper = $parentMapper ?: new QuoteResponseMapper60(
            null,
            new CustomerMapper(),
            null,
            new LineItemMapper()
        );
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
    public function map(ResponseInterface $object)
    {
        return $this->parentMapper->map($object);
    }
}
