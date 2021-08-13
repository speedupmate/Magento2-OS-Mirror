<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model;

class DomDocumentFactory
{
    /**
     * Create a DOMDocument
     */
    public function create(): \DOMDocument
    {
        return new \DOMDocument();
    }
}
