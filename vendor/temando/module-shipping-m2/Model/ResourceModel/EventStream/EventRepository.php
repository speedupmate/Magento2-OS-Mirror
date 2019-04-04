<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\EventStream;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Rest\Adapter\EventStreamApiInterface;
use Temando\Shipping\Rest\EntityMapper\StreamEventResponseMapper;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\ListRequestInterfaceFactory;
use Temando\Shipping\Rest\Request\StreamEventItemRequestFactory;
use Temando\Shipping\Rest\Response\DataObject\StreamEvent;
use Temando\Shipping\Webservice\Pagination\PaginationFactory;

/**
 * Temando Event Stream Repository
 *
 * @package Temando\Shipping\Model
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class EventRepository implements EventRepositoryInterface
{
    /**
     * @var EventStreamApiInterface
     */
    private $apiAdapter;

    /**
     * @var PaginationFactory
     */
    private $paginationFactory;

    /**
     * @var ListRequestInterfaceFactory
     */
    private $listRequestFactory;

    /**
     * @var StreamEventItemRequestFactory
     */
    private $itemRequestFactory;

    /**
     * @var StreamEventResponseMapper
     */
    private $streamEventMapper;

    /**
     * StreamEventRepository constructor.
     *
     * @param EventStreamApiInterface $apiAdapter
     * @param StreamEventItemRequestFactory $itemRequestFactory
     * @param ListRequestInterfaceFactory $listRequestFactory
     * @param StreamEventResponseMapper $streamEventMapper
     */
    public function __construct(
        EventStreamApiInterface $apiAdapter,
        StreamEventItemRequestFactory $itemRequestFactory,
        ListRequestInterfaceFactory $listRequestFactory,
        StreamEventResponseMapper $streamEventMapper
    ) {
        $this->apiAdapter = $apiAdapter;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->listRequestFactory = $listRequestFactory;
        $this->streamEventMapper = $streamEventMapper;
    }

    /**
     * @param string $streamId
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return \Temando\Shipping\Model\StreamEventInterface[]
     * @throws LocalizedException
     */
    public function getEventList($streamId, $offset = null, $limit = null)
    {
        try {
            $pagination = $this->paginationFactory->create([
                'offset' => $offset,
                'limit' => $limit,
            ]);

            $request = $this->listRequestFactory->create([
                'parentId' => $streamId,
                'pagination' => $pagination,
            ]);

            // convert api response to local (reduced) event objects
            $apiStreamEvents = $this->apiAdapter->getStreamEvents($request);
            $streamEvents = array_map(function (StreamEvent $apiEvent) {
                return $this->streamEventMapper->map($apiEvent);
            }, $apiStreamEvents);
        } catch (AdapterException $e) {
            throw new LocalizedException(__('Unable to load stream events.'), $e);
        }

        return $streamEvents;
    }

    /**
     * @param string $streamId
     * @param string $eventId
     *
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete($streamId, $eventId)
    {
        try {
            $request = $this->itemRequestFactory->create([
                'streamId' => $streamId,
                'entityId' => $eventId,
            ]);
            $this->apiAdapter->deleteStreamEvent($request);
        } catch (AdapterException $e) {
            throw new CouldNotDeleteException(__('Unable to delete stream event.'), $e);
        }
    }
}
