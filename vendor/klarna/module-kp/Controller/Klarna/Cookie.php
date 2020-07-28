<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\Kp\Controller\Klarna;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\DefaultConfigProvider;

/**
 * Order update from pending status
 */
class Cookie extends Action implements HttpGetActionInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var DefaultConfigProvider
     */
    private $defaultConfigProvider;

    /**
     * @param Context               $context
     * @param Session               $session
     * @param UrlInterface          $urlBuilder
     * @param DefaultConfigProvider $defaultConfigProvider
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Session $session,
        UrlInterface $urlBuilder,
        DefaultConfigProvider $defaultConfigProvider
    ) {
        parent::__construct($context);
        $this->checkoutSession       = $session;
        $this->urlBuilder            = $urlBuilder;
        $this->defaultConfigProvider = $defaultConfigProvider;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()->setPath($this->getRedirectUrl());
    }

    /**
     * Retrieve the redirect url, that was set to the checkout session during the authorize
     *
     * @return string
     */
    private function getRedirectUrl(): string
    {
        $redirectUrl = $this->checkoutSession->getRedirectUrl();
        if (!$redirectUrl) {
            $redirectUrl = $this->urlBuilder->getUrl($this->defaultConfigProvider->getDefaultSuccessPageUrl());
        }
        return $redirectUrl;
    }
}
