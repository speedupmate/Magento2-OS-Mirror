<?php
/**
 * This file is part of the Klarna KpGraphQl module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Plugin\Model\Api\Rest;

use Klarna\Core\Model\Api\Rest\Service;
use Magento\Framework\App\RequestInterface;

class ServicePlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @codeCoverageIgnore
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Extends version info with graphql module version if graphql flag is set in request
     *
     * @param Service $subject
     * @param string $product
     * @param string $version
     * @param string $mageInfo
     * @return array
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function beforeSetUserAgent(Service $subject, $product, $version, $mageInfo): array
    {
        if ($this->request->getParam('GraphQlCreateSession')) {
            $version = $version . ';GraphQlCreateSession';
        }

        return [$product, $version, $mageInfo];
    }
}
