<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Temando\Shipping\Model\PackagingInterface;
use Temando\Shipping\Model\ResourceModel\Repository\PackagingRepositoryInterface;

/**
 * Temando Packaging Source Model
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Packaging extends AbstractSource
{
    /**
     * @var PackagingRepositoryInterface
     */
    private $packagingRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * Packaging constructor.
     *
     * @param PackagingRepositoryInterface $packagingRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        PackagingRepositoryInterface $packagingRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder
    ) {
        $this->packagingRepository = $packagingRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Retrieve option array
     *
     * @return mixed[]
     */
    public function getAllOptions(): array
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $this->filterBuilder->setField('provider')->setConditionType('eq')->setValue('*');
        $providerFilter = $this->filterBuilder->create();

        $searchCriteriaBuilder->addFilter($providerFilter);
        $searchCriteria = $searchCriteriaBuilder->create();

        $containers = $this->packagingRepository->getList($searchCriteria);
        $options = array_map(function (PackagingInterface $packaging) {
            return [
                'value' => $packaging->getPackagingId(),
                'label' => $packaging->getName(),
            ];
        }, $containers);

        array_unshift($options, ['label' => __('-- Please Select --'), 'value' => '']);

        return $options;
    }
}
