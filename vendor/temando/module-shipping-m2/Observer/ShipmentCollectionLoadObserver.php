<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection as ShipmentCollection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Order\Grid\Collection as OrderShipmentCollection;

/**
 * Temando Shipment Collection Load Observer
 *
 * @package Temando\Shipping\Observer
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentCollectionLoadObserver implements ObserverInterface
{
    /**
     * Add the shipment status column, aliased with namespace prefix to avoid collisions.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $collection = $observer->getData('collection');
        if ($collection instanceof ShipmentCollection || $collection instanceof OrderShipmentCollection) {
            $index = 'shipment_status';
            $alias = 'temando_shipment_status';

            try {
                $collection->getSelect()->columns([$alias => $index]);
                $where = $collection->getSelect()->getPart(\Zend_Db_Select::WHERE);
                $collection->getSelect()->setPart(\Zend_Db_Select::WHERE, str_replace($alias, $index, $where));
            } catch (\Zend_Db_Select_Exception $exception) {
                return;
            }
        }
    }
}
