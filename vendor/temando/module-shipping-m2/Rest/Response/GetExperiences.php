<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response;

/**
 * Temando API Get Experiences Operation
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class GetExperiences implements GetExperiencesInterface
{
    /**
     * @var \Temando\Shipping\Rest\Response\Type\ExperienceResponseType[]
     */
    private $data = [];

    /**
     * @return \Temando\Shipping\Rest\Response\Type\ExperienceResponseType[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param  \Temando\Shipping\Rest\Response\Type\ExperienceResponseType[] $experiences
     * @return void
     */
    public function setData(array $experiences)
    {
        $this->data = $experiences;
    }
}
