<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Vertex\Tax\Model\ModuleDetail;

/**
 * Displays the connector version
 */
class Version extends Field
{
    const CACHE_ID = 'vertex_tax_version';

    /** @var CacheInterface */
    protected $cache;

    /** @var ReadFactory ReadFactory */
    private $readFactory;

    /** @var Json */
    private $serializer;

    /** @var Files Files */
    private $files;

    /** @var ModuleDetail */
    private $moduleDetail;

    public function __construct(
        Context $context,
        ReadFactory $readFactory,
        Json $serializer,
        CacheInterface $cache,
        Files $files,
        ModuleDetail $moduleDetail,
        array $data = []
    ) {
        $this->readFactory = $readFactory;
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->files = $files;
        $this->moduleDetail = $moduleDetail;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        return '<p>' . $this->moduleDetail->getModuleVersion() . '</p>';
    }
}
