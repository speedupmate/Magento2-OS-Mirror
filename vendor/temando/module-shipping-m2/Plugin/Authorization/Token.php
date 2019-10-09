<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Plugin\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Plugin around \Magento\Framework\Authorization::isAllowed
 * Plugin to allow admin users to access resources using a token
 *
 * @package Temando\Shipping\Controller
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Token
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * Inject dependencies.
     *
     * @param UserContextInterface $userContext
     */
    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * Check if resource for which access is needed has token permissions defined in webapi config.
     *
     * @param \Magento\Framework\Authorization $subject
     * @param \Closure $proceed
     * @param string $resource
     * @param string $privilege
     *
     * @return bool true if resource permission is token and valid bearer token is sent
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsAllowed(
        \Magento\Framework\Authorization $subject,
        \Closure $proceed,
        $resource,
        $privilege = null
    ) {
        if ($resource === 'token'
            && $this->userContext->getUserId()
            && $this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN) {
            return true;
        }

        return $proceed($resource, $privilege);
    }
}
