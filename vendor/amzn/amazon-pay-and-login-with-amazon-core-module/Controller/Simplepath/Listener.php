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

namespace Amazon\Core\Controller\Simplepath;

/**
 * Class Listener
 * @package Amazon\Core\Controller\Simplepath
 */
class Listener extends \Magento\Framework\App\Action\Action
{

    // @var \Magento\Framework\Controller\Result\JsonFactory
    private $jsonResultFactory;

    // @var \Amazon\Core\Model\Config\SimplePath
    private $simplepath;

    /**
     * Listener constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Amazon\Core\Model\Config\SimplePath $simplepath
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Amazon\Core\Model\Config\SimplePath $simplepath,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->simplepath        = $simplepath;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->scopeConfig       = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Parse POST request from Amazon and import keys
     */
    public function execute()
    {
        $url = parse_url($this->simplepath->getEndpointRegister());
        $host = trim(preg_replace("/\r|\n/", "", $url['host']));
        $this->getResponse()->setHeader('Access-Control-Allow-Origin', 'https://' .$host );
        $this->getResponse()->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->getResponse()->setHeader('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-Token');

        $payload = $this->_request->getParam('payload');

        $result = $this->jsonResultFactory->create();

        $return = ['result' => 'error', 'message' => 'Empty payload'];

        try {
            if (strpos($payload, 'encryptedKey') === false) {
                $return = ['result' => 'error', 'message' => 'Invalid payload: ' . $payload];
            } else if ($payload) {
                $json = $this->simplepath->decryptPayload($payload, false);

                if ($json) {
                    $return = ['result' => 'success'];
                }
            } else {
                $return = ['result' => 'error', 'message' => 'payload parameter not found.'];
            }
        } catch (\Exception $e) {
            $return = ['result' => 'error', 'message' => $e->getMessage()];
        }

        if ($this->_request->isPost() && (empty($return['result']) || $return['result'] == 'error')) {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
        }

        $result->setData($return);

        return $result;
    }

    /**
     * Overridden to allow POST without form key
     *
     * @return bool
     */
    public function _processUrlKeys()
    {
        $_isValidFormKey = true;
        $_isValidSecretKey = true;
        $_keyErrorMsg = '';
        if ($this->_auth->isLoggedIn()) {
            if ($this->_backendUrl->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = __('You entered an invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        \Magento\Framework\Json\Helper\Data::class
                    )->jsonEncode(
                        ['error' => true, 'message' => $_keyErrorMsg]
                    )
                );
            } else {
                $this->_redirect($this->_backendUrl->getStartupPageUrl());
            }
            return false;
        }
        return true;
    }
}
