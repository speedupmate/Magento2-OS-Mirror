<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Cron;

use Magento\Framework\Exception\LocalizedException;
use Vertex\Tax\Model\Config;

/**
 * Class triggered by cron to pre load wsdl
 */
class WsdlCache
{
    /** @var Config */
    private $config;

    /** @var \Vertex\Tax\Model\WsdlCache */
    private $wsdlCache;

    /**
     * @param Config $config
     * @param \Vertex\Tax\Model\WsdlCache $wsdlCache
     */
    public function __construct(
        Config $config,
        \Vertex\Tax\Model\WsdlCache $wsdlCache
    ) {
        $this->config = $config;
        $this->wsdlCache = $wsdlCache;
    }

    /**
     * execute pre load wsdl.
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->config->isWsdlCacheEnabled()) {
            return;
        }

        try {
            $this->wsdlCache->load();
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('Could not successfully load wsdl'), $e);
        }
    }
}
