<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Services\TaxAreaLookup;

use Vertex\Data\TaxAreaLookupResultInterface;
use Vertex\Services\SoapCallResponseInterface;

/**
 * {@inheritDoc}
 */
class Response implements ResponseInterface, SoapCallResponseInterface
{
    /** @var int */
    private $httpCallTime;

    /** @var TaxAreaLookupResultInterface[] */
    private $results = [];

    /**
     * @inheritDoc
     */
    public function getHttpCallTime()
    {
        return $this->httpCallTime;
    }

    /**
     * @inheritdoc
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @inheritDoc
     */
    public function setHttpCallTime($milliseconds)
    {
        $this->httpCallTime = $milliseconds;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setResults(array $results)
    {
        array_walk(
            $results,
            static function ($result) {
                if (!($result instanceof TaxAreaLookupResultInterface)) {
                    throw new \InvalidArgumentException(
                        'Lookup results must be instances of TaxAreaLookupResultInterface'
                    );
                }
            }
        );
        $this->results = $results;
        return $this;
    }
}
