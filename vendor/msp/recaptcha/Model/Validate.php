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
 * @package    MSP_ReCaptcha
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace MSP\ReCaptcha\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use MSP\ReCaptcha\Api\ValidateInterface;
use ReCaptcha\ReCaptcha;

class Validate implements ValidateInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var State
     */
    private $state;

    /**
     * Validate constructor.
     * @param Config $config
     * @param State $state
     */
    public function __construct(
        Config $config,
        State $state = null
    ) {
        $this->config = $config;
        $this->state = $state ?: ObjectManager::getInstance()->get(State::class);
    }

    /**
     * Return true if reCaptcha validation has passed
     * @param string $reCaptchaResponse
     * @param string $remoteIp
     * @return bool
     * @throws LocalizedException
     */
    public function validate($reCaptchaResponse, $remoteIp)
    {
        $secret = $this->config->getPrivateKey();

        if ($reCaptchaResponse) {
            // @codingStandardsIgnoreStart
            $reCaptcha = new ReCaptcha($secret);
            // @codingStandardsIgnoreEmd

            if ($this->config->getType() === 'recaptcha_v3') {
                $threshold = $this->state->getAreaCode() === Area::AREA_ADMINHTML ?
                    $this->config->getMinBackendScore() :
                    $this->config->getMinFrontendScore();

                $reCaptcha->setScoreThreshold($threshold);
            }
            $res = $reCaptcha->verify($reCaptchaResponse, $remoteIp);

            if (($this->config->getType() === 'recaptcha_v3') && ($res->getScore() === null)) {
                throw new LocalizedException(__('Internal error: Make sure you are using reCaptcha V3 api keys'));
            }

            if ($res->isSuccess()) {
                return true;
            }
        }

        return false;
    }
}
