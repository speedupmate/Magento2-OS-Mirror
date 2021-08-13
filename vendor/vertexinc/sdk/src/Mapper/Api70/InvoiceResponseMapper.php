<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api70;

use Vertex\Mapper\InvoiceResponseMapperInterface;
use Vertex\Mapper\Api60\InvoiceResponseMapper as InvoiceResponseMapper60;
use Vertex\Services\Invoice\ResponseInterface;

/**
 * API Level 70 implementation of {@see InvoiceResponseMapperInterface}
 */
class InvoiceResponseMapper implements InvoiceResponseMapperInterface
{
    /** @var InvoiceResponseMapper60 */
    private $parentMapper;

    /**
     * @param InvoiceResponseMapper60|null $parentMapper
     */
    public function __construct(InvoiceResponseMapper60 $parentMapper = null)
    {
        $this->parentMapper = $parentMapper?: new InvoiceResponseMapper60(
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
