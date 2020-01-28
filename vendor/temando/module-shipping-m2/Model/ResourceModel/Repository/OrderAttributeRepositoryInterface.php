<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Repository;

use Temando\Shipping\Model\Order\OrderRecipientInterface;

/**
 * Temando Order Attribute Repository Interface.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface OrderAttributeRepositoryInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $salesOrder
     * @param \Temando\Shipping\Model\Order\OrderRecipientInterface $recipient
     * @return \Temando\Shipping\Model\Order\OrderRecipientInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveRecipient($salesOrder, OrderRecipientInterface $recipient);
}
