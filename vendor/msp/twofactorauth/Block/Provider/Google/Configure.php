<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Block\Provider\Google;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\Auth\Session;
use MSP\TwoFactorAuth\Model\Provider\Engine\Google;

class Configure extends Template
{
    private $session;

    /**
     * @var Google
     */
    private $google;

    public function __construct(Template\Context $context, Google $google, Session $session, array $data = [])
    {
        $this->session = $session;
        $this->google  = $google;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $this->jsLayout['components']['msp-twofactorauth-configure']['postUrl'] =
            $this->getUrl('*/*/configurepost');

        $this->jsLayout['components']['msp-twofactorauth-configure']['qrCodeUrl'] =
            $this->getUrl('*/*/qr');

        $this->jsLayout['components']['msp-twofactorauth-configure']['successUrl'] =
            $this->getUrl($this->_urlBuilder->getStartupPageUrl());

        $this->jsLayout['components']['msp-twofactorauth-configure']['secretCode'] =
            $this->google->getSecretCode($this->session->getUser());

        return parent::getJsLayout();
    }
}
