<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Webservice\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;
use Temando\Shipping\Model\OrderInterface;
use Temando\Shipping\Webservice\Processor\OrderOperation\SaveProcessorInterface;
use Temando\Shipping\Webservice\Response\Type\OrderResponseType;

/**
 * Temando Order Response Processor Pool
 *
 * @package  Temando\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class OrderOperationProcessorPool
{
    /**
     * @var SaveProcessorInterface[]
     */
    private $saveProcessors;

    /**
     * OrderOperationProcessorPool constructor.
     * @param SaveProcessorInterface[] $saveProcessors
     */
    public function __construct(array $saveProcessors = [])
    {
        $this->saveProcessors = $saveProcessors;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param OrderInterface $requestType
     * @param OrderResponseType $responseType
     * @return void
     * @throws LocalizedException
     */
    public function processSaveResponse(
        SalesOrderInterface $salesOrder,
        OrderInterface $requestType,
        OrderResponseType $responseType
    ) {
        foreach ($this->saveProcessors as $processor) {
            $processor->postProcess($salesOrder, $requestType, $responseType);
        }
    }
}
