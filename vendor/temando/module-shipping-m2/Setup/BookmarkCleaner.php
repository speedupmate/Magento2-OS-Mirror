<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Setup;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Ui\Api\BookmarkRepositoryInterface;

/**
 * Utility for removing outdated UI Bookmark entries after UI component changes.
 *
 * @package Temando\Shipping\Setup
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class BookmarkCleaner
{
    /**
     * @var BookmarkRepositoryInterface
     */
    private $bookmarkRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * BookmarkCleaner constructor.
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        BookmarkRepositoryInterface $bookmarkRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->bookmarkRepository = $bookmarkRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Clean up the current pickup listing bookmark.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resetPickupGrid()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $namespaceFilter = $this->filterBuilder
            ->setField('namespace')
            ->setConditionType('eq')
            ->setValue('temando_pickup_listing')
            ->create();

        $searchCriteriaBuilder->addFilter($namespaceFilter);

        $searchCriteria = $searchCriteriaBuilder->create();
        $searchResult = $this->bookmarkRepository->getList($searchCriteria);

        foreach ($searchResult->getItems() as $bookmark) {
            $this->bookmarkRepository->delete($bookmark);
        }
    }

    /**
     * Clean up the current order pickup listing bookmark.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resetOrderPickupGrid()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $namespaceFilter = $this->filterBuilder
            ->setField('namespace')
            ->setConditionType('eq')
            ->setValue('sales_order_pickup_listing')
            ->create();

        $searchCriteriaBuilder->addFilter($namespaceFilter);

        $searchCriteria = $searchCriteriaBuilder->create();
        $searchResult = $this->bookmarkRepository->getList($searchCriteria);

        foreach ($searchResult->getItems() as $bookmark) {
            $this->bookmarkRepository->delete($bookmark);
        }
    }
}
