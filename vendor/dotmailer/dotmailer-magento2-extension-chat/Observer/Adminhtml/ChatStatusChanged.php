<?php

namespace Dotdigitalgroup\Chat\Observer\Adminhtml;

use Dotdigitalgroup\Chat\Model\Config;
use Dotdigitalgroup\Email\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Dotdigitalgroup\Email\Logger\Logger;

/**
 * Validate api when saving creds in admin.
 */
class ChatStatusChanged implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    private $helper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ChatStatusChanged constructor.
     * @param Context $context
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param Data $helper
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        Config $config,
        ManagerInterface $messageManager,
        Data $helper,
        Logger $logger
    ) {
        $this->context = $context;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Check API credentials when live chat is enabled
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $website = $this->helper->getWebsiteForSelectedScopeInAdmin();
        $groups = $this->context->getRequest()->getPost('groups');
        $enabled = $this->getEnabled($groups);

        if (!$enabled) {
            $this->config->deleteChatApiCredentials();
            return;
        }

        $client = $this->helper->getWebsiteApiClient($website);
        $response = $client->setUpChatAccount();

        if (!$response || isset($response->message)) {
            $this->messageManager->addErrorMessage(__("There was a problem creating your chat account"));
            $this->config->setLiveChatStatus(false);
            $this->config->deleteChatApiCredentials();
            return;
        }

        $this->logger->info('Initialised for chat');

        $this->config->saveChatApiSpaceId($response->apiSpaceID)
            ->saveChatApiToken($response->token)
            ->reinitialiseConfig();
    }

    /**
     * @param $groups
     * @return mixed
     */
    private function getEnabled($groups)
    {
        if (isset($groups['settings']['fields']['enabled']['value'])) {
            return (bool) $groups['settings']['fields']['enabled']['value'];
        }

        return 'Default';
    }
}
