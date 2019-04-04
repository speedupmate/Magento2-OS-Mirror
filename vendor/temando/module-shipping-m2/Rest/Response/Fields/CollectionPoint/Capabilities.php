<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Fields\CollectionPoint;

use Temando\Shipping\Rest\Response\Fields\CollectionPoint\Capabilities\AuthorityToLeave;

/**
 * Temando API Collection Point Qualification Collection Point Capabilities Field
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Capabilities
{
    /**
     * @var \Temando\Shipping\Rest\Response\Fields\CollectionPoint\Capabilities\AuthorityToLeave
     */
    private $authorityToLeave;

    /**
     * @return \Temando\Shipping\Rest\Response\Fields\CollectionPoint\Capabilities\AuthorityToLeave
     */
    public function getAuthorityToLeave()
    {
        return $this->authorityToLeave;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Fields\CollectionPoint\Capabilities\AuthorityToLeave $authorityToLeave
     * @return void
     */
    public function setAuthorityToLeave(AuthorityToLeave $authorityToLeave)
    {
        $this->authorityToLeave = $authorityToLeave;
    }
}
