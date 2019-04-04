<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Temando\Shipping\Model\Shipment\ShipmentDestinationInterface;
use Temando\Shipping\Model\ShipmentInterface;

/**
 * Temando Return To Name Grid Column Renderer.
 *
 * @package  Temando\Shipping\Ui
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class ReturnToName extends Column
{
    /**
     * Extract "Return To Name" from destination location.
     *
     * @param mixed[] $dataSource
     * @return mixed[]
     */
    public function prepareDataSource(array $dataSource)
    {
        $key = ShipmentInterface::DESTINATION_LOCATION;
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$key])) {
                    /** @var ShipmentDestinationInterface $destinationLocation */
                    $destinationLocation = $item[$key];
                    $item[$fieldName] = sprintf(
                        '%s %s',
                        $destinationLocation->getPersonFirstName(),
                        $destinationLocation->getPersonLastName()
                    );
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}
