<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Repository;

use Temando\Shipping\Model\Attribute\Mapping\ProductInterface;

/**
 * Temando Product Attribute Mapping Repository Interface.
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface AttributeMappingProductRepositoryInterface
{
    /**
     * @param string $nodePath
     * @return ProductInterface
     */
    public function getByNodePathId($nodePath);

    /**
     * @param array $data
     * @return ProductInterface
     */
    public function save($data);

    /**
     * @return array
     */
    public function getMappedAttributes();
}
