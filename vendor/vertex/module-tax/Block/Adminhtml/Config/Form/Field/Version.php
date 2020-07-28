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

    public function __construct(
        Context $context,
        ReadFactory $readFactory,
        Json $serializer,
        CacheInterface $cache,
        Files $files,
        array $data = []
    ) {
        $this->readFactory = $readFactory;
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->files = $files;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element) : string
    {
        return '<p>' . $this->getComposerVersion() . '</p>';
    }

    private function getComposerInformation() : array
    {
        try {
            $composer = $this->files->getModuleFile('Vertex', 'Tax', 'composer.json');

            /** @var Read $file */
            $file = $this->readFactory->create($composer, DriverPool::FILE);
            return (array)$this->serializer->unserialize($file->readAll());
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getComposerVersion() : string
    {
        $version = $this->cache->load(self::CACHE_ID);

        if ($version === false) {
            $version = $this->getComposerInformation()['version'] ?? __('N/A');
            $this->cache->save($version, self::CACHE_ID);
        }
        return $version;
    }
}
