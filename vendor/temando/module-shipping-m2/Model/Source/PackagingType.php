<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Temando Packaging Type Source Model
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PackagingType extends AbstractSource
{
    const PACKAGING_TYPE_NONE = 'none';
    const PACKAGING_TYPE_PACKED = 'packed';
    const PACKAGING_TYPE_ASSIGNED = 'assigned';

    /**
     * Retrieve option array
     *
     * @return mixed[]
     */
    public function getAllOptions(): array
    {
        return [
            ['value' => self::PACKAGING_TYPE_NONE, 'label' => __('None')],
            ['value' => self::PACKAGING_TYPE_PACKED, 'label' => __('Pre-packaged')],
            ['value' => self::PACKAGING_TYPE_ASSIGNED, 'label' => __('Assigned')],
        ];
    }
}
