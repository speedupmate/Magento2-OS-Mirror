<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\ViewModel\Attribute\Mapping;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Integration\Model\Oauth\TokenFactory;

/**
 * View model for product attribute mapping.
 *
 * @package Temando\Shipping\ViewModel
 * @author  Nathan Wilson<nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Product implements ArgumentInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * Product constructor.
     * @param UrlInterface $urlBuilder
     * @param TokenFactory $tokenFactory
     * @param Session $session
     */
    public function __construct(
        UrlInterface $urlBuilder,
        TokenFactory $tokenFactory,
        Session $session
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->tokenFactory = $tokenFactory;
        $this->session = $session;
    }

    /**
     * Get the save product attribute mapping form URL.
     *
     * @return string
     */
    public function getSaveProductAttributeMappingUrl(): string
    {
        return $this->urlBuilder->getUrl('temando/attribute_mapping_product/save');
    }

    /**
     * Get the shipping attributes API url.
     *
     * @return string
     */
    public function getShippingAttributesUrl(): string
    {
        return $this->urlBuilder->getDirectUrl(
            'rest/V1/attribute/product/shipping',
            ['_secure' => true]
        );
    }

    /**
     * Get the product attributes API url.
     *
     * @return string
     */
    public function getProductAttributesUrl(): string
    {
        return $this->urlBuilder->getDirectUrl(
            'rest/V1/attribute/product',
            ['_secure' => true]
        );
    }

    /**
     * Get the delete product attribute mapping url.
     *
     * @return string
     */
    public function getDeleteMappingUrl(): string
    {
        return $this->urlBuilder->getDirectUrl(
            'rest/V1/attribute/product/delete',
            ['_secure' => true]
        );
    }

    /**
     * Create an admin user token.
     *
     * @return string
     */
    public function getAdminToken(): string
    {
        $user = $this->session->getUser();
        return $this->tokenFactory->create()->createAdminToken($user->getId())->getToken();
    }
}
