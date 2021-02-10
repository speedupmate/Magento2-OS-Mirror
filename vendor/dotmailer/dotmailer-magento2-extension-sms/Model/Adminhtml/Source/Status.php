<?php

namespace Dotdigitalgroup\Sms\Model\Adminhtml\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '0',
                'label' => __('Pending'),
            ],
            [
                'value' => '1',
                'label' => __('In progress'),
            ],
            [
                'value' => '2',
                'label' => __('Delivered'),
            ],
            [
                'value' => '3',
                'label' => __('Failed'),
            ],
            [
                'value' => '4',
                'label' => __('Expired'),
            ],
            [
                'value' => '5',
                'label' => __('Unknown'),
            ]
        ];
    }
}
