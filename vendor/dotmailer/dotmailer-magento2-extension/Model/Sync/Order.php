<?php

namespace Dotdigitalgroup\Email\Model\Sync;

/**
 * Sync Orders.
 */
class Order
{
    /**
     * @var \Dotdigitalgroup\Email\Model\ResourceModel\Contact\CollectionFactory
     */
    public $contactCollectionFactory;

    /**
     * @var array
     */
    private $accounts = [];

    /**
     * @var string
     */
    private $apiUsername;

    /**
     * @var string
     */
    private $apiPassword;

    /**
     * Global number of orders.
     *
     * @var int
     */
    public $countOrders = 0;

    /**
     * @var array
     */
    private $orderIds;

    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Dotdigitalgroup\Email\Model\ResourceModel\Contact
     */
    private $contactResource;

    /**
     * @var \Dotdigitalgroup\Email\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $salesOrderFactory;

    /**
     * @var \Dotdigitalgroup\Email\Model\Connector\OrderFactory
     */
    private $connectorOrderFactory;

    /**
     * @var \Dotdigitalgroup\Email\Model\Connector\AccountFactory
     */
    private $accountFactory;

    /**
     * @var \Dotdigitalgroup\Email\Model\ImporterFactory
     */
    private $importerFactory;

    /**
     * @var \Dotdigitalgroup\Email\Model\ResourceModel\Order
     */
    private $orderResource;

    /**
     * @var array
     */
    public $guests = [];

    /**
     * Order constructor.
     * @param \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory
     * @param \Dotdigitalgroup\Email\Model\OrderFactory $orderFactory
     * @param \Dotdigitalgroup\Email\Model\Connector\AccountFactory $accountFactory
     * @param \Dotdigitalgroup\Email\Model\Connector\OrderFactory $connectorOrderFactory
     * @param \Dotdigitalgroup\Email\Model\ResourceModel\Contact $contactResource
     * @param \Dotdigitalgroup\Email\Model\ResourceModel\Contact\CollectionFactory $contactCollectionFactory
     * @param \Dotdigitalgroup\Email\Model\ResourceModel\Order $orderResource
     * @param \Dotdigitalgroup\Email\Helper\Data $helper
     * @param \Magento\Sales\Model\OrderFactory $salesOrderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory,
        \Dotdigitalgroup\Email\Model\OrderFactory $orderFactory,
        \Dotdigitalgroup\Email\Model\Connector\AccountFactory $accountFactory,
        \Dotdigitalgroup\Email\Model\Connector\OrderFactory $connectorOrderFactory,
        \Dotdigitalgroup\Email\Model\ResourceModel\Contact $contactResource,
        \Dotdigitalgroup\Email\Model\ResourceModel\Contact\CollectionFactory $contactCollectionFactory,
        \Dotdigitalgroup\Email\Model\ResourceModel\Order $orderResource,
        \Dotdigitalgroup\Email\Helper\Data $helper,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->importerFactory       = $importerFactory;
        $this->orderFactory          = $orderFactory;
        $this->accountFactory        = $accountFactory;
        $this->connectorOrderFactory = $connectorOrderFactory;
        $this->contactResource       = $contactResource;
        $this->orderResource         = $orderResource;
        $this->helper                = $helper;
        $this->salesOrderFactory     = $salesOrderFactory;
        $this->storeManager          = $storeManagerInterface;
        $this->contactCollectionFactory = $contactCollectionFactory;
    }

    /**
     * Initial sync the transactional data.
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sync()
    {
        $response = ['success' => true, 'message' => 'Done.'];

        // Initialise a return hash containing results of our sync attempt
        $this->searchWebsiteAccounts();

        foreach ($this->accounts as $account) {
            $orders = $account->getOrders();
            $ordersForSingleSync = $account->getOrdersForSingleSync();
            $numOrders = count($orders);
            $numOrdersForSingleSync = count($ordersForSingleSync);
            $website = $account->getWebsites();
            $this->countOrders += $numOrders;
            $this->countOrders += $numOrdersForSingleSync;
            //create bulk
            if ($numOrders) {
                $this->helper->log('--------- Order sync ---------- : ' . $numOrders);
                //queue order into importer
                $this->importerFactory->create()
                    ->registerQueue(
                        \Dotdigitalgroup\Email\Model\Importer::IMPORT_TYPE_ORDERS,
                        $orders,
                        \Dotdigitalgroup\Email\Model\Importer::MODE_BULK,
                        $website[0]
                    );
            }
            //create single
            if ($numOrdersForSingleSync) {
                $this->createSingleImports($ordersForSingleSync, $website);
            }

            //mark the orders as imported
            $this->orderResource->setImported($this->orderIds);

            unset($this->accounts[$account->getApiUsername()]);
        }

        /**
         * Add guests to contact table.
         */
        if (! empty($this->guests)) {
            $emailsForGuests = array_keys($this->guests);
            $guestEmailsExist = $this->contactCollectionFactory->create()
                ->addFieldToFilter('email', ['in' => $emailsForGuests])
                ->getColumnValues('email');
            $newGuests = array_diff_key($this->guests, array_flip($guestEmailsExist));
            //insert new guests contacts
            $this->contactResource->insertGuests($newGuests);
            //update the contacts and mark them as a guest
            $this->contactResource->updateContactsAsGuests($guestEmailsExist);
        }

        if ($this->countOrders) {
            $response['message'] = 'Orders updated ' . $this->countOrders;
        }

        return $response;
    }

    /**
     * Search the configuration data per website.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return null
     */
    public function searchWebsiteAccounts()
    {
        $websites = $this->helper->getWebsites();
        foreach ($websites as $website) {
            $apiEnabled = $this->helper->isEnabled($website);
            $storeIds = $website->getStoreIds();
            // api and order sync should be enabled, skip website with no store ids
            if ($apiEnabled && $this->helper->getWebsiteConfig(
                \Dotdigitalgroup\Email\Helper\Config::XML_PATH_CONNECTOR_SYNC_ORDER_ENABLED,
                $website
            )
                && !empty($storeIds)
            ) {
                $this->apiUsername = $this->helper->getApiUsername($website);
                $this->apiPassword = $this->helper->getApiPassword($website);
                // limit for orders included to sync
                $limit = $this->helper->getWebsiteConfig(
                    \Dotdigitalgroup\Email\Helper\Config::XML_PATH_CONNECTOR_TRANSACTIONAL_DATA_SYNC_LIMIT,
                    $website
                );
                //set account for later use
                if (! isset($this->accounts[$this->apiUsername])) {
                    $account = $this->accountFactory->create();
                    $account->setApiUsername($this->apiUsername);
                    $account->setApiPassword($this->apiPassword);
                    $this->accounts[$this->apiUsername] = $account;
                }
                $pendingOrders = $this->getPendingConnectorOrders($website, $limit);
                if (! empty($pendingOrders)) {
                    $this->accounts[$this->apiUsername]->setOrders($pendingOrders);
                }
                $this->accounts[$this->apiUsername]->setWebsites($website->getId());
                $modifiedOrders = $this->getModifiedOrders($website, $limit);
                if (! empty($modifiedOrders)) {
                    $this->accounts[$this->apiUsername]->setOrdersForSingleSync($modifiedOrders);
                }
            }
        }
    }

    /**
     * @param mixed $website
     * @param int $limit
     *
     * @return array
     */
    public function getPendingConnectorOrders($website, $limit = 100)
    {
        $orders = [];
        $storeIds = $website->getStoreIds();
        /** @var \Dotdigitalgroup\Email\Model\Order $orderModel */
        $orderModel = $this->orderFactory->create();
        //get order statuses set in configuration section
        $orderStatuses = $this->helper->getConfigSelectedStatus($website);

        //no active store for website
        if (empty($storeIds) || empty($orderStatuses)) {
            return [];
        }

        //pending order from email_order
        $orderCollection = $orderModel->getOrdersToImport($storeIds, $limit, $orderStatuses);

        //no orders found
        if (! $orderCollection->getSize()) {
            return $orders;
        }

        $orders = $this->mappOrderData($orderCollection, $orderModel, $orders);

        return $orders;
    }

    /**
     * @param mixed $website
     * @param int $limit
     *
     * @return array
     */
    protected function getModifiedOrders($website, $limit)
    {
        $orders =  [];
        $storeIds = $website->getStoreIds();
        /** @var \Dotdigitalgroup\Email\Model\Order $orderModel */
        $orderModel = $this->orderFactory->create();
        //get order statuses set in configuration section
        $orderStatuses = $this->helper->getConfigSelectedStatus($website);

        //no active store for website
        if (empty($storeIds) || empty($orderStatuses)) {
            return [];
        }

        //pending order from email_order
        $orderCollection = $orderModel->getModifiedOrdersToImport($storeIds, $limit, $orderStatuses);

        //no orders found
        if (! $orderCollection->getSize()) {
            return $orders;
        }

        $orders = $this->mappOrderData($orderCollection, $orderModel, $orders);

        return $orders;
    }

    /**
     * @param \Dotdigitalgroup\Email\Model\ResourceModel\Order\Collection $orderCollection
     * @param \Dotdigitalgroup\Email\Model\Order $orderModel
     * @param array $orders
     *
     * @return array
     */
    protected function mappOrderData($orderCollection, $orderModel, $orders)
    {
        $orderIds = $orderCollection->getColumnValues('order_id');

        //get the order collection
        $salesOrderCollection = $orderModel->getSalesOrdersWithIds($orderIds);

        foreach ($salesOrderCollection as $order) {
            if ($order->getId()) {
                $storeId = $order->getStoreId();
                $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

                /**
                 * Add guest to array to add to contacts table.
                 */
                if ($order->getCustomerIsGuest()
                    && $order->getCustomerEmail()
                ) {
                    //add guest to the list
                    if (!isset($this->guests[$order->getCustomerEmail()])) {
                        $this->guests[$order->getCustomerEmail()] = [
                            'email' => $order->getCustomerEmail(),
                            'website_id' => $websiteId,
                            'store_id' => $storeId,
                            'is_guest' => 1
                        ];
                    }
                }

                $connectorOrder = $this->connectorOrderFactory->create();
                $connectorOrder->setOrderData($order);
                $orders[] = $connectorOrder;
            }

            $this->orderIds[] = $order->getId();
        }

        return $orders;
    }

    /**
     * @param array $ordersForSingleSync
     * @param mixed $website
     *
     * @return null
     */
    protected function createSingleImports($ordersForSingleSync, $website)
    {
        foreach ($ordersForSingleSync as $order) {
            //register in queue with importer
            $this->importerFactory->create()
                ->registerQueue(
                    \Dotdigitalgroup\Email\Model\Importer::IMPORT_TYPE_ORDERS,
                    $order,
                    \Dotdigitalgroup\Email\Model\Importer::MODE_SINGLE,
                    $website[0]
                );
        }
    }
}
