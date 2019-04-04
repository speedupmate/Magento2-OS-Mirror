<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Sales\Service\Operation;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Temando\Shipping\Model\ShipmentInterface;

/**
 * Temando Shipment Operation: Send Shipment Cancellation Email.
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <cnathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class SendCancelEmail implements ShipmentOperationInterface
{
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ShipmentSender
     */
    private $emailSender;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * SendCancelEmail constructor.
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentSender $emailSender
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentSender $emailSender,
        ManagerInterface $messageManager
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->emailSender = $emailSender;
        $this->messageManager = $messageManager;
    }

    /**
     * Send the shipment cancellation email
     * @param ShipmentInterface $shipment
     * @param int $salesShipmentId
     */
    public function execute(ShipmentInterface $shipment, int $salesShipmentId): void
    {
        /** @var \Magento\Sales\Model\Order\Shipment $salesShipment */
        $salesShipment = $this->shipmentRepository->get($salesShipmentId);

        try {
            $this->emailSender->send($salesShipment);
            $salesShipment->getEmailSent()
                ? $this->messageManager->addSuccessMessage(__('The shipment cancellation email was sent.'))
                : $this->messageManager->addWarningMessage(__('The shipment cancellation email was not sent.'));
        } catch (MailException $exception) {
            $this->messageManager->addErrorMessage(__('An error occurred during email sending.'));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('An error occurred during email sending.'));
        }
    }
}
