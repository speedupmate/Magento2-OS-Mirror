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

namespace Klarna\KpGraphQl\Model\Resolver;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kp\Model\Session;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Resolver for generating Klarna Payments session
 */
class CreateKlarnaPaymentsSession implements ResolverInterface
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var ConfigHelper
     */
    private $configHelper;
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @param ConfigHelper   $configHelper
     * @param Session        $session
     * @param GetCartForUser $getCartForUser
     * @codeCoverageIgnore
     */
    public function __construct(
        ConfigHelper $configHelper,
        Session $session,
        GetCartForUser $getCartForUser
    ) {
        $this->configHelper   = $configHelper;
        $this->session        = $session;
        $this->getCartForUser = $getCartForUser;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $maskedCartId = $args['input']['cart_id'];
        $storeId      = (int)$context->getExtensionAttributes()->getStore()->getId();
        $this->validate($maskedCartId, $storeId);

        $currentUserId = $context->getUserId();
        $cart          = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        $response      = $this->session->initWithCartId((string)$cart->getId(), $currentUserId);

        if (!$response->isSuccessfull()) {
            throw new GraphQlInputException(__($response->getResponseStatusMessage()));
        }

        return [
            'client_token'              => $response->getClientToken(),
            'payment_method_categories' => $response->getPaymentMethodCategories()
        ];
    }

    /**
     * Validation if any cart_id was provided and Klarna Payments is enabled.
     *
     * @param string $maskedCartId
     * @param int    $storeId
     * @throws GraphQlInputException
     */
    private function validate(string $maskedCartId, int $storeId): void
    {
        if (!$maskedCartId) {
            throw new GraphQlInputException(__("Required parameter '%1' is missing", 'cart_id'));
        }
        if (!$this->configHelper->isPaymentConfigFlag('active', $storeId)) {
            throw new GraphQlInputException(__("Klarna Payments method is not active"));
        }
    }
}
