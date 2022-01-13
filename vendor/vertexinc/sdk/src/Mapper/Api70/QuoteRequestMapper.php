<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api70;

use Vertex\Mapper\Api60\QuoteRequestMapper as QuoteRequestMapper60;
use Vertex\Mapper\QuoteRequestMapperInterface;
use Vertex\Services\Quote\RequestInterface;

/**
 * API Level 70 implementation of {@see QuoteRequestMapperInterface}
 */
class QuoteRequestMapper implements QuoteRequestMapperInterface
{
    /** @var QuoteRequestMapper60 */
    private $parentMapper;

    /**
     * @param QuoteRequestMapper60|null $parentMapper
     */
    public function __construct(QuoteRequestMapper60 $parentMapper = null)
    {
        $this->parentMapper = $parentMapper ?: new QuoteRequestMapper60(
            null,
            new CustomerMapper(),
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
    public function map(RequestInterface $object)
    {
        return $this->parentMapper->map($object);
    }

    /**
     * @inheritDoc
     */
    public function getLocationCodeMaxLength()
    {
        return $this->parentMapper->getLocationCodeMaxLength();
    }

    /**
     * @inheritDoc
     */
    public function getLocationCodeMinLength()
    {
        return $this->parentMapper->getLocationCodeMinLength();
    }

    /**
     * @inheritDoc
     */
    public function validateLocationCode($fieldValue)
    {
        return $this->parentMapper->validateLocationCode($fieldValue);
    }
}
