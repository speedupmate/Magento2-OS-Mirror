<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Gateway\Helper;

use Braintree\Transaction;
use InvalidArgumentException;
use PayPal\Braintree\Gateway\Helper\SubjectReader;

class SubjectReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Helper\SubjectReader::readCustomerId
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The "customerId" field does not exists
     */
    public function testReadCustomerIdWithException()
    {
        $this->subjectReader->readCustomerId([]);
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Helper\SubjectReader::readCustomerId
     */
    public function testReadCustomerId()
    {
        $customerId = 1;
        static::assertEquals($customerId, $this->subjectReader->readCustomerId(['customer_id' => $customerId]));
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Helper\SubjectReader::readPublicHash
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The "public_hash" field does not exists
     */
    public function testReadPublicHashWithException()
    {
        $this->subjectReader->readPublicHash([]);
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Helper\SubjectReader::readPublicHash
     */
    public function testReadPublicHash()
    {
        $hash = 'fj23djf2o1fd';
        static::assertEquals($hash, $this->subjectReader->readPublicHash(['public_hash' => $hash]));
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Helper\SubjectReader::readPayPal
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Transaction has't paypal attribute
     */
    public function testReadPayPalWithException()
    {
        $transaction = Transaction::factory([
            'id' => 'u38rf8kg6vn'
        ]);
        $this->subjectReader->readPayPal($transaction);
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Helper\SubjectReader::readPayPal
     */
    public function testReadPayPal()
    {
        $paypal = [
            'paymentId' => '3ek7dk7fn0vi1',
            'payerEmail' => 'payer@example.com'
        ];
        $transaction = Transaction::factory([
            'id' => '4yr95vb',
            'paypal' => $paypal
        ]);

        static::assertEquals($paypal, $this->subjectReader->readPayPal($transaction));
    }
}
