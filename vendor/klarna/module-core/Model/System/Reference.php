<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Model\System;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;

class Reference extends Field
{
    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param UrlInterface $url
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        UrlInterface $url
    ) {
        parent::__construct($context);
        $this->urlBuilder = $url;
    }

    /**
     * Getting back the reference text
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(AbstractElement $element)
    {
        $logsUrl = $this->urlBuilder->getUrl('klarna/index/logs');
        $supportUrl = $this->urlBuilder->getUrl('klarna/index/support/form/new');

        return "Logs can be found <p style='display:inline'><a href='$logsUrl' target='_blank'>here</a></p>. " .
            "You can report an issue or ask a question " .
            "<p style='display:inline'><a href='$supportUrl' target='_blank'>here</a></p>.";
    }
}
