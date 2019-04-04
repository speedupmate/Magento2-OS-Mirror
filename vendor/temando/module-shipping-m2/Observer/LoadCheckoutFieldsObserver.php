<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressExtensionInterface;
use Magento\Quote\Api\Data\AddressExtensionInterfaceFactory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Temando\Shipping\Api\Data\Checkout\AddressInterfaceFactory;
use Temando\Shipping\Model\ResourceModel\Repository\AddressRepositoryInterface;

/**
 * Attach checkout fields to quote shipping address.
 *
 * @package  Temando\Shipping\Observer
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class LoadCheckoutFieldsObserver implements ObserverInterface
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var AddressExtensionInterfaceFactory
     */
    private $addressExtensionFactory;

    /**
     * SaveCheckoutFieldsObserver constructor.
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressFactory
     * @param AddressExtensionInterfaceFactory $addressExtensionFactory
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory,
        AddressExtensionInterfaceFactory $addressExtensionFactory
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->addressExtensionFactory = $addressExtensionFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quoteAddress = null;

        if ($observer->hasData('shipping_assignment')) {
            /** @var ShippingAssignmentInterface $shippingAssignment */
            $shippingAssignment = $observer->getData('shipping_assignment');
            $quoteAddress = $shippingAssignment->getShipping()->getAddress();
        } elseif ($observer->hasData('quote_address')) {
            $quoteAddress = $observer->getData('quote_address');
        }

        if (!$quoteAddress) {
            return;
        }

        /** @var \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $quoteAddress */
        if ($quoteAddress->getAddressType() !== \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING) {
            return;
        }

        try {
            $checkoutAddress = $this->addressRepository->getByQuoteAddressId($quoteAddress->getId());
        } catch (NoSuchEntityException $e) {
            // no checkout fields found for shipping address
            return;
        }

        $extensionAttributes = $quoteAddress->getExtensionAttributes();
        if (!$extensionAttributes instanceof AddressExtensionInterface) {
            $extensionAttributes = $this->addressExtensionFactory->create();
        }

        $extensionAttributes->setCheckoutFields($checkoutAddress->getServiceSelection());
        $quoteAddress->setExtensionAttributes($extensionAttributes);
    }
}
