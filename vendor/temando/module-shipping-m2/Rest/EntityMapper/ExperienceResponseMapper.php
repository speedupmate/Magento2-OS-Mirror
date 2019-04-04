<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Temando\Shipping\Model\ExperienceInterface;
use Temando\Shipping\Model\ExperienceInterfaceFactory;
use Temando\Shipping\Rest\Response\Type\ExperienceResponseType;

/**
 * Map API data to application data object
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ExperienceResponseMapper
{
    /**
     * @var ExperienceInterfaceFactory
     */
    private $experienceFactory;

    /**
     * ExperienceResponseMapper constructor.
     * @param ExperienceInterfaceFactory $experienceFactory
     */
    public function __construct(ExperienceInterfaceFactory $experienceFactory)
    {
        $this->experienceFactory = $experienceFactory;
    }

    /**
     * @param ExperienceResponseType $apiExperience
     * @return ExperienceInterface
     */
    public function map(ExperienceResponseType $apiExperience)
    {
        $experience = $this->experienceFactory->create(['data' => [
            ExperienceInterface::EXPERIENCE_ID => $apiExperience->getId(),
            ExperienceInterface::NAME => $apiExperience->getAttributes()->getExperienceName(),
            ExperienceInterface::STATUS => $apiExperience->getAttributes()->getStatus(),
        ]]);

        return $experience;
    }
}
