<?php

namespace Dotdigitalgroup\ChatGraphQl\Model\Resolver;

use Dotdigitalgroup\Chat\Model\Api\Requests\UpdateProfile;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class UpdateChatProfile implements ResolverInterface
{
    /**
     * @var UpdateProfile
     */
    private $updateProfile;

    /**
     * UpdateChatProfile constructor.
     * @param UpdateProfile $updateProfile
     */
    public function __construct(
        UpdateProfile $updateProfile
    ) {
        $this->updateProfile = $updateProfile;
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
        $profileId = $args['profileId'];

        if (empty($profileId)) {
            throw new GraphQlInputException(__('A profile id must be supplied.'));
        }

        // patch profile if email or first/last names are available
        if (isset($args['email'], $args['firstname'], $args['lastname'])) {
            $this->updateProfile->send($profileId, array_filter([
                'firstName' => $args['firstname'] ?? null,
                'lastName' => $args['lastname'] ?? null,
                'email' => $args['email'] ?? null,
            ]));

            return true;
        }

        return false;
    }
}
