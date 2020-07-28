<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Ui\Component\Report\Listing\Column;

use PayPal\Braintree\Ui\Component\Report\Listing\Column\PaymentType;
use PayPal\Braintree\Ui\Component\Report\Listing\Column\Status;
use PayPal\Braintree\Ui\Component\Report\Listing\Column\TransactionType;

class CheckColumnOptionSourceTest extends \PHPUnit\Framework\TestCase
{
    public function testPaymentTypeSource()
    {
        $source = new PaymentType();
        $options = $source->toOptionArray();

        static::assertEquals(6, count($options));
    }

    public function testStatusSource()
    {
        $source = new Status();
        $options = $source->toOptionArray();

        static::assertEquals(14, count($options));
    }

    public function testTransactionTypeSource()
    {
        $source = new TransactionType();
        $options = $source->toOptionArray();

        static::assertEquals(2, count($options));
    }
}
