<?php

namespace Dotdigitalgroup\EmailGraphQl\Model\Resolver;

use Dotdigitalgroup\Email\Helper\Data;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class TrackingData implements ResolverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * TrackingData constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $websiteId = (int)$context->getExtensionAttributes()->getStore()->getWebsiteId();

        if (!$this->helper->isEnabled($websiteId)) {
            return [];
        }

        return [
            'page_tracking_enabled' => $this->helper->isPageTrackingEnabled($websiteId),
            'roi_tracking_enabled' => $this->helper->isRoiTrackingEnabled($websiteId),
            'wbt_profile_id' => $this->helper->getProfileId($websiteId),
            'region_prefix' => $this->helper->getRegionPrefix(),
        ];
    }
}
