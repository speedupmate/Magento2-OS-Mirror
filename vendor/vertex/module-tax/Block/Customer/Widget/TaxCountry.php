<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Block\Customer\Widget;

use Magento\Customer\Block\Account\Dashboard;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Newsletter\Model\SubscriberFactory;
use Vertex\Tax\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Vertex\Tax\Model\ResourceModel\Country\Collection as CountryCollection;

/**
 * Handles the tax country field on frontend
 *
 * @api
 */
class TaxCountry extends Dashboard
{
    /** @var CountryCollectionFactory */
    private $countryCollectionFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        CountryCollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
    }

    /**
     * Retrieves an array of countries
     *
     * @return array
     */
    public function getCountryOptions(): array
    {
        /** @var $collection CountryCollection */
        $collection = $this->countryCollectionFactory->create()->loadByStore();

        return $collection->toOptionArray();
    }

    /**
     * Return the customer tax country value, if customer is logged in
     *
     * @return string|null
     */
    public function getTaxCountry(): ?string
    {
        $vertexCustomerCountry = null;
        if ($this->customerSession->getId()) {
            $vertexCustomerCountry = $this->getCustomer()->getExtensionAttributes()->getVertexCustomerCountry();
        }

        return $vertexCustomerCountry;
    }
}
