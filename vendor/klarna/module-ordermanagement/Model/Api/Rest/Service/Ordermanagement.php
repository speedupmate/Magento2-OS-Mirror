<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Ordermanagement\Model\Api\Rest\Service;

use Klarna\Core\Api\ServiceInterface;
use Klarna\Core\Helper\VersionInfo;
use Klarna\Core\Logger\Api\Container;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Ordermanagement
{
    const API_VERSION = 'v1';

    const ACTIONS = [
        'acknowledge_order'               => 'Acknowledge order',
        'get_order'                       => 'Get order',
        'update_order_items'              => 'Update order items',
        'extend_authorization'            => 'Extend authorization',
        'update_merchant_references'      => 'Update merchant references',
        'update_addresses'                => 'Update addresses',
        'add_shipping_info'               => 'Add shipping info',
        'cancel_order'                    => 'Cancel order',
        'capture_order'                   => 'Capture order',
        'get_capture'                     => 'Get capture',
        'add_shipping_details_to_capture' => 'Add shipping details to capture',
        'update_capture_billing_address'  => 'Update capture billing address',
        'resend_order_invoice'            => 'Resend order invoice',
        'refund'                          => 'Refund',
        'release_authorization'           => 'Release authorization',
    ];

    /**
     * @var ServiceInterface
     */
    private $service;
    /**
     * @var Container
     */
    private $loggerContainer;

    /**
     * Initialize class
     *
     * @param ServiceInterface $service
     * @param VersionInfo      $versionInfo
     * @param Container|null   $loggerContainer
     */
    public function __construct(
        ServiceInterface $service,
        VersionInfo $versionInfo,
        Container $loggerContainer = null
    ) {
        $this->service = $service;
        $this->loggerContainer = $loggerContainer ?? ObjectManager::getInstance()->get(Container::class);

        $version = sprintf(
            '%s;Core/%s',
            $versionInfo->getVersion('Klarna_Ordermanagement'),
            $versionInfo->getVersion('Klarna_Core')
        );

        $mageMode = $versionInfo->getMageMode();
        $mageVersion = $versionInfo->getMageEdition() . ' ' . $versionInfo->getMageVersion();
        $mageInfo = "Magento {$mageVersion} {$mageMode} mode";
        $this->service->setUserAgent('Magento2_OM', $version, $mageInfo);
        $this->service->setHeader('Accept', '*/*');
    }

    /**
     * Setup connection based on store config
     *
     * @param string $user
     * @param string $password
     * @param string $url
     */
    public function resetForStore($user, $password, $url)
    {
        $this->service->connect($user, $password, $url);
    }

    /**
     * Used by merchants to acknowledge the order.
     *
     * Merchants will receive the order confirmation push until the order has been acknowledged.
     *
     * @param $orderId
     *
     * @return array
     */
    public function acknowledgeOrder($orderId)
    {
        $this->loggerContainer->setAction(self::ACTIONS['acknowledge_order']);
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/acknowledge";
        return $this->service->makeRequest(
            $url,
            [],
            ServiceInterface::POST,
            $orderId
        );
    }

    /**
     * Get the current state of an order
     *
     * @param $orderId
     *
     * @return array
     */
    public function getOrder($orderId)
    {
        $this->loggerContainer->setAction(self::ACTIONS['get_order']);
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}";
        return $this->service->makeRequest(
            $url,
            [],
            ServiceInterface::GET,
            $orderId
        );
    }

    /**
     * Update the total order amount of an order, subject to a new customer credit check.
     *
     * The updated amount can optionally be accompanied by a descriptive text and new order lines. Supplied order lines
     * will replace the existing order lines. If no order lines are supplied in the call, the existing order lines will
     * be deleted. The updated 'order_amount' must not be negative, nor less than current 'captured_amount'. Currency
     * is inferred from the original order.
     *
     * @param string $orderId
     * @param array  $data
     *
     * @return array
     */
    public function updateOrderItems($orderId, $data)
    {
        $this->loggerContainer->setAction(self::ACTIONS['update_order_items']);
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/authorization";
        return $this->service->makeRequest(
            $url,
            $data,
            ServiceInterface::PATCH,
            $orderId
        );
    }

    /**
     * Extend the order's authorization by default period according to merchant contract.
     *
     * @param string $orderId
     *
     * @return array
     */
    public function extendAuthorization($orderId)
    {
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/extend-authorization-time";

        $this->loggerContainer->setAction(self::ACTIONS['extend_authorization']);
        return $this->service->makeRequest(
            $url,
            [],
            ServiceInterface::POST,
            $orderId
        );
    }

    /**
     * Update one or both merchant references. To clear a reference, set its value to "" (empty string).
     *
     * @param string $orderId
     * @param string $merchantReference1
     * @param string $merchantReference2
     *
     * @return array
     */
    public function updateMerchantReferences($orderId, $merchantReference1, $merchantReference2 = null)
    {
        $url  = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/merchant-references";
        $data = [
            'merchant_reference1' => $merchantReference1
        ];

        if ($merchantReference2 !== null) {
            $data['merchant_reference2'] = $merchantReference2;
        }

        $this->loggerContainer->setAction(self::ACTIONS['update_merchant_references']);
        return $this->service->makeRequest(
            $url,
            $data,
            ServiceInterface::PATCH,
            $orderId
        );
    }

    /**
     * Update billing and/or shipping address for an order, subject to customer credit check.
     * Fields can be updated independently. To clear a field, set its value to "" (empty string).
     *
     * Mandatory fields can not be cleared
     *
     * @param string $orderId
     * @param array  $data
     *
     * @return array
     */
    public function updateAddresses($orderId, $data)
    {
        $this->loggerContainer->setAction(self::ACTIONS['update_addresses']);
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/customer-details";
        return $this->service->makeRequest(
            $url,
            $data,
            ServiceInterface::PATCH,
            $orderId
        );
    }

    /**
     * Add shipping info to capture
     *
     * @param string $orderId
     * @param string $captureId
     * @param array  $data
     * @return array
     */
    public function addShippingInfo($orderId, $captureId, $data)
    {
        $this->loggerContainer->setAction(self::ACTIONS['add_shipping_info']);
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures/{$captureId}/shipping-info";
        return $this->service->makeRequest(
            $url,
            $data,
            ServiceInterface::POST,
            $orderId
        );
    }

    /**
     * Cancel an authorized order. For a cancellation to be successful, there must be no captures on the order.
     * The authorized amount will be released and no further updates to the order will be allowed.
     *
     * @param string $orderId
     *
     * @return array
     */
    public function cancelOrder($orderId)
    {
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/cancel";

        $this->loggerContainer->setAction(self::ACTIONS['cancel_order']);
        return $this->service->makeRequest(
            $url,
            [],
            ServiceInterface::POST,
            $orderId
        );
    }

    /**
     * Capture the supplied amount. Use this call when fulfillment is completed, e.g. physical goods are being shipped
     * to the customer.
     * 'captured_amount' must be equal to or less than the order's 'remaining_authorized_amount'.
     * Shipping address is inherited from the order. Use PATCH method below to update the shipping address of an
     * individual capture. The capture amount can optionally be accompanied by a descriptive text and order lines for
     * the captured items.
     *
     * @param string $orderId
     * @param array  $data
     *
     * @return array
     * @throws \Klarna\Core\Model\Api\Exception
     */
    public function captureOrder($orderId, $data)
    {
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures";

        $this->loggerContainer->setAction(self::ACTIONS['capture_order']);
        return $this->service->makeRequest(
            $url,
            $data,
            ServiceInterface::POST,
            $orderId
        );
    }

    /**
     * Retrieve a capture
     *
     * @param string $orderId
     * @param string $captureId
     *
     * @return array
     */
    public function getCapture($orderId, $captureId)
    {
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures/{$captureId}";

        $this->loggerContainer->setAction(self::ACTIONS['get_capture']);
        return $this->service->makeRequest(
            $url,
            [],
            ServiceInterface::GET,
            $orderId
        );
    }

    /**
     * Appends new shipping info to a capture.
     *
     * @param $orderId
     * @param $captureId
     * @param $data
     *
     * @return array
     */
    public function addShippingDetailsToCapture($orderId, $captureId, $data)
    {
        $this->loggerContainer->setAction(self::ACTIONS['add_shipping_details_to_capture']);
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures/{$captureId}/shipping-info";
        return $this->service->makeRequest(
            $url,
            $data,
            ServiceInterface::POST,
            $orderId
        );
    }

    /**
     * Update the billing address for a capture. Shipping address can not be updated.
     * Fields can be updated independently. To clear a field, set its value to "" (empty string).
     *
     * Mandatory fields can not be cleared,
     *
     * @param $orderId
     * @param $captureId
     * @param $data
     *
     * @return array
     */
    public function updateCaptureBillingAddress($orderId, $captureId, $data)
    {
        $this->loggerContainer->setAction(self::ACTIONS['update_capture_billing_address']);
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures/{$captureId}/customer-details";
        return $this->service->makeRequest(
            $url,
            $data,
            ServiceInterface::PATCH,
            $orderId
        );
    }

    /**
     * Trigger a new send out of customer communication., typically a new invoice, for a capture.
     *
     * @param $orderId
     * @param $captureId
     *
     * @return array
     */
    public function resendOrderInvoice($orderId, $captureId)
    {
        $this->loggerContainer->setAction(self::ACTIONS['resend_order_invoice']);
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/captures/{$captureId}/trigger-send-out";
        return $this->service->makeRequest(
            $url,
            [],
            ServiceInterface::POST,
            $orderId
        );
    }

    /**
     * Refund an amount of a captured order. The refunded amount will be credited to the customer.
     * The refunded amount must not be higher than 'captured_amount'.
     * The refunded amount can optionally be accompanied by a descriptive text and order lines.
     *
     * @param $orderId
     * @param $data
     *
     * @return array
     */
    public function refund($orderId, $data)
    {
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/refunds";

        $this->loggerContainer->setAction(self::ACTIONS['refund']);
        return $this->service->makeRequest(
            $url,
            $data,
            ServiceInterface::POST,
            $orderId
        );
    }

    /**
     * Signal that there is no intention to perform further captures.
     *
     * @param string $orderId
     *
     * @return array
     */
    public function releaseAuthorization($orderId)
    {
        $url = "/ordermanagement/" . self::API_VERSION . "/orders/{$orderId}/release-remaining-authorization";

        $this->loggerContainer->setAction(self::ACTIONS['release_authorization']);
        return $this->service->makeRequest(
            $url,
            [],
            ServiceInterface::POST,
            $orderId
        );
    }

    /**
     * Get resource id from Location URL
     *
     * This assumes the ID is the last url path
     *
     * @param string|array|DataObject $location
     *
     * @return string
     */
    public function getLocationResourceId($location)
    {
        if ($location instanceof DataObject) {
            $responseObject = $location->getResponseObject();
            $location = $responseObject['headers']['Location'];
        }
        if (is_array($location)) {
            $location = array_shift($location);
        }

        $location = rtrim($location, '/');
        $locationArr = explode('/', $location);
        return array_pop($locationArr);
    }
}
