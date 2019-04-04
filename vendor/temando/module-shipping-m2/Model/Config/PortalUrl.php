<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Config;

use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\PackageInfo;
use Temando\Shipping\Rest\AuthenticationInterface;

/**
 * Portal URL provider
 *
 * @package  Temando\Shipping\Model
 * @author   Nathan Wilson <nathan.wilson@temando.com>
 * @license  https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.temando.com/
 */
class PortalUrl
{
    /**
     * @var AuthenticationInterface
     */
    private $auth;

    /**
     * @var ModuleConfig
     */
    private $config;

    /**
     * @var ProductMetadata
     */
    private $metadata;

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * PortalUrl constructor.
     * @param AuthenticationInterface $auth
     * @param ModuleConfig $config
     * @param ProductMetadata $metadata
     * @param PackageInfo $packageInfo
     */
    public function __construct(
        AuthenticationInterface $auth,
        ModuleConfig $config,
        ProductMetadata $metadata,
        PackageInfo $packageInfo
    ) {
        $this->auth = $auth;
        $this->config = $config;
        $this->metadata = $metadata;
        $this->packageInfo = $packageInfo;
    }

    /**
     * Collect platform and account details.
     *
     * @return string[]
     * @throws LocalizedException
     */
    private function getQueryParams(): array
    {
        $bearerToken = $this->config->getBearerToken();
        $accountId = $this->config->getAccountId();

        $this->auth->connect($accountId, $bearerToken);

        $queryParams = [
            'platform' => [
                'name' => $this->metadata->getName(),
                'version' => $this->metadata->getVersion(),
                'extensionName' => 'Temando_Shipping',
                'extensionVersion' => $this->packageInfo->getVersion('Temando_Shipping'),
            ],
            '[session]accountId' => $accountId,
            '[session]apiUrl' => $this->config->getApiEndpoint(),
            '[session]token' => $this->auth->getSessionToken(),
            '[session]expiry' => $this->auth->getSessionTokenExpiry()
        ];

        return $queryParams;
    }

    /**
     * Obtain the Shipping Portal URL, account section.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getAccountUrl(): string
    {
        $portalUrl = $this->config->getShippingPortalUrl();
        $queryParams = $this->getQueryParams();

        $portalUrl = sprintf('%s%s?%s', $portalUrl, 'account', http_build_query($queryParams));

        return $portalUrl;
    }

    /**
     * Obtain the Shipping Portal URL, shipping experiences section.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getExperiencesUrl(): string
    {
        $portalUrl = $this->config->getShippingPortalUrl();
        $queryParams = $this->getQueryParams();

        $portalUrl = sprintf('%s%s?%s', $portalUrl, 'shipping-experiences', http_build_query($queryParams));

        return $portalUrl;
    }
}
