<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert that quantity of visible Cart items is the same as minicart configuration value.
 */
class AssertVisibleItemsQtyMessageOnCheckoutSummaryBlock extends AbstractConstraint
{
    /**
     * Items counter default message.
     */
    const ITEMS_COUNTER_MASSAGE = "%s Items in Cart";

    /**
     * Items counter message with limitations.
     */
    const VISIBLE_ITEMS_COUNTER_MASSAGE = "%s of %s Items in Cart";

    /**
     * Assert that quantity of visible Cart items is the same as minicart configuration value.
     *
     * @param CmsIndex $cmsIndex
     * @param CheckoutOnepage $checkoutPage
     * @param int $checkoutSummaryMaxVisibleCartItemsCount
     * @param int $totalItemsCountInShoppingCart
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CheckoutOnepage $checkoutPage,
        $checkoutSummaryMaxVisibleCartItemsCount,
        $totalItemsCountInShoppingCart
    ) {
        $sidebar = $cmsIndex->getCartSidebarBlock();
        $sidebar->openMiniCart();
        $sidebar->clickProceedToCheckoutButton();

        $reviewBlock = $checkoutPage->getReviewBlock();

        if ($totalItemsCountInShoppingCart > $checkoutSummaryMaxVisibleCartItemsCount) {
            $counterMessage = sprintf(
                self::VISIBLE_ITEMS_COUNTER_MASSAGE,
                $checkoutSummaryMaxVisibleCartItemsCount,
                $totalItemsCountInShoppingCart
            );
        } else {
            $counterMessage = sprintf(self::ITEMS_COUNTER_MASSAGE, $totalItemsCountInShoppingCart);
        }

        $count = $reviewBlock->getVisibleItemsCounter();

        \PHPUnit_Framework_Assert::assertEquals(
            $counterMessage,
            $count,
            'Visible cart items quantity has wrong value in mini shopping cart'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Quantity of visible Cart items is the same as minicart configuration value.';
    }
}
