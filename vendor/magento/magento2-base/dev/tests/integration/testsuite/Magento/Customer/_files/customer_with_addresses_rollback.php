<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var CustomerRepositoryInterface $customerRepo */
$customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
try {
    $customer = $customerRepo->get('customer_with_addresses@test.com');
    $customerRepo->delete($customer);
} catch (NoSuchEntityException $exception) {
    //Already deleted
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
