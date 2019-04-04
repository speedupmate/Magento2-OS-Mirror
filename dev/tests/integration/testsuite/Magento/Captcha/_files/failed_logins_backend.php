<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Captcha\Model\ResourceModel\Log;

$objectManager = Bootstrap::getObjectManager();
$logFactory = $objectManager->get(LogFactory::class);

/** @var Log $captchaLog */
$captchaLog = $logFactory->create();
$captchaLog->logAttempt('mageadmin');
