<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Pickup\Pdf;

use Magento\Framework\Exception\LocalizedException;
use Zend\Barcode\Renderer\AbstractRenderer;

/**
 * Temando Pickup Pdf Barcode Renderer
 *
 * This renderer draws a ZF2 barcode on a ZF1 PDF document.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 *
 */
class BarcodeRenderer extends AbstractRenderer
{
    /**
     * PDF resource.
     *
     * @var \Zend_Pdf
     */
    protected $resource = null;

    /**
     * Page number in PDF resource.
     *
     * @var int
     */
    private $page = 0;

    /**
     * Checking of parameters after all settings
     * @return void
     */
    protected function checkSpecificParams()
    {
    }

    /**
     * Initialize the rendering resource
     * @return void
     */
    protected function initRenderer()
    {
        if ($this->resource === null) {
            $this->resource = new \Zend_Pdf();
            $this->resource->pages[] = new \Zend_Pdf_Page(\Zend_Pdf_Page::SIZE_A4);
        }

        /** @var \Zend_Pdf_Page $pdfPage */
        $pdfPage = $this->resource->pages[$this->page];
        $this->adjustPosition($pdfPage->getHeight(), $pdfPage->getWidth());
    }

    /**
     * Calculate the width of a string:
     * in case of using alignment parameter in drawText
     * @param string $text
     * @param \Zend_Pdf_Resource_Font $font
     * @param float $fontSize
     *
     * @return float
     * @throws \Zend_Pdf_Exception
     */
    public function widthForStringUsingFontSize($text, $font, $fontSize)
    {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $text);
        $characters = [];
        for ($i = 0; $i < strlen($drawingString); $i ++) {
            $characters[] = (ord($drawingString[$i ++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }

    /**
     * Draw a polygon in the rendering resource
     * @param string $text
     * @param float $size
     * @param array $position
     * @param string $font
     * @param int $color
     * @param string $alignment
     * @param float|int $orientation
     *
     * @throws \Zend_Pdf_Exception
     */
    protected function drawText(
        $text,
        $size,
        $position,
        $font,
        $color,
        $alignment = 'center',
        $orientation = 0
    ) {
        /** @var \Zend_Pdf_Page $page */
        $page  = $this->resource->pages[$this->page];
        $color = new \Zend_Pdf_Color_Rgb(
            (($color & 0xFF0000) >> 16) / 255.0,
            (($color & 0x00FF00) >> 8) / 255.0,
            ($color & 0x0000FF) / 255.0
        );

        $page->setLineColor($color);
        $page->setFillColor($color);
        $page->setFont(\Zend_Pdf_Font::fontWithPath($font), $size * $this->moduleSize * 1.2);

        $width = $this->widthForStringUsingFontSize(
            $text,
            \Zend_Pdf_Font::fontWithPath($font),
            $size * $this->moduleSize
        );

        $angle = pi() * $orientation / 180;
        $left = $position[0] * $this->moduleSize + $this->leftOffset;
        $top  = $page->getHeight() - $position[1] * $this->moduleSize - $this->topOffset;

        switch ($alignment) {
            case 'center':
                $left -= ($width / 2) * cos($angle);
                $top  -= ($width / 2) * sin($angle);
                break;
            case 'right':
                $left -= $width;
                break;
        }
        $page->rotate($left, $top, $angle);
        $page->drawText($text, $left, $top);
        $page->rotate($left, $top, - $angle);
    }

    /**
     * Draw a polygon in the rendering resource
     * @param array $points
     * @param int $color
     * @param  bool $filled
     */
    protected function drawPolygon($points, $color, $filled = true)
    {
        /** @var \Zend_Pdf_Page $page */
        $page = $this->resource->pages[$this->page];
        $x = [];
        $y = [];
        foreach ($points as $point) {
            $x[] = $point[0] * $this->moduleSize + $this->leftOffset;
            $y[] = $page->getHeight() - $point[1] * $this->moduleSize - $this->topOffset;
        }
        if (count($y) == 4) {
            if ($x[0] != $x[3] && $y[0] == $y[3]) {
                $y[0] -= ($this->moduleSize / 2);
                $y[3] -= ($this->moduleSize / 2);
            }
            if ($x[1] != $x[2] && $y[1] == $y[2]) {
                $y[1] += ($this->moduleSize / 2);
                $y[2] += ($this->moduleSize / 2);
            }
        }

        $color = new \Zend_Pdf_Color_Rgb(
            (($color & 0xFF0000) >> 16) / 255.0,
            (($color & 0x00FF00) >> 8) / 255.0,
            ($color & 0x0000FF) / 255.0
        );

        $page->setLineColor($color);
        $page->setFillColor($color);
        $page->setLineWidth($this->moduleSize);

        $fillType = ($filled)
            ? \Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE
            : \Zend_Pdf_Page::SHAPE_DRAW_STROKE;

        $page->drawPolygon($x, $y, $fillType);
    }

    /**
     * Set a PDF resource to draw the barcode inside
     *
     * @param \Zend_Pdf $pdf
     * @param int $page
     * @return void
     */
    public function setResource(\Zend_Pdf $pdf, int $page = 0)
    {
        $this->resource = $pdf;
        $this->page = $page;

        if (empty($this->resource->pages)) {
            $this->page = 0;
            $this->resource->pages[] = new \Zend_Pdf_Page(\Zend_Pdf_Page::SIZE_A4);
        }
    }

    /**
     * Render the resource by sending headers and drawn resource.
     *
     * Note: This barcode renderer does not support direct output. Obtain the
     * drawn resource instead and proceed from there.
     *
     * @see draw
     * @return mixed
     * @throws LocalizedException
     */
    public function render()
    {
        throw new LocalizedException(__('Direct barcode output is not supported.'));
    }
}
