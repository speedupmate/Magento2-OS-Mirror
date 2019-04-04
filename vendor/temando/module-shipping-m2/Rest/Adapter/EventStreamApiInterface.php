<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Rest\Adapter;

use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\ItemRequestInterface;
use Temando\Shipping\Rest\Request\ListRequestInterface;
use Temando\Shipping\Rest\Request\StreamCreateRequestInterface;
use Temando\Shipping\Rest\Request\StreamEventItemRequest;
use Temando\Shipping\Rest\Response\DataObject\StreamEvent;

/**
 * Temando API Adapter Event Stream Part
 *
 * @package  Temando\Shipping\Rest
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
interface EventStreamApiInterface
{
    /**
     * @param StreamCreateRequestInterface $request
     *
     * @return void
     * @throws AdapterException
     */
    public function createStream(StreamCreateRequestInterface $request);

    /**
     * @param ItemRequestInterface $request
     *
     * @return void
     * @throws AdapterException
     */
    public function deleteStream(ItemRequestInterface $request);

    /**
     * @param ListRequestInterface $request
     *
     * @return StreamEvent[]
     * @throws AdapterException
     */
    public function getStreamEvents(ListRequestInterface $request);

    /**
     * @param StreamEventItemRequest $request
     *
     * @return void
     * @throws AdapterException
     */
    public function deleteStreamEvent(StreamEventItemRequest $request);
}
