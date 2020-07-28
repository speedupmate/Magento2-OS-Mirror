<?php

namespace Dotdigitalgroup\Chat\Plugin;

use Dotdigitalgroup\Chat\Model\Config;
use Dotdigitalgroup\Chat\Model\Profile\UpdateChatProfile;
use Dotdigitalgroup\Email\Controller\Ajax\Emailcapture;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;

class EmailcapturePlugin
{
    /**
     * @var UpdateChatProfile
     */
    private $chatProfile;

    /**
     * @var CookieReaderInterface
     */
    private $cookieReader;

    /**
     * @param UpdateChatProfile $chatProfile
     * @param CookieReaderInterface $cookieReader
     */
    public function __construct(
        UpdateChatProfile $chatProfile,
        CookieReaderInterface $cookieReader
    ) {
        $this->chatProfile = $chatProfile;
        $this->cookieReader = $cookieReader;
    }

    /**
     * @param Emailcapture $emailcapture
     */
    public function afterExecute(Emailcapture $emailcapture)
    {
        // if a chat profile ID is present, update chat profile data
        if ($chatProfileId = $this->cookieReader->getCookie(Config::COOKIE_CHAT_PROFILE, null)) {
            $this->chatProfile->update($chatProfileId, $emailcapture->getRequest()->getParam('email'));
        }
    }
}
