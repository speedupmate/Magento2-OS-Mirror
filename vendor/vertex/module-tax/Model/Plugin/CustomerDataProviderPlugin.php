<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Repository\CustomerCodeRepository;
use Vertex\Tax\Model\Repository\CustomerCountryRepository;
use Vertex\Tax\Model\ResourceModel\Country\CollectionFactory;

/**
 * Ensures the Vertex Customer Code is available in the Customer Admin Form
 *
 * @see DataProvider
 */
class CustomerDataProviderPlugin
{
    /** @var ArrayManager */
    private $arrayManager;

    /** @var Config */
    private $config;

    /** @var CustomerCodeRepository */
    private $customerCodeRepository;

    /** @var CustomerCountryRepository */
    private $customerCountryRepository;

    /** @var CollectionFactory */
    private $countryCollectionFactory;

    /**
     * @param CustomerCodeRepository $customerCodeRepository
     * @param CustomerCountryRepository $customerCountryRepository
     * @param Config $config
     * @param CollectionFactory $countryCollectionFactory
     */
    public function __construct(
        CustomerCodeRepository $customerCodeRepository,
        CustomerCountryRepository $customerCountryRepository,
        Config $config,
        CollectionFactory $countryCollectionFactory,
        ArrayManager $arrayManager
    ) {
        $this->customerCodeRepository = $customerCodeRepository;
        $this->customerCountryRepository = $customerCountryRepository;
        $this->config = $config;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Load the Vertex Customer Code into the Customer Data Provider for use in the Admin form
     *
     * @param DataProvider $subject
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see DataProvider::getData() Intercepted method
     */
    public function afterGetData(AbstractDataProvider $subject, $data): array
    {
        if (empty($data) || !$this->config->isVertexActive()) {
            return $data;
        }
        $customerIds = [];
        foreach ($data as $fieldData) {
            if (!isset($fieldData['customer']['entity_id'])) {
                continue;
            }
            $customerIds[] = $fieldData['customer']['entity_id'];
        }
        $customerCodes = $this->customerCodeRepository->getListByCustomerIds($customerIds);
        $customerCountries = $this->customerCountryRepository->getListByCustomerIds($customerIds);
        foreach ($data as $dataKey => $fieldData) {
            if (!isset($fieldData['customer']['entity_id'], $customerCodes[$fieldData['customer']['entity_id']])) {
                continue;
            }
            $entityId = $fieldData['customer']['entity_id'];
            $customerCode = $customerCodes[$entityId]->getCustomerCode();
            $customerCountry = $customerCountries[$entityId]->getCustomerCountry();
            $data[$dataKey]['customer']['extension_attributes']['vertex_customer_code'] = $customerCode;
            $data[$dataKey]['customer']['extension_attributes']['vertex_customer_country'] = $customerCountry;
        }

        return $data;
    }

    /**
     * Show Vertex custom fields only if extension is enabled
     *
     * @param AbstractDataProvider $subject
     * @param array $meta
     * @return mixed
     * @see DataProvider::getMeta() Intercepted method
     */
    public function afterGetMeta(AbstractDataProvider $subject, $meta): array
    {
        if (!$this->config->isVertexActive()) {
            return $meta;
        }

        $fields = $this->getFields();

        $vatSortOrderPath = 'customer/children/taxvat/arguments/data/config/sortOrder';

        foreach ($fields as $key => $field) {
            if ($key === 'vertex_customer_country' && $this->arrayManager->exists($vatSortOrderPath, $meta)) {
                $field = $this->arrayManager->set(
                    'arguments/data/config/sortOrder',
                    $field,
                    $this->arrayManager->get($vatSortOrderPath, $meta, 0) + 1
                );
            }
            $meta = $this->arrayManager->set('customer/children/'.$key, $meta, $field);
        }

        return $meta;
    }

    /**
     * Create new children's field structure
     *
     * @return array
     */
    private function getFields()
    {
        $newFields = $this->createNewFields();
        $formFields = [];

        foreach ($newFields as $key => $field) {
            $formFields[$key] = $this->arrayManager->set('arguments/data/config', [], $field);
        }

        return $formFields;
    }

    /**
     * Create new fields
     *
     * @return array
     */
    private function createNewFields()
    {
        $countryCollection = $this->countryCollectionFactory->create();
        $fields = [
            'vertex_customer_code' => [
                'label' => __('Vertex Customer Code'),
                'dataType' => 'text',
                'formElement' => 'input',
                'dataScope' => 'extension_attributes.vertex_customer_code',
                'componentType' => 'field'
            ],
            'vertex_customer_country' => [
                'label' => __('VAT Registration Country'),
                'dataType' => 'text',
                'formElement' => 'select',
                'options' => $countryCollection->toOptionArray(),
                'component' => 'Vertex_Tax/js/form/element/customer-country-validation',
                'dependField' => 'taxvat',
                'dataScope' => 'extension_attributes.vertex_customer_country',
                'validation' => [
                    'vertex-customer-country' => 1
                ],
                'componentType' => 'field',
            ]
        ];

        return $fields;
    }
}
