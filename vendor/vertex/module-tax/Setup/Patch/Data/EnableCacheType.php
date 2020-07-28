<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Setup\Patch\Data;

use Magento\Framework\App\Cache\State;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Vertex\Tax\Model\Cache\Type;

class EnableCacheType implements DataPatchInterface, PatchVersionInterface
{
    /** @var StateInterface */
    private $cacheState;

    /** @var DeploymentConfig */
    private $deploymentConfig;

    public function __construct(
        DeploymentConfig $deploymentConfig,
        StateInterface $cacheState
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->cacheState = $cacheState;
    }

    public function apply(): void
    {
        $statuses = $this->deploymentConfig->getConfigData(State::CACHE_KEY);

        if (!array_key_exists(Type::TYPE_IDENTIFIER, $statuses)) {
            $this->cacheState->setEnabled(Type::TYPE_IDENTIFIER, true);
            $this->cacheState->persist();
        }
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public static function getVersion(): string
    {
        return '100.5.1';
    }
}
