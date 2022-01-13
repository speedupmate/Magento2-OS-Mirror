<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\ViewModel;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vertex\AddressValidation\Model\Config;

class CustomerAddressValidation implements ArgumentInterface
{
    /** @var Config */
    private $config;

    /** @var SerializerInterface */
    private $serializer;

    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        Config $config,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    public function getConfig() : array
    {
        return [
            'enabled'            => $this->config->isAddressValidationEnabled(),
            'storeCode'          => $this->storeManager->getStore()->getCode(),
            'showSuccessMessage' => $this->config->showValidationSuccessMessage(),
            'countryValidation'  => $this->config->getCountriesToValidate(),
            'validateButtonText' => __('Validate &amp; Save'),
            'saveAsIsButtonText' => __('Save As Is'),
            'updateButtonText'   => __('Update &amp; Save')
        ];
    }

    public function getSerializedConfig() : string
    {
        return $this->serializer->serialize($this->getConfig());
    }
}
