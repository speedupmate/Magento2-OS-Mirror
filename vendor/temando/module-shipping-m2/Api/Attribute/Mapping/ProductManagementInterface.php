<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Api\Attribute\Mapping;

/**
 * Process product attribute mappings.
 *
 * @api
 * @package Temando\Shipping\Api
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface ProductManagementInterface
{
    /**
     * Retrieve shipping platform attributes.
     *
     * @return mixed[]
     */
    public function getShippingAttributes(): array;

    /**
     * @return mixed[]
     */
    public function getProductAttributes(): array;

    /**
     * @param string $nodePathId
     * @return string
     */
    public function delete($nodePathId): string;
}
