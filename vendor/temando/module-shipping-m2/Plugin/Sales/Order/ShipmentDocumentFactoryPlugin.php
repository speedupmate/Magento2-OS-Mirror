<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Plugin\Sales\Order;

use Magento\Framework\Filesystem\Driver\Https as HttpsDownloader;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Psr\Log\LoggerInterface;

/**
 * ShipmentDocumentFactoryPlugin
 *
 * @package Temando\Shipping\Plugin
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentDocumentFactoryPlugin
{
    /**
     * @var ShipmentExtensionFactory
     */
    private $shipmentExtensionFactory;

    /**
     * @var HttpsDownloader
     */
    private $downloader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ClientFactory
     */
    private $client;

    /**
     * ShipmentDocumentFactoryPlugin constructor.
     * @param ShipmentExtensionFactory $extensionFactory
     * @param HttpsDownloader $downloader - deprecatedsince 1.5.3, use $client
     * @param LoggerInterface $logger
     * @param ClientFactory|null $client
     */
    public function __construct(
        ShipmentExtensionFactory $extensionFactory,
        HttpsDownloader $downloader,
        LoggerInterface $logger,
        ClientFactory $client = null
    ) {
        $this->shipmentExtensionFactory = $extensionFactory;
        $this->downloader = $downloader;
        $this->logger = $logger;
        $this->client = $client ?: \Magento\Framework\App\ObjectManager::getInstance()->get(ClientFactory::class);
    }

    /**
     * The salesShipOrderV1 service allows to add extension attributes within
     * the "arguments" object. However, the ShipmentDocumentFactory does not
     * process it.
     * We need to add the extension attributes to the shipment entity ourselves.
     * Additionally, label contents are fetched from the given download URL.
     *
     * @see \Magento\Sales\Api\ShipOrderInterface::execute
     * @see \Magento\Sales\Model\Order\ShipmentDocumentFactory::create()
     *
     * @param ShipmentDocumentFactory $subject
     * @param callable $proceed
     * @param OrderInterface $order
     * @param array $items
     * @param array $tracks
     * @param ShipmentCommentCreationInterface|null $comment
     * @param bool $appendComment
     * @param array $packages
     * @param ShipmentCreationArgumentsInterface|null $arguments
     * @return ShipmentInterface
     */
    public function aroundCreate(
        ShipmentDocumentFactory $subject,
        callable $proceed,
        OrderInterface $order,
        array $items = [],
        array $tracks = [],
        ShipmentCommentCreationInterface $comment = null,
        $appendComment = false,
        array $packages = [],
        ShipmentCreationArgumentsInterface $arguments = null
    ) {
        /** @var ShipmentInterface $shipment */
        $shipment = $proceed($order, $items, $tracks, $comment, $appendComment, $packages, $arguments);
        if (!$arguments) {
            // no shipment creation arguments available
            return $shipment;
        }

        if (!$shipment->getExtensionAttributes()) {
            // extension arguments not initialized yet
            $extensionAttributes = $this->shipmentExtensionFactory->create();
            $shipment->setExtensionAttributes($extensionAttributes);
        }

        // shift external shipment id to shipment
        $extShipmentId = $arguments->getExtensionAttributes()->getExtShipmentId();
        if ($extShipmentId) {
            $shipment->getExtensionAttributes()->setExtShipmentId($extShipmentId);
        }

        // shift external return shipment id to shipment
        $extReturnShipmentId = $arguments->getExtensionAttributes()->getExtReturnShipmentId();
        if ($extReturnShipmentId) {
            $shipment->getExtensionAttributes()->setExtReturnShipmentId($extReturnShipmentId);
        }

        // shift external location id to shipment
        $extLocationId = $arguments->getExtensionAttributes()->getExtLocationId();
        if ($extLocationId) {
            $shipment->getExtensionAttributes()->setExtLocationId($extLocationId);
        }

        // shift external tracking url to shipment
        $extTrackingUrl = $arguments->getExtensionAttributes()->getExtTrackingUrl();
        if ($extTrackingUrl) {
            $shipment->getExtensionAttributes()->setExtTrackingUrl($extTrackingUrl);
        }

        // shift external tracking reference to shipment
        $extTrackingReference = $arguments->getExtensionAttributes()->getExtTrackingReference();
        if ($extTrackingReference) {
            $shipment->getExtensionAttributes()->setExtTrackingReference($extTrackingReference);
        }

        // download label and attach to shipment
        $labelUri = $arguments->getExtensionAttributes()->getShippingLabel();
        if ($labelUri) {
            try {
                $curl = $this->client->create();
                $curl->setOptions(
                    [
                        CURLOPT_HEADER => false,
                        CURLOPT_FOLLOWLOCATION => true
                    ]
                );
                $curl->get($labelUri);
                $labelContent = $curl->getBody();
            } catch (\Exception $exception) {
                $this->logger->critical('Shipping label download failed', ['exception' => $exception]);
                $labelContent = '';
            }

            $shipment->setShippingLabel($labelContent);
        }

        return $shipment;
    }
}
