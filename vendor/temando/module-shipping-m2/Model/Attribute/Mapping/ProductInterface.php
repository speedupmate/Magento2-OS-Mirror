<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Attribute\Mapping;

/**
 * Temando Product Attribute Mapping Interface.
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface ProductInterface
{
    const NODE_PATH_ID = 'node_path_id';
    const LABEL = 'label';
    const DESCRIPTION = 'description';
    const MAPPED_ATTRIBUTE_ID = 'mapping_attribute_id';
    const IS_DEFAULT = 'is_default';

    /**
     * @return string
     */
    public function getNodePathId();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getMappingAttributeId();

    /**
     * @return int
     */
    public function getIsDefault();
}
