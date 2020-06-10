<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Block\Form;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Vertex\Tax\Model\ExceptionLogger;

class MessageRender extends AbstractBlock
{
    const KEY_RENDER_HANDLE = 'render_handle';
    const KEY_RENDER_BLOCK_NAME = 'render_block_name';

    /** @var LayoutFactory */
    private $layoutFactory;

    /** @var ExceptionLogger */
    private $logger;

    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory,
        ExceptionLogger $logger,
        array $data = []
    ) {
        $this->layoutFactory = $resultLayoutFactory;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function addHandle(Layout $layout, string $handleName) : void
    {
        $layout->getUpdate()->addHandle($handleName);
    }

    public function loadLayout(Layout $layout) : void
    {
        try {
            $layout->getUpdate()->load();
            $layout->generateXml();
            $layout->generateElements();
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }
    }

    public function render() : string
    {
        /** @var Layout $layout */
        $layout = $this->layoutFactory->create();

        $this->addHandle($layout, $this->_getData(self::KEY_RENDER_HANDLE));
        $this->loadLayout($layout);

        $block = $layout->getBlock($this->_getData(self::KEY_RENDER_BLOCK_NAME));
        return $block ? $block->toHtml() : '';
    }
}
