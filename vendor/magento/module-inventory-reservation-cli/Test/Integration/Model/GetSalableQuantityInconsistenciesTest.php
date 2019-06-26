<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Test\Integration\Model;

use Magento\InventoryReservationCli\Model\GetSalableQuantityInconsistencies;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class GetSalableQuantityInconsistenciesTest extends TestCase
{
    /**
     * @var GetSalableQuantityInconsistencies
     */
    private $getSalableQuantityInconsistencies;

    /**
     * Initialize test dependencies
     */
    protected function setUp()
    {
        $this->getSalableQuantityInconsistencies
            = Bootstrap::getObjectManager()->get(GetSalableQuantityInconsistencies::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/create_incomplete_order_with_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testIncompleteOrderWithExistingReservation(): void
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertSame([], $inconsistencies);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/create_incomplete_order_without_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testIncompleteOrderWithoutReservation(): void
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertCount(1, $inconsistencies);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/order_with_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testCompletedOrderWithReservations(): void
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertSame([], $inconsistencies);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_shipping_and_invoice.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_fixtures/broken_reservation.php
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function testCompletedOrderWithMissingReservations(): void
    {
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertCount(1, $inconsistencies);
    }
}
