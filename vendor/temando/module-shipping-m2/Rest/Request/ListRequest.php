<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request;

use Temando\Shipping\Webservice\Filter\CollectionFilterInterface;
use Temando\Shipping\Webservice\Pagination\PaginationInterface;

/**
 * Temando API Item Listing Operation
 *
 * @package  Temando\Shipping\Rest
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.temando.com/
 */
class ListRequest implements ListRequestInterface
{
    /**
     * The list's parent entity, e.g. /book/{parentId}/chapters.
     *
     * @var string
     */
    private $parentId;

    /**
     * Pagination parameters.
     *
     * @var PaginationInterface
     */
    private $pagination;

    /**
     * Limit and offset parameters.
     *
     * @var CollectionFilterInterface
     */
    private $filter;

    /**
     * ListRequest constructor.
     * @param string $parentId
     * @param PaginationInterface $pagination
     * @param CollectionFilterInterface $filter
     */
    public function __construct(
        $parentId = '',
        PaginationInterface $pagination = null,
        CollectionFilterInterface $filter = null
    ) {
        $this->parentId = $parentId;
        $this->pagination = $pagination;
        $this->filter = $filter;
    }

    /**
     * @return string[]
     */
    public function getPathParams()
    {
        return $this->parentId ? [$this->parentId] : [];
    }

    /**
     * Retrieve query parameters for listings.
     *
     * @return string[]
     */
    public function getRequestParams()
    {
        if ($this->pagination instanceof PaginationInterface) {
            $pageParams = $this->pagination->getPageParams();
        } else {
            $pageParams = [];
        }

        if ($this->filter instanceof CollectionFilterInterface) {
            $filterParams = $this->filter->getFilters();
        } else {
            $filterParams = [];
        }

        $requestParams = array_merge($pageParams, $filterParams);
        return $requestParams;
    }
}
