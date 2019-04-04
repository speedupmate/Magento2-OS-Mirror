<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model\Files;

interface TestDataInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @return string
     */
    public function isDefaultShipping();

    /**
     * @return string
     */
    public function isRequiredBilling();
}
