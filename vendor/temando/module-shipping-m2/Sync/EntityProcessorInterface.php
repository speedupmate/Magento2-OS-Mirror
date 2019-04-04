<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Sync;

use Temando\Shipping\Sync\Exception\EventException;
use Temando\Shipping\Sync\Exception\EventProcessorException;

/**
 * Temando Entity Event Processor Interface
 *
 * @package  Temando\Shipping\Sync
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
interface EntityProcessorInterface
{
    /**
     * @param string $operation
     * @param string $externalEntityId
     * @return int Processed entity ID.
     * @throws EventException
     * @throws EventProcessorException
     */
    public function execute(string $operation, string $externalEntityId): int;
}
