<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Temando\Shipping\Model\PackagingInterface;

/**
 * Temando Packaging Grid Actions
 *
 * @package  Temando\Shipping\Ui
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class PackagingActions extends Column
{
    const PACKAGING_URL_PATH_EDIT = 'temando/configuration_packaging/edit';
    const PACKAGING_URL_PATH_DELETE = 'temando/configuration_packaging/delete';

    /**
     * Add edit action to grid.
     *
     * @param mixed[] $dataSource
     * @return mixed[]
     */
    public function prepareDataSource(array $dataSource)
    {
        $key = PackagingInterface::PACKAGING_ID;
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item[$key])) {
                    $item[$name]['edit'] = [
                        'href' => $this->getContext()->getUrl(self::PACKAGING_URL_PATH_EDIT, [$key => $item[$key]]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->getContext()->getUrl(self::PACKAGING_URL_PATH_DELETE, [$key => $item[$key]]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete "${ $.$data.name }" of type "${ $.$data.type }"'),
                            'message' => __('Are you sure you want to delete the package "${ $.$data.name }"?')
                        ]
                    ];
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}
