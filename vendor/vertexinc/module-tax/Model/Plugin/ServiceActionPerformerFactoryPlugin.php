<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Framework\ObjectManagerInterface;
use Vertex\Utility\ServiceActionPerformer;
use Vertex\Utility\ServiceActionPerformerFactory;
use Vertex\Utility\SoapClientFactory;

/**
 * Replaces a Vertex SDK Factory with Magento 2 dependency injection
 *
 * @see ServiceActionPerformerFactory
 */
class ServiceActionPerformerFactoryPlugin
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Change Vertex SDK's Factory to utilize ObjectManager
     *
     * We don't prevent the call to the original so we preserve it's checks and exceptions
     *
     * @see ServiceActionPerformerFactory::create()
     */
    public function afterCreate(
        ServiceActionPerformerFactory $subject,
        ServiceActionPerformer $performer,
        array $parameters
    ): ServiceActionPerformer {
        if (!isset($parameters['soapClientFactory'])) {
            // This is necessary to ensure that the plugins for the SoapClientFactory are utilized
            $parameters['soapClientFactory'] = $this->objectManager->get(SoapClientFactory::class);
        }

        return $this->objectManager->create(ServiceActionPerformer::class, $parameters);
    }
}
