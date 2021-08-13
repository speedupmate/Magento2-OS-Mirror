<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_with_simple_product_without_address', 'reserved_order_id')->delete();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMask::class);
$quoteIdMask->delete($quote->getId());
