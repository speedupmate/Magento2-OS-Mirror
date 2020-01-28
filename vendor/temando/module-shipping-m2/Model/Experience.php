<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model;

use Magento\Framework\DataObject;

/**
 * Temando Experience Entity
 *
 * The experience data object represents one shipping experience as configured
 * at the Temando platform. This is not to be confused with the shipping options
 * as available to a consumer during checkout. The checkout experiences represent
 * the shipping rates available for the current cart while the platform
 * experiences contain the full rule sets taken into account when qualifying an
 * order. Checkout experiences are derived from the merchant's platform experiences.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Experience extends DataObject implements ExperienceInterface
{
    /**
     * @return string
     */
    public function getExperienceId()
    {
        return $this->getData(self::EXPERIENCE_ID);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
}
