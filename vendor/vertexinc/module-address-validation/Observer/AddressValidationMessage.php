<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Vertex\AddressValidation\Model\Config;

class AddressValidationMessage implements ObserverInterface
{
    /** @var Config */
    private $config;

    /** @var RequestInterface */
    private $request;

    /** @var ManagerInterface */
    private $messageManager;

    public function __construct(
        Config $config,
        RequestInterface $request,
        ManagerInterface $messageManager
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    public function execute(Observer $observer) : void
    {
        if ($this->config->showValidationSuccessMessage() && $this->request->getParam('vertex_valid_message')) {
            $this->messageManager->addSuccessMessage(__('The address is valid'));
        }
    }
}
