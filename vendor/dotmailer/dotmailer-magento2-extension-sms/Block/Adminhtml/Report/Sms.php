<?php

namespace Dotdigitalgroup\Sms\Block\Adminhtml\Report;

use Dotdigitalgroup\Email\Block\Adminhtml\Config\Report\AbstractConfigField;

class Sms extends AbstractConfigField
{
    /**
     * @var string
     */
    public $buttonLabel = 'SMS Report';

    /**
     * @var string
     */
    protected $linkUrlPath = 'dotdigitalgroup_sms/report/index';
}
