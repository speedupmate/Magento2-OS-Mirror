<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Fields;

/**
 * Temando API Experience Resource Object Attributes
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ExperienceAttributes
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $experienceName;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getExperienceName()
    {
        return $this->experienceName;
    }

    /**
     * @param string $experienceName
     * @return void
     */
    public function setExperienceName($experienceName)
    {
        $this->experienceName = $experienceName;
    }
}
