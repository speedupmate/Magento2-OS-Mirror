<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

/**
 * Class TokensDialogTest.
 */
class TokensDialogTest extends \Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTest
{
    public function testTokensDialog()
    {
        $controller = $this->_createIntegrationController('TokensDialog');
        $this->_registryMock->expects($this->any())->method('register');

        $this->_requestMock->expects(
            $this->any()
        )->method(
                'getParam'
            )->will(
                $this->returnValueMap(
                    [
                        [
                            \Magento\Integration\Controller\Adminhtml\Integration::PARAM_INTEGRATION_ID,
                            null,
                            self::INTEGRATION_ID, ],
                        [\Magento\Integration\Controller\Adminhtml\Integration::PARAM_REAUTHORIZE, 0, 0],
                    ]
                )
            );

        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                $this->equalTo(self::INTEGRATION_ID)
            )->will(
                $this->returnValue($this->_getIntegrationModelMock())
            );

        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->_oauthSvcMock->expects($this->once())->method('createAccessToken')->will($this->returnValue(true));

        $this->_viewMock->expects($this->any())->method('loadLayout');
        $this->_viewMock->expects($this->any())->method('renderLayout');

        $controller->execute();
    }
}
