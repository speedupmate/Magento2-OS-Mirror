<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Vertex\Tax\Model\DomDocumentFactory;

/**
 * Renders default Fieldset position <group> comment outside of collapsible area
 */
class FieldsetWithComment extends Fieldset
{
    /** @var DomDocumentFactory */
    protected $documentFactory;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param DomDocumentFactory $domFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        DomDocumentFactory $domFactory,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->documentFactory = $domFactory;
    }

    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        $html = parent::render($element);

        return $this->moveCommentElement($html);
    }

    /**
     * Moves div element outside collapsible area
     *
     * Since DOMDocument closes all HTML elements when saved,
     * we have to work with the entire HTML, instead just header
     *
     * @param string $sectionHtml HTML from config section
     * @return string
     */
    private function moveCommentElement($sectionHtml)
    {
        /** @var DomDocumentFactory */
        $dom = $this->documentFactory->create();

        // supress warnings due to ampersands inside of HTML
        @$dom->loadHtml($sectionHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // grab the hidden legend as starting point
        $legends = $dom->getElementsByTagName('legend');
        $legend = $legends && $legends->length && $legends->item(0) ? $legends->item(0) : null;

        if (!$legend) {
            return $sectionHtml;
        }

        // layout renders more than one time, check if it's not already there
        $exists = $legend->parentNode->parentNode->firstChild->getElementsByTagName('div');

        if ($exists && $exists->item(0)) {
            return $sectionHtml;
        }

        // grab div comment element
        $comment = $legend->nextSibling;

        if ($comment && $comment->nodeName == 'div') {
            // grabs admin collapsible div from section group
            $divs = $legend->parentNode->parentNode->getElementsByTagName('div');

            if ($divs->length > 0) {
                $collapsibleDiv = $divs->item(0);
                $collapsibleDiv->appendChild($comment);
                $sectionHtml = $dom->saveHtml();
            }
        }

        return $sectionHtml;
    }
}
