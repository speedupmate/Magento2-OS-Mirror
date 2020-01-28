<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Plugin;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Repository\CustomerCodeRepository;

/**
 * Ensures the Vertex Customer Code is available in the Customer Admin Form
 *
 * @see DataProvider
 */
class CustomerDataProviderPlugin
{
    /** @var Config */
    private $config;

    /** @var CustomerCodeRepository */
    private $repository;

    /**
     * @param CustomerCodeRepository $repository
     * @param Config $config
     */
    public function __construct(CustomerCodeRepository $repository, Config $config)
    {
        $this->repository = $repository;
        $this->config = $config;
    }

    /**
     * Load the Vertex Customer Code into the Customer Data Provider for use in the Admin form
     *
     * @see DataProvider::getData() Intercepted method
     * @param DataProvider $subject
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(AbstractDataProvider $subject, $data)
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
        $customerCodes = $this->repository->getListByCustomerIds($customerIds);
        foreach ($data as $dataKey => $fieldData) {
            if (!isset($fieldData['customer']['entity_id'], $customerCodes[$fieldData['customer']['entity_id']])) {
                continue;
            }
            $entityId = $fieldData['customer']['entity_id'];
            $customerCode = $customerCodes[$entityId]->getCustomerCode();
            $data[$dataKey]['customer']['extension_attributes']['vertex_customer_code'] = $customerCode;
        }

        return $data;
    }
}
