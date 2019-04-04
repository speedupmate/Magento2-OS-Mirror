<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\ViewModel\DataProvider;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Temando\Shipping\Model\PickupInterface;

/**
 * Pickup Fulfillment URL provider
 *
 * @package Temando\Shipping\ViewModel
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PickupUrl implements EntityUrlInterface
{
    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * PickupUrl constructor.
     * @param UrlInterface $urlBuilder
     * @param RedirectInterface $redirect
     * @param EncoderInterface $encoder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        RedirectInterface $redirect,
        EncoderInterface $encoder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->redirect = $redirect;
        $this->encoder = $encoder;
    }

    /**
     * Creating pickup fulfillments via UI is not supported.
     *
     * @return string
     */
    public function getNewActionUrl(): string
    {
        return '';
    }

    /**
     * Link to the pickup fulfillment grid listing
     *
     * @return string
     */
    public function getListActionUrl(): string
    {
        return $this->urlBuilder->getUrl('temando/pickup/index');
    }

    /**
     * Link to the "Prepare for Pickup" page with editable quantities.
     *
     * @param mixed[] $data Item data to pick entity identifiers.
     * @return string
     */
    public function getEditActionUrl(array $data): string
    {
        return $this->urlBuilder->getUrl('temando/pickup/prepare', [
            'pickup_id' => $data[PickupInterface::PICKUP_ID],
            'sales_order_id' => $data[PickupInterface::SALES_ORDER_ID],
        ]);
    }

    /**
     * Link to the pickup detail view.
     *
     * @param mixed[] $data Item data to pick entity identifier.
     * @return string
     */
    public function getViewActionUrl(array $data): string
    {
        return $this->urlBuilder->getUrl('temando/pickup/view', [
            'pickup_id' => $data[PickupInterface::PICKUP_ID],
            'sales_order_id' => $data[PickupInterface::SALES_ORDER_ID],
        ]);
    }

    /**
     * Link to the pickup forward action with placeholder.
     *
     * @return string
     */
    public function getForwardActionUrl(): string
    {
        return $this->urlBuilder->getUrl('temando/pickup/forward', ['pickup_id' => '--id--']);
    }

    /**
     * Link to the "mark as ready for pickup" POST action
     *
     * @param mixed[] $data Item data to pick entity identifiers.
     * @return string
     */
    public function getReadyActionUrl(array $data): string
    {
        $uenc = $this->encoder->encode($this->redirect->getRefererUrl());

        $routeParams = [];
        $routeParams['pickup_id'] = $data[PickupInterface::PICKUP_ID];
        $routeParams['sales_order_id'] = $data[PickupInterface::SALES_ORDER_ID];
        $routeParams[ActionInterface::PARAM_NAME_URL_ENCODED] = $uenc;

        return $this->urlBuilder->getUrl('temando/pickup/ready', $routeParams);
    }

    /**
     * Link to the "mark as picked up" POST action
     *
     * @param mixed[] $data Item data for the implementer to pick entity identifier.
     * @return string
     */
    public function getCollectedActionUrl(array $data): string
    {
        $uenc = $this->encoder->encode($this->redirect->getRefererUrl());

        $routeParams = [];
        $routeParams['pickup_id'] = $data[PickupInterface::PICKUP_ID];
        $routeParams['sales_order_id'] = $data[PickupInterface::SALES_ORDER_ID];
        $routeParams[ActionInterface::PARAM_NAME_URL_ENCODED] = $uenc;

        return $this->urlBuilder->getUrl('temando/pickup/collected', $routeParams);
    }

    /**
     * Link to the pickup cancel POST action.
     *
     * @param mixed[] $data Item data for the implementer to pick entity identifier.
     * @return string
     */
    public function getDeleteActionUrl(array $data): string
    {
        $uenc = $this->encoder->encode($this->redirect->getRefererUrl());

        $routeParams = [];
        $routeParams['pickup_id'] = $data[PickupInterface::PICKUP_ID];
        $routeParams['sales_order_id'] = $data[PickupInterface::SALES_ORDER_ID];
        $routeParams[ActionInterface::PARAM_NAME_URL_ENCODED] = $uenc;

        return $this->urlBuilder->getUrl('temando/pickup/cancel', $routeParams);
    }

    /**
     * Link to the pickup print action.
     *
     * @param mixed[] $data Item data for the implementer to pick entity identifier.
     * @return string
     */
    public function getPrintActionUrl(array $data): string
    {
        return $this->urlBuilder->getUrl('temando/pickup/print', [
            'pickup_id' => $data[PickupInterface::PICKUP_ID],
            'sales_order_id' => $data[PickupInterface::SALES_ORDER_ID],
        ]);
    }
}
