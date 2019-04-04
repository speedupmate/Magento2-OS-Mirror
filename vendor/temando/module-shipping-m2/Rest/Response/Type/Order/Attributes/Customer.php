<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Type\Order\Attributes;

/**
 * Temando API Order Attributes Customer Response Type
 *
 * @package  Temando\Shipping\Rest
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class Customer
{
    /**
     * @var \Temando\Shipping\Rest\Response\Type\Order\Attributes\Customer\Contact
     */
    private $contact;

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Order\Attributes\Customer\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Order\Attributes\Customer\Contact $contact
     * @return void
     */
    public function setContact(\Temando\Shipping\Rest\Response\Type\Order\Attributes\Customer\Contact $contact)
    {
        $this->contact = $contact;
    }
}
