<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Exception;
use Magento\Store\Model\ScopeInterface;
use Vertex\Tax\Api\QuoteInterface;
use Vertex\Tax\Model\ConfigurationValidator\ValidSampleRequestBuilder;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * pre Load WSDL.
 */
class WsdlCache
{
    /** @var Config */
    private $config;

    /** @var ExceptionLogger */
    private $logger;

    /** @var QuoteInterface */
    private $quote;

    /** @var ValidSampleRequestBuilder */
    private $sampleRequestFactory;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    public function __construct(
        Config $config,
        ExceptionLogger $logger,
        QuoteInterface $quote,
        ValidSampleRequestBuilder $sampleRequestFactory,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->quote = $quote;
        $this->sampleRequestFactory = $sampleRequestFactory;
        $this->websiteRepository = $websiteRepository;
    }

    public function load() :void
    {
        $scopeType = ScopeInterface::SCOPE_WEBSITE;
        $websites = $this->websiteRepository->getList();

        try {

            foreach ($websites as $website) {
                $websiteId = $website->getId();

                if (!$this->config->isVertexActive($websiteId, $scopeType)
                    || !$this->config->isTaxCalculationEnabled($websiteId, $scopeType)
                ) {
                    continue;
                }

                $request = $this->sampleRequestFactory
                    ->setScopeType($scopeType)
                    ->setScopeCode($websiteId)
                    ->build();

                $this->quote->request($request, $websiteId, $scopeType);
            }

        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }
}
