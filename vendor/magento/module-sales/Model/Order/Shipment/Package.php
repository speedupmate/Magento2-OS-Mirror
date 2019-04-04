<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

/**
 * Class Package
 * @api
 */
class Package implements \Magento\Sales\Api\Data\ShipmentPackageInterface
{
    /**
     * @var \Magento\Sales\Api\Data\ShipmentPackageExtensionInterface
     */
    private $extensionAttributes;

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentPackageExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }
}
