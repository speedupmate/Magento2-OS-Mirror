<?php

namespace Dotdigitalgroup\Sms\Controller\Adminhtml\Report;

use Dotdigitalgroup\Email\Helper\MassDeleteCsrf;
use Dotdigitalgroup\Sms\Model\ResourceModel\SmsOrder;
use Dotdigitalgroup\Sms\Model\ResourceModel\SmsOrder\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends MassDeleteCsrf
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Dotdigitalgroup_Sms::report';

    /**
     * MassDelete constructor.
     * @param SmsOrder $collectionResource
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        SmsOrder $collectionResource,
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->collectionResource = $collectionResource;
        parent::__construct($context);
    }
}
