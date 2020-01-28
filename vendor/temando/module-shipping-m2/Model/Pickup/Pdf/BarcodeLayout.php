<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Pickup\Pdf;

/**
 * Temando Pickup Pdf Barcode Layout
 *
 * Data container for barcode size and position.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class BarcodeLayout
{
    /**
     * @var int
     */
    private $offsetTop;

    /**
     * @var int
     */
    private $offsetLeft;

    /**
     * @var float
     */
    private $moduleSize;

    /**
     * BarcodeLayout constructor.
     * @param int $offsetTop
     * @param int $offsetLeft
     * @param float $moduleSize
     */
    public function __construct(int $offsetTop = 46, int $offsetLeft = 285, float $moduleSize = 0.75)
    {
        $this->offsetTop = $offsetTop;
        $this->offsetLeft = $offsetLeft;
        $this->moduleSize = $moduleSize;
    }

    /**
     * Obtain barcode's position from the top.
     *
     * @return int
     */
    public function getOffsetTop(): int
    {
        return $this->offsetTop;
    }

    /**
     * Obtain barcode's position from the left.
     *
     * @return int
     */
    public function getOffsetLeft(): int
    {
        return $this->offsetLeft;
    }

    /**
     * Obtain barcode's sizing factor.
     *
     * @return float
     */
    public function getModuleSize(): float
    {
        return $this->moduleSize;
    }
}
