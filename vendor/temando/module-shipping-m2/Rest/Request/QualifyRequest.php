<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Rest\Request;

use Temando\Shipping\Rest\Request\Type\QualificationRequestType;

/**
 * QualifyRequest
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class QualifyRequest
{
    /**
     * @var QualificationRequestType
     */
    private $requestType;

    /**
     * QualifyRequest constructor.
     *
     * @param QualificationRequestType $requestType
     */
    public function __construct(QualificationRequestType $requestType)
    {
        $this->requestType = $requestType;
    }

    /**
     * @return string
     */
    public function getRequestBody()
    {
        return json_encode($this->requestType);
    }
}
