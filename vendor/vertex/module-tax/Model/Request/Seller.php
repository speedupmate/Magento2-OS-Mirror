<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Request;

use Vertex\Tax\Model\Config;

/**
 * Seller Data formatter for Vertex API Calls
 */
class Seller
{
    /** @var Config */
    private $config;

    /** @var Address */
    private $addressFormatter;

    /**
     * @param Config $config
     * @param Address $addressFormatter
     */
    public function __construct(
        Config $config,
        Address $addressFormatter
    ) {
        $this->addressFormatter = $addressFormatter;
        $this->config = $config;
    }

    /**
     * Create properly formatted Seller Data for use with the Vertex API
     *
     * Created from store configuration
     *
     * @param string|null $store
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFormattedSellerData($store = null)
    {
        $data = [];

        $address = $this->addressFormatter->getFormattedAddressData(
            [
                $this->config->getCompanyStreet1($store),
                $this->config->getCompanyStreet2($store)
            ],
            $this->config->getCompanyCity($store),
            $this->config->getCompanyRegionId($store),
            $this->config->getCompanyPostalCode($store),
            $this->config->getCompanyCountry($store)
        );

        $data['Company'] = $this->config->getCompanyCode();
        $data['PhysicalOrigin'] = $address;

        return $data;
    }
}
