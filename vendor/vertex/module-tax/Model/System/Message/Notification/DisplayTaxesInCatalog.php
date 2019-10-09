<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\System\Message\Notification;

use Magento\Framework\Notification\MessageInterface;
use Vertex\Tax\Model\Config\DisableMessage;

/**
 * This class displays notifications in the admin panel
 *
 */
class DisplayTaxesInCatalog implements MessageInterface
{
    /** @var DisableMessage */
    private $disableMessage;

    /**
     * @param DisableMessage $disableMessage
     */
    public function __construct(DisableMessage $disableMessage)
    {
        $this->disableMessage = $disableMessage;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return 'VERTEX_NOTIFICATION_TAX_IN_CATALOG';
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        return $this->disableMessage->getMessage(null, true);
    }

    /**
     * Check whether or not to display the error message
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return count($this->disableMessage->getAffectedScopes()) > 0;
    }
}
