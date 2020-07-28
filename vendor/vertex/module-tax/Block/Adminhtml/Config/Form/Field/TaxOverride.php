<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Exception\LocalizedException;

/**
 * Backend TaxOverride system config field renderer
 */
class TaxOverride extends AbstractFieldArray
{
    /** @var Countries */
    private $countriesRenderer;

    /** @var DeliveryTerms */
    private $deliverTermsRenderer;

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'country_id',
            [
                'label' => __('Country'),
                'renderer' => $this->getCountriesRenderer(),
            ]
        );
        $this->addColumn(
            'delivery_term',
            [
                'label' => __('Delivery Term'),
                'renderer' => $this->getDeliveryTermsRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @inheritdoc
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $countryId = $row->getData('country_id');
        $deliveryTerm = $row->getData('delivery_term');
        $options = [];
        if ($countryId) {
            $name = 'option_'.$this->getCountriesRenderer()->calcOptionHash($countryId);
            $options[$name] = 'selected="selected"';
        }
        if ($deliveryTerm) {
            $name = 'option_'.$this->getDeliveryTermsRenderer()->calcOptionHash($deliveryTerm);
            $options[$name] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Retrieve countries renderer
     *
     * @return Countries
     * @throws LocalizedException
     */
    private function getCountriesRenderer()
    {
        if (!$this->countriesRenderer) {
            $this->countriesRenderer = $this->getLayout()->createBlock(
                Countries::class,
                '',
                [
                    'data' => [
                        'is_render_to_js_template' => true,
                        'class' => 'input-text required-entry validate-no-empty',
                    ],
                ]
            );
        }

        return $this->countriesRenderer;
    }

    /**
     * Retrieve Delivery Terms Renderer
     *
     * @return DeliveryTerms
     * @throws LocalizedException
     */
    private function getDeliveryTermsRenderer()
    {
        if (!$this->deliverTermsRenderer) {
            $this->deliverTermsRenderer = $this->getLayout()->createBlock(
                DeliveryTerms::class,
                '',
                [
                    'data' => [
                        'is_render_to_js_template' => true,
                        'class' => 'input-text required-entry validate-no-empty',
                    ],
                ]
            );
        }

        return $this->deliverTermsRenderer;
    }
}
