<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Repository;

/**
 * Temando Location Repository Interface.
 *
 * Access the origin locations as defined for the merchant's account.
 *
 * @package  Temando\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
interface LocationRepositoryInterface
{
    /**
     * Load locations listing.
     *
     * @param int|null $offset
     * @param int|null $limit
     * @return \Temando\Shipping\Model\LocationInterface[]
     */
    public function getList($offset = null, $limit = null);
}
