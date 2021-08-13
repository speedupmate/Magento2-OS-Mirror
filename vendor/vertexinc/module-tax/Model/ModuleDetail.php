<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

namespace Vertex\Tax\Model;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json;

class ModuleDetail
{
    const CACHE_ID = 'vertex_tax_module_information';

    /** @var CacheInterface */
    protected $cache;

    /** @var Files Files */
    private $files;

    /** @var ReadFactory ReadFactory */
    private $readFactory;

    /** @var Json */
    private $serializer;

    public function __construct(
        CacheInterface $cache,
        Files $files,
        Json $serializer,
        ReadFactory $readFactory
    ) {
        $this->cache = $cache;
        $this->files = $files;
        $this->serializer = $serializer;
        $this->readFactory = $readFactory;
    }

    private function getComposerInformation(): array
    {
        try {
            $composer = $this->cache->load(self::CACHE_ID);

            if ($composer === false) {
                $composer = $this->files->getModuleFile('Vertex', 'Tax', 'composer.json');

                /** @var Read $file */
                $file = $this->readFactory->create($composer, DriverPool::FILE);
                $composer = $file->readAll();
                $this->cache->save($composer, self::CACHE_ID);
            }
            $composer = $this->serializer->unserialize($composer);
            return (array)$composer;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getModuleName(): string
    {
        return $this->getComposerInformation()['name'] ?? __('N/A');
    }

    public function getModuleVersion(): string
    {
        return $this->getComposerInformation()['version'] ?? __('N/A');
    }
}
