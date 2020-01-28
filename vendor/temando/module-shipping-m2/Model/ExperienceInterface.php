<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model;

/**
 * Temando Experience Interface.
 *
 * The experience data object represents one shipping experience as configured
 * at the Temando account. This is not to be confused with the shipping options
 * as available to a consumer during checkout. The checkout experiences represent
 * the shipping rates available for the current cart while the account
 * experiences contain the full rule sets used when qualifying an order.
 * Checkout experiences are derived from the merchant's account experiences.
 *
 * @package  Temando\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
interface ExperienceInterface
{
    const EXPERIENCE_ID = 'experience_id';
    const NAME = 'name';
    const STATUS = 'status';

    const STATUS_DRAFT = 'draft';
    const STATUS_DISABLED = 'disabled';
    const STATUS_PRODUCTION = 'production';

    /**
     * @return string
     */
    public function getExperienceId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getStatus();
}
