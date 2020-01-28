<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Rest\Adapter;

use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\ListRequestInterface;
use Temando\Shipping\Rest\Response\Type\ExperienceResponseType;

/**
 * The Temando Experiences API interface defines the supported subset of operations
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
     * Obtain shipping experiences.
     *
     * @param ListRequestInterface $request
     * @return ExperienceResponseType[]
     * @throws AdapterException
     */
    public function getExperiences(ListRequestInterface $request);
}
