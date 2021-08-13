<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Plugin;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Vertex\Tax\Model\GuestAfterPaymentWorkaroundService;

/**
 * Plugin for Guest Checkout to trigger our _save_commit_after observers
 *
 * @see GuestAfterPaymentWorkaroundService Where everything happens
 * @see GuestPaymentInformationManagementInterface Intercepted Class
 */
class GuestPaymentInformationManagementPlugin
{
    /** @var GuestAfterPaymentWorkaroundService */
    private $workaroundService;

    /**
     * @param GuestAfterPaymentWorkaroundService $workaroundService
     */
    public function __construct(GuestAfterPaymentWorkaroundService $workaroundService)
    {
        $this->workaroundService = $workaroundService;
    }

    /**
     * Instruct the Workaround Service to Process its objects
     *
     * @see GuestPaymentInformationManagementInterface::savePaymentInformationAndPlaceOrder() Intercepted Method
     * @param GuestPaymentInformationManagementInterface $subject
     * @param int $orderId
     * @return int
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
        $orderId
    ) {
        $this->workaroundService->process();
        return $orderId;
    }
}
