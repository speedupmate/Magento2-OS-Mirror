<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Source;

use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order;

class OrderStatus extends Status
{
    public function toOptionArray(): array
    {
        $options = parent::toOptionArray();

        foreach ($options as $key => $option) {
            if ($option['value'] === Order::STATE_CLOSED) {
                unset($options[$key]);
                break;
            }
        }
        return $options;
    }
}
