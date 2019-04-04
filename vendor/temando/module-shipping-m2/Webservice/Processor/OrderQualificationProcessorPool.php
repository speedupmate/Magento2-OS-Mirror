<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Webservice\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Temando\Shipping\Api\Data\Order\ShippingExperienceInterface;
use Temando\Shipping\Model\OrderInterface;
use Temando\Shipping\Webservice\Processor\OrderOperation\RatesProcessorInterface;
use Temando\Shipping\Webservice\Response\Type\QualificationResponseType;

/**
 * Temando Order Qualification Response Processor Pool
 *
 * @package Temando\Shipping\Webservice
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderQualificationProcessorPool
{
    /**
     * @var RatesProcessorInterface[]
     */
    private $ratesProcessors;

    /**
     * OrderOperationProcessorPool constructor.
     * @param RatesProcessorInterface[] $ratesProcessors
     */
    public function __construct(array $ratesProcessors = [])
    {
        $this->ratesProcessors = $ratesProcessors;
    }

    /**
     * @param RateRequest $rateRequest
     * @param OrderInterface $requestType
     * @param QualificationResponseType $responseType
     * @return ShippingExperienceInterface[]
     * @throws LocalizedException
     */
    public function processRatesResponse(
        RateRequest $rateRequest,
        OrderInterface $requestType,
        QualificationResponseType $responseType
    ) {
        $rates = [];

        foreach ($this->ratesProcessors as $processor) {
            $processorRates = $processor->postProcess($rateRequest, $requestType, $responseType);
            $rates = array_merge($rates, $processorRates);
        }

        return $rates;
    }
}
