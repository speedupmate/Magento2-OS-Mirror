<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Rest\Adapter;

use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\ListRequestInterface;
use Temando\Shipping\Rest\Request\QualifyRequest;
use Temando\Shipping\Rest\Response\DataObject\Experience;
use Temando\Shipping\Rest\Response\DataObject\OrderQualification;

/**
 * The Temando Experience API interface defines the supported subset of operations
 * as available at the Temando API.
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface ExperienceApiInterface
{
    /**
     * Retrieve shipping options for the current quote.
     *
     * @param QualifyRequest $request
     * @return OrderQualification[]
     * @throws AdapterException
     */
    public function qualify(QualifyRequest $request);

    /**
     * Obtain shipping experiences.
     *
     * @param ListRequestInterface $request
     * @return Experience[]
     * @throws AdapterException
     */
    public function getExperiences(ListRequestInterface $request);
}
