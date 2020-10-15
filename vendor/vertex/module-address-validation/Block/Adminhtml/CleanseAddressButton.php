<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Backend\Helper\Data;
use Vertex\AddressValidation\Model\Config;

/**
 * @api
 */
class CleanseAddressButton extends Element
{
    /** @var Data */
    private $backendHelper;

    /** @var Config */
    private $config;

    /** @var null|string */
    private $prefix;

    public function __construct(
        Context $context,
        Config $config,
        Data $backendHelper,
        string $prefix = '',
        array $data = []
    ) {
        $this->config = $config;
        $this->prefix = $prefix;
        $this->backendHelper = $backendHelper;
        parent::__construct($context, $data);
    }

    public function getCleanseAddressButtonBlock(): Button
    {
        return $this->getLayout()->createBlock(Button::class)->setData(
            [
                'label' => __('Validate address'),
                'before_html' => '',
                'data_attribute' => [
                    'role' => 'vertex-cleanse_address',
                ],
            ]
        );
    }

    public function getUpdateAddressButton(): Button
    {
        return $this->getLayout()->createBlock(Button::class)->setData(
            [
                'label' => __('Update address'),
                'before_html' => '',
                'data_attribute' => [
                    'role' => 'vertex-update_address'
                ],
                'style' => 'display:none',
            ]
        );
    }

    public function isAddressValidationEnabled(): bool
    {
        return $this->config->isAddressValidationEnabled();
    }

    /**
     * Retrieve the configuration for the JavaScript widget
     *
     * Available configuration options:
     * - apiUrl: The URL to the Cleanse Address Admin Controller
     * - animationDuration: The milliseconds at which to
     *
     * @param array $config
     * @return array
     */
    public function getDataInit(array $config): array
    {
        return [
            'Vertex_AddressValidation/js/view/cleanse-address-button' => array_merge(
                [
                    'apiUrl' => $this->backendHelper->getUrl('cleanse-address'),
                    'animationDuration' => 200,
                    'prefix' => $this->getPrefix(),
                    'cleanseAddressButtonSelector' => '[data-role="vertex-cleanse_address"]',
                    'updateAddressButtonSelector' => '[data-role="vertex-update_address"]',
                    'validCountryList' => $this->config->getCountriesToValidate(),
                ],
                $config
            ),
        ];
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
