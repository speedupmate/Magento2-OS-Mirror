<?php

namespace Dotdigitalgroup\Sms\Model\Apiconnector;

use Dotdigitalgroup\Email\Helper\Data;
use Dotdigitalgroup\Sms\Model\Apiconnector\Client;
use Dotdigitalgroup\Sms\Model\Apiconnector\ClientFactory;

class SmsClientFactory
{
    /**
     * @var Data
     */
    private $emailHelper;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * SmsClientFactory constructor
     * @param Data $emailHelper
     * @param ClientFactory $clientFactory
     */
    public function __construct(
        Data $emailHelper,
        ClientFactory $clientFactory
    ) {
        $this->emailHelper = $emailHelper;
        $this->clientFactory = $clientFactory;
    }

    /**
     * Api client by website.
     *
     * @param int $websiteId
     * @return Client
     */
    public function create($websiteId = 0)
    {
        $apiUsername = $this->emailHelper->getApiUsername($websiteId);
        $apiPassword = $this->emailHelper->getApiPassword($websiteId);

        $client = $this->clientFactory->create();
        $client->setApiUsername($apiUsername)
            ->setApiPassword($apiPassword);

        return $client;
    }
}
