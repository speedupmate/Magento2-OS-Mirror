<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api70;

use Vertex\Mapper\InvoiceRequestMapperInterface;
use Vertex\Mapper\Api60\InvoiceRequestMapper as InvoiceRequestMapper60;
use Vertex\Services\Invoice\RequestInterface;

/**
 * API Level 70 implementation of {@see InvoiceRequestMapperInterface}
 */
class InvoiceRequestMapper implements InvoiceRequestMapperInterface
{
    /** @var InvoiceRequestMapper60 $parentMapper */
    private $parentMapper;

    /**
     * @param InvoiceRequestMapper60|null $parentMapper
     */
    public function __construct(InvoiceRequestMapper60 $parentMapper = null)
    {
        $this->parentMapper = $parentMapper ?: new InvoiceRequestMapper60(
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
}
