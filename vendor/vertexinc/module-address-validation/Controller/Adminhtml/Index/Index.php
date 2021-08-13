<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Vertex\AddressValidationApi\Api\CleanseAddressInterface;
use Vertex\AddressValidationApi\Api\Data\AddressInterface;
use Vertex\AddressValidationApi\Api\Data\AddressInterfaceFactory;

/**
 * Admin panel bridge for {@see CleanseAddressInterface}
 */
class Index extends Action implements HttpPostActionInterface
{
    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var CleanseAddressInterface */
    private $cleanser;

    public function __construct(
        Context $context,
        CleanseAddressInterface $cleanser,
        AddressInterfaceFactory $addressFactory
    ) {
        parent::__construct($context);
        $this->cleanser = $cleanser;
        $this->addressFactory = $addressFactory;
    }

    public function execute()
    {
        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $params = $this->getRequest()->getParams();

        if (isset($params['city'])) {
            $address->setCity($params['city']);
        }
        if (isset($params['country'])) {
            $address->setCountry($params['country']);
        }
        if (isset($params['main_division'])) {
            $address->setMainDivision($params['main_division']);
        }
        if (isset($params['postal_code'])) {
            $address->setPostalCode($params['postal_code']);
        }
        if (isset($params['street_address'])) {
            $address->setStreetAddress($params['street_address']);
        }

        $cleanAddress = $this->cleanser->cleanseAddress($address);

        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(
            $cleanAddress ? [
                'street_address' => $cleanAddress->getStreetAddress(),
                'city' => $cleanAddress->getCity(),
                'region_id' => $cleanAddress->getRegionId(),
                'region_name' => $cleanAddress->getRegionName(),
                'postal_code' => $cleanAddress->getPostalCode(),
                'sub_division' => $cleanAddress->getSubDivision(),
                'country_code' => $cleanAddress->getCountryCode(),
                'country_name' => $cleanAddress->getCountryName(),
            ] : null
        );
        return $result;
    }
}
