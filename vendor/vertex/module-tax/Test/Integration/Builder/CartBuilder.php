<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Integration\Builder;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartManagementInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;

/**
 * Build a Cart
 */
class CartBuilder
{
    /** @var CartItemInterfaceFactory */
    private $cartItemFactory;

    /** @var CartManagementInterfaceFactory */
    private $cartManagerFactory;

    /** @var CartRepositoryInterfaceFactory */
    private $cartRepositoryFactory;

    /** @var CartItemInterface[] */
    private $items = [];

    /**
     * @param CartItemInterfaceFactory $cartItemFactory
     * @param CartManagementInterfaceFactory $cartManagerFactory
     * @param CartRepositoryInterfaceFactory $cartRepositoryFactory
     */
    public function __construct(
        CartItemInterfaceFactory $cartItemFactory,
        CartManagementInterfaceFactory $cartManagerFactory,
        CartRepositoryInterfaceFactory $cartRepositoryFactory
    ) {
        $this->cartItemFactory = $cartItemFactory;
        $this->cartManagerFactory = $cartManagerFactory;
        $this->cartRepositoryFactory = $cartRepositoryFactory;
    }

    /**
     * Add a product to the cart
     *
     * @param ProductInterface $product
     * @param int $qty Default 1
     * @return $this
     */
    public function addItem(ProductInterface $product, $qty = 1)
    {
        /** @var CartItemInterface $cartItem */
        $cartItem = $this->cartItemFactory->create();
        $cartItem->setSku($product->getSku());
        $cartItem->setName($product->getName());
        $cartItem->setQty($qty);
        $cartItem->setPrice($product->getPrice());
        $cartItem->setProductType($product->getTypeId());

        $this->items[] = $cartItem;

        return $this;
    }

    /**
     * Build the Cart
     *
     * @param int $customerId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function create($customerId)
    {
        /** @var CartManagementInterface $cartManager */
        $cartManager = $this->cartManagerFactory->create();

        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = $this->cartRepositoryFactory->create();

        $cartManager->createEmptyCartForCustomer($customerId);
        $cart = $cartManager->getCartForCustomer($customerId);
        $cart->setItems($this->items);
        $cartRepository->save($cart);

        if ($cart instanceof DataObject) {
            $cart->setData('totals_collected_flag', false);
        }

        return $cart;
    }

    /**
     * Set the cart items
     *
     * @param CartItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = [])
    {
        $this->items = $items;

        return $this;
    }
}
