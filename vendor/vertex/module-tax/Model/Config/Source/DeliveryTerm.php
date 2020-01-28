<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vertex\Data\DeliveryTerm as SdkDeliveryTerm;

/**
 * Contains options for delivery terms
 */
class DeliveryTerm implements OptionSourceInterface
{
    /**
     * Retrieve delivery term options as an array formatted for select dropdowns
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => SdkDeliveryTerm::CFR,
                'label' => __('%1 - Cost and Freight', SdkDeliveryTerm::CFR)
            ],
            [
                'value' => SdkDeliveryTerm::CIF,
                'label' => __('%1 - Cost Insurance and Freight', SdkDeliveryTerm::CIF)
            ],
            [
                'value' => SdkDeliveryTerm::CIP,
                'label' => __('%1 - Carriage Insurance Paid To', SdkDeliveryTerm::CIP)
            ],
            [
                'value' => SdkDeliveryTerm::CPT,
                'label' => __('%1 - Carriage Paid To', SdkDeliveryTerm::CPT)
            ],
            [
                'value' => SdkDeliveryTerm::CUS,
                'label' => __('%1 - Customer Ships', SdkDeliveryTerm::CUS)
            ],
            [
                'value' => SdkDeliveryTerm::DAF,
                'label' => __('%1 - Delivered at Frontier', SdkDeliveryTerm::DAF)
            ],
            [
                'value' => SdkDeliveryTerm::DAP,
                'label' => __('%1 - Delivered at Place', SdkDeliveryTerm::DAP)
            ],
            [
                'value' => SdkDeliveryTerm::DAT,
                'label' => __('%1 - Delivered at Terminal', SdkDeliveryTerm::DAT)
            ],
            [
                'value' => SdkDeliveryTerm::DDP,
                'label' => __('%1 - Delivery Duty Paid', SdkDeliveryTerm::DDP)
            ],
            [
                'value' => SdkDeliveryTerm::DDU,
                'label' => __('%1 - Delivery Duty Unpaid', SdkDeliveryTerm::DDU)
            ],
            [
                'value' => SdkDeliveryTerm::DEQ,
                'label' => __('%1 - Delivered Ex Quay Duty Unpaid', SdkDeliveryTerm::DEQ)
            ],
            [
                'value' => SdkDeliveryTerm::DES,
                'label' => __('%1 - Delivered Ex Quay Duty Unpaid', SdkDeliveryTerm::DES)
            ],
            [
                'value' => SdkDeliveryTerm::EXW,
                'label' => __('%1 - Ex Works', SdkDeliveryTerm::EXW)
            ],
            [
                'value' => SdkDeliveryTerm::FAS,
                'label' => __('%1 - Free Along Side Ship', SdkDeliveryTerm::FAS)
            ],
            [
                'value' => SdkDeliveryTerm::FCA,
                'label' => __('%1 - Free Carrier', SdkDeliveryTerm::FCA)
            ],
            [
                'value' => SdkDeliveryTerm::FOB,
                'label' => __('%1 - Free Onboard Vessel', SdkDeliveryTerm::FOB)
            ],
            [
                'value' => SdkDeliveryTerm::SUP,
                'label' => __('%1 - Supplier Ships', SdkDeliveryTerm::SUP)
            ],
        ];
    }
}
