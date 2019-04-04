<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks\CanUseForCountry;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class CountryProvider
{
    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(DirectoryHelper $directoryHelper)
    {
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Get payment country
     *
     * @param Quote $quote
     *
     * @return string
     */
    public function getCountry(Quote $quote)
    {
        /** @var string $country */
        $country = $quote->getBillingAddress()->getCountry() ? :
            $quote->getShippingAddress()->getCountry();

        if (!$country) {
            $country = $this->directoryHelper->getDefaultCountry();
        }

        return $country;
    }
}
