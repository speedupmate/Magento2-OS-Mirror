<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Shipping\RateResult;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

/**
 * Temando Rate Result Free Shipping Handler.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class FreeShipping
{
    /**
     * Apply free shipping as configured via cart price rules.
     *
     * For now we only consider the `free_shipping` flag on the rate request itself.
     * Under certain conditions, the quote items also have a `free_shipping`
     * property (boolean flag or number). As the free shipping information on
     * item level is not handled consistently in core carriers and there is no
     * documentation available how it should be handled by 3rd party carriers,
     * we cannot reliably process it.
     *
     * @param RateRequest $rateRequest
     * @param Result $rateResult
     */
    public function apply(RateRequest $rateRequest, Result $rateResult)
    {
        if (!$rateRequest->getFreeShipping()) {
            return;
        }

        foreach ($rateResult->getAllRates() as $method) {
            $method->setPrice(0);
        }
    }
}
