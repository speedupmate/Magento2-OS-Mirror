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
namespace Amazon\Login\Block;

use Amazon\Core\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class Login extends Template
{
    /**
     * @var Data
     */
    private $coreHelper;

    public function __construct(Context $context, Data $coreHelper)
    {
        $this->coreHelper = $coreHelper;
        parent::__construct($context);
    }

    protected function _toHtml()
    {
        if (!$this->coreHelper->isLoginButtonEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
