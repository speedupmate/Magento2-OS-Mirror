<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Observer;

use Magento\Customer\Model\ResourceModel\Customer\Grid as CustomerGrid;

/**
 * @deprecated
 */
class Grid
{
    /**
     * @var CustomerGrid
     */
    protected $customerGrid;

    /**
     * @param CustomerGrid $grid
     */
    public function __construct(
        CustomerGrid $grid
    ) {
        $this->customerGrid = $grid;
    }

    /**
     * @return void
     *
     * @deprecated
     */
    public function syncCustomerGrid()
    {
        $this->customerGrid->syncCustomerGrid();
    }
}
