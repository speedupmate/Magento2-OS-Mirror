<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Repository;

use Temando\Shipping\Model\OrderInterface;

/**
 * Temando Experience Repository Interface.
 *
 * Access shipping experiences as defined in the merchant's account.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface ExperienceRepositoryInterface
{
    /**
     * Fetch order qualifications from platform which match the given order.
     *
     * @param \Temando\Shipping\Model\OrderInterface $order
     * @return \Temando\Shipping\Webservice\Response\Type\QualificationResponseType
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function getExperiencesForOrder(OrderInterface $order);

    /**
     * Read all experiences from platform as configured by the merchant.
     *
     * @return \Temando\Shipping\Model\ExperienceInterface[]
     */
    public function getExperiences();
}
