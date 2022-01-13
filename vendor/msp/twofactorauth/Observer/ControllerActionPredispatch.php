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

namespace MSP\TwoFactorAuth\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TfaSessionInterface;
use MSP\TwoFactorAuth\Api\TrustedManagerInterface;

class ControllerActionPredispatch implements ObserverInterface
{
    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var TfaSessionInterface
     */
    private $tfaSession;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TrustedManagerInterface
     */
    private $trustedManager;

    public function __construct(
        TfaInterface $tfa,
        ActionFlag $actionFlag,
        UrlInterface $url,
        Session $session,
        TfaSessionInterface $tfaSession,
        TrustedManagerInterface $trustedManager
    ) {
        $this->tfa = $tfa;
        $this->actionFlag = $actionFlag;
        $this->url = $url;
        $this->tfaSession = $tfaSession;
        $this->session = $session;
        $this->trustedManager = $trustedManager;
    }

    /**
     * Get current user
     * @return \Magento\User\Model\User|null
     */
    private function getUser()
    {
        return $this->session->getUser();
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->tfa->isEnabled()) {
            return;
        }

        /** @var $controllerAction \Magento\Backend\App\AbstractAction */
        $controllerAction = $observer->getEvent()->getControllerAction();
        $fullActionName = $controllerAction->getRequest()->getFullActionName();

        if (in_array($fullActionName, $this->tfa->getAllowedUrls())) {
            return;
        }

        $user = $this->getUser();
        if ($user && !empty($this->tfa->getUserProviders($user->getId()))) {
            $accessGranted = ($this->tfaSession->isGranted() || $this->trustedManager->isTrustedDevice()) &&
                empty($this->tfa->getProvidersToActivate($user->getId()));

            if (!$accessGranted) {
                $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                $url = $this->url->getUrl('msp_twofactorauth/tfa/index');
                $controllerAction->getResponse()->setRedirect($url);
            }
        }
    }
}
