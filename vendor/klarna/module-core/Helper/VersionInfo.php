<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Helper;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Module\ResourceInterface;

class VersionInfo
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var ResourceInterface
     */
    private $resource;

    /**
     * ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param State                    $appState
     * @param ResourceInterface        $resource
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        State $appState,
        ResourceInterface $resource
    ) {
        $this->appState = $appState;
        $this->productMetadata = $productMetadata;
        $this->resource = $resource;
    }

    /**
     * Get module version info
     *
     * @param string $packageName
     * @return array|bool
     */
    public function getVersion($packageName)
    {
        return $this->resource->getDataVersion($packageName);
    }

    /**
     * Gets the current MAGE_MODE setting
     *
     * @return string
     */
    public function getMageMode()
    {
        return $this->appState->getMode();
    }

    /**
     * Gets the current Magento version
     *
     * @return string
     */
    public function getMageVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Gets the current Magento Edition
     *
     * @return string
     */
    public function getMageEdition()
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * Creates the module version string
     *
     * @param string $version
     * @param string $caller
     * @return string
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function getModuleVersionString(string $version, string $caller): string
    {
        return sprintf(
            "%s;Core/%s",
            $version,
            $this->getVersion('Klarna_Core')
        );
    }
}
