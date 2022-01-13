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

namespace MSP\TwoFactorAuth\Controller\Adminhtml\Authy;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use MSP\TwoFactorAuth\Model\AlertInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TfaSessionInterface;
use MSP\TwoFactorAuth\Api\TrustedManagerInterface;
use MSP\TwoFactorAuth\Controller\Adminhtml\AbstractAction;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Verifyonetouch extends AbstractAction
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var TrustedManagerInterface
     */
    private $trustedManager;

    /**
     * @var TfaSessionInterface
     */
    private $tfaSession;

    /**
     * @var AlertInterface
     */
    private $alert;

    /**
     * @var Authy\OneTouch
     */
    private $oneTouch;

    /**
     * Verifyonetouch constructor.
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param TrustedManagerInterface $trustedManager
     * @param TfaSessionInterface $tfaSession
     * @param TfaInterface $tfa
     * @param AlertInterface $alert
     * @param Authy\OneTouch $oneTouch
     * @param Session $session
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        TrustedManagerInterface $trustedManager,
        TfaSessionInterface $tfaSession,
        TfaInterface $tfa,
        AlertInterface $alert,
        Authy\OneTouch $oneTouch,
        Session $session
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->jsonFactory = $jsonFactory;
        $this->tfa = $tfa;
        $this->trustedManager = $trustedManager;
        $this->tfaSession = $tfaSession;
        $this->alert = $alert;
        $this->oneTouch = $oneTouch;
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
     * @inheritdoc
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $res = $this->oneTouch->verify($this->getUser());
            if ($res == 'approved') {
                $this->trustedManager->handleTrustDeviceRequest(Authy::CODE, $this->getRequest());
                $this->tfaSession->grantAccess();
                $res = ['success' => true, 'status' => 'approved'];
            } else {
                $res = ['success' => false, 'status' => $res];

                if ($res == 'denied') {
                    $this->alert->event(
                        'MSP_TwoFactorAuth',
                        'Authy onetouch auth denied',
                        AlertInterface::LEVEL_WARNING,
                        $this->getUser()->getUserName()
                    );
                }
            }
        } catch (\Exception $e) {
            $result->setHttpResponseCode(500);
            $res = ['success' => false, 'message' => $e->getMessage()];

            $this->alert->event(
                'MSP_TwoFactorAuth',
                'Authy onetouch error',
                AlertInterface::LEVEL_ERROR,
                $this->getUser()->getUserName(),
                AlertInterface::ACTION_LOG,
                $e->getMessage()
            );
        }

        $result->setData($res);
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        $user = $this->getUser();

        return
            $user &&
            $this->tfa->getProviderIsAllowed($user->getId(), Authy::CODE) &&
            $this->tfa->getProvider(Authy::CODE)->isActive($user->getId());
    }
}
