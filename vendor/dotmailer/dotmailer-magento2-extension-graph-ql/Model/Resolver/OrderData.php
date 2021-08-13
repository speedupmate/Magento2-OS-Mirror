<?php

namespace Dotdigitalgroup\EmailGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;

class OrderData implements ResolverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * OrderData constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['orderId'])) {
            throw new GraphQlInputException(__('Required parameter "orderId" is missing'));
        }

        $orderId = $args['orderId'];

        $criteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::INCREMENT_ID, $orderId)
            ->create();

        $orders = $this->orderRepository->getList($criteria)->getItems();
        $order = reset($orders);

        $items = [];

        foreach ($order->getItems() as $orderItem) {
            $items[] = $orderItem->getName();
        }
        return [
            'items' => array_unique($items),
            'total' => $order->getBaseGrandTotal()
        ];
    }
}
