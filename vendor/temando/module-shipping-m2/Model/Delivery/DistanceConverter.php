<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Delivery;

use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Helper\Carrier;
use Magento\Store\Model\ScopeInterface;

/**
 * Temando Delivery Location Distance Converter
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class DistanceConverter
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Carrier
     */
    private $unitConverter;

    /**
     * DistanceConverter constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Carrier $unitConverter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Carrier $unitConverter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->unitConverter = $unitConverter;
    }

    /**
     * Normalize a delivery location distance to meters.
     *
     * @param float $value
     * @param string $unit "km" or "mi"
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    public function normalize(float $value, string $unit): int
    {
        $targetUnit = \Zend_Measure_Length::METER;

        switch ($unit) {
            case 'mi':
                $sourceUnit = \Zend_Measure_Length::MILE;
                break;
            case 'km':
                $sourceUnit = \Zend_Measure_Length::KILOMETER;
                break;
            default:
                throw new \InvalidArgumentException("Unit $unit is not supported for delivery location distance.");
        }

        $value = (int) $this->unitConverter->convertMeasureDimension($value, $sourceUnit, $targetUnit);

        return $value;
    }

    /**
     * Localize a delivery location distance.
     *
     * @param int $value
     * @param int $storeId
     * @return float
     */
    public function localize(int $value, int $storeId): float
    {
        $sourceUnit = \Zend_Measure_Length::METER;

        $weightUnit = $this->scopeConfig->getValue(Data::XML_PATH_WEIGHT_UNIT, ScopeInterface::SCOPE_STORE, $storeId);
        $targetUnit = ($weightUnit === 'kgs') ? \Zend_Measure_Length::KILOMETER : \Zend_Measure_Length::MILE;

        $value = (float) $this->unitConverter->convertMeasureDimension($value, $sourceUnit, $targetUnit);

        return $value;
    }

    /**
     * Localize a delivery location distance and obtain display text incl. unit.
     *
     * @param int|null $value
     * @param int $storeId
     * @param string $format
     * @param int $precision
     * @return string
     */
    public function format(?int $value, int $storeId, string $format = '%1$s %2$s', int $precision = 3): string
    {
        if (!$value) {
            return (string) $value;
        }

        $value = $this->localize($value, $storeId);
        $value = (string) round($value, $precision);

        $weightUnit = $this->scopeConfig->getValue(Data::XML_PATH_WEIGHT_UNIT, ScopeInterface::SCOPE_STORE, $storeId);
        $targetUnit = ($weightUnit === 'kgs') ? \Zend_Measure_Length::KILOMETER : \Zend_Measure_Length::MILE;

        $unit = $this->unitConverter->getMeasureDimensionName($targetUnit);

        return sprintf($format, $value, $unit);
    }
}
