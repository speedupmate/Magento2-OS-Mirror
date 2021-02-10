<?php

namespace Dotdigitalgroup\Sms\Api;

use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SmsOrderRepositoryInterface
{
    /**
     * @param SmsOrderInterface $orderSms
     */
    public function save(SmsOrderInterface $orderSms);

    /**
     * @param $id
     * @return SmsOrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
