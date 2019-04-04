<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Exception;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Temando\Shipping\Rest\Response\DataObject\Error;
use Temando\Shipping\Rest\Response\Document\Errors;

/**
 * Temando Adapter Exception Component Test
 *
 * @package Temando\Shipping\Test\Unit
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class AdapterExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Test setup
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        parent::setUp();
    }

    /**
     * Provide different input with expectations.
     *
     * @return mixed[]
     */
    public function exceptionMessageDataProvider()
    {
        $status = '400';
        $title = 'General error';
        $code = 'general-error';
        $detail = 'Detailed error';

        $errorBoth = new Error();
        $errorBoth->setStatus($status);
        $errorBoth->setTitle($title);
        $errorBoth->setCode($code);
        $errorBoth->setDetail($detail);

        $errorNoDetail = new Error();
        $errorNoDetail->setStatus($status);
        $errorNoDetail->setTitle($title);
        $errorNoDetail->setCode($code);

        $errorNoTitle = new Error();
        $errorNoTitle->setStatus($status);
        $errorNoTitle->setCode($code);
        $errorNoTitle->setDetail($detail);

        return [
            'both' => [
                $errorBoth,
                "$code: $detail"
            ],
            'no-detail' => [
                $errorNoDetail,
                "$code: $title"
            ],
            'no-title' => [
                $errorNoTitle,
                "$code: $detail"
            ]
        ];
    }

    /**
     * Assert exception message contains error details.
     *
     * @test
     * @dataProvider exceptionMessageDataProvider
     *
     * @param Error $error
     * @param string $expected
     */
    public function extractMessageFromException($error, $expected)
    {
        /** @var Errors $errors */
        $errors = $this->objectManager->getObject(Errors::class);

        $errors->setErrors([$error]);
        $exception = AdapterException::errorResponse($errors);

        self::assertSame($expected, $exception->getMessage());
    }
}
