<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Service;

use Vertex\Services\Invoice;
use Vertex\Services\InvoiceFactory as SdkInvoiceFactory;
use Vertex\Tax\Model\Api\ConfigBuilder;
use Vertex\Utility\ServiceActionPerformerFactory;

/**
 * Build an {@see Invoice} service class
 */
class InvoiceBuilder
{
    /** @var ConfigBuilder */
    private $configBuilder;

    /** @var string Scope ID */
    private $scopeCode;

    /** @var string Scope Type */
    private $scopeType;

    /** @var SdkInvoiceFactory */
    private $sdkFactory;

    /** @var ServiceActionPerformerFactory */
    private $serviceActionPerformerFactory;

    /**
     * @param ConfigBuilder $configBuilder
     * @param SdkInvoiceFactory $sdkFactory
     */
    public function __construct(
        ConfigBuilder $configBuilder,
        SdkInvoiceFactory $sdkFactory,
        ServiceActionPerformerFactory $serviceActionPerformerFactory
    ) {
        $this->configBuilder = $configBuilder;
        $this->sdkFactory = $sdkFactory;
        $this->serviceActionPerformerFactory =$serviceActionPerformerFactory;
    }

    /**
     * Create an Invoice Service
     *
     * @return Invoice
     */
    public function build()
    {
        $config = $this->configBuilder
            ->setScopeCode($this->scopeCode)
            ->setScopeType($this->scopeType)
            ->build();

        return $this->sdkFactory->create(
            [
                'configuration' => $config,
                'actionPerformerFactory'  => $this->serviceActionPerformerFactory
            ]
        );
    }

    /**
     * Set the Scope Code
     *
     * @param string|null $storeCode
     * @return InvoiceBuilder
     */
    public function setScopeCode($storeCode)
    {
        $this->scopeCode = $storeCode;
        return $this;
    }

    /**
     * Set the Scope Type
     *
     * @param string|null $scopeType
     * @return InvoiceBuilder
     */
    public function setScopeType($scopeType)
    {
        $this->scopeType = $scopeType;
        return $this;
    }
}
