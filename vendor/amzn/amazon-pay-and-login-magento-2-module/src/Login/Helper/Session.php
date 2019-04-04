<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Login\Helper;

use Amazon\Core\Api\Data\AmazonCustomerInterface;
use Amazon\Login\Domain\ValidationCredentials;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

class Session
{
    /**
     * @var CustomerSession
     */
    private $session;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @param CustomerSession $session
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        CustomerSession $session,
        EventManagerInterface $eventManager
    ) {
        $this->session      = $session;
        $this->eventManager = $eventManager;
    }

    /**
     * Login customer by data
     *
     * @param CustomerInterface $customerData
     */
    public function login(CustomerInterface $customerData)
    {
        if ($customerData->getId() != $this->session->getId() || !$this->session->isLoggedIn()) {
            $this->dispatchAuthenticationEvent();
            $this->session->setCustomerDataAsLoggedIn($customerData);
            $this->session->regenerateId();
        }
    }

    /**
     * Login customer by id
     *
     * @param integer $customerId
     */
    public function loginById($customerId)
    {
        $this->dispatchAuthenticationEvent();
        $this->session->loginById($customerId);
        $this->session->regenerateId();
    }

    /**
     * For compatibility with customer_customer_authenticated event dispatched from standard login controller.
     * The observers are also attached to this with the exception of password related ones.
     */
    protected function dispatchAuthenticationEvent()
    {
        $this->eventManager->dispatch('amazon_customer_authenticated');
    }

    /**
     * Set validation credentials in session
     *
     * @param ValidationCredentials $credentials
     */
    public function setValidationCredentials(ValidationCredentials $credentials)
    {
        $this->session->setAmazonValidationCredentials($credentials);
    }

    /**
     * Get validation credentials from session
     *
     * @return ValidationCredentials|null
     */
    public function getValidationCredentials()
    {
        $credentials = $this->session->getAmazonValidationCredentials();

        return ($credentials) ?: null;
    }

    /**
     * Check if Magento account is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * @param AmazonCustomerInterface $amazonCustomer
     * @return void
     */
    public function setAmazonCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        $this->session->setAmazonCustomer($amazonCustomer);
    }

    /**
     * @return void
     */
    public function clearAmazonCustomer()
    {
        $this->session->unsAmazonCustomer();
    }

    /**
     * @return AmazonCustomerInterface|null
     */
    public function getAmazonCustomer()
    {
        $amazonCustomer = $this->session->getAmazonCustomer();

        if ($amazonCustomer && (!$amazonCustomer instanceof AmazonCustomerInterface)) {
            $this->clearAmazonCustomer();
            $amazonCustomer = null;
        }

        return $amazonCustomer;
    }
}
