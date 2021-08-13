<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class UpdateCronConfiguration implements DataPatchInterface, PatchRevertableInterface
{
    private const NEW_PATH = 'crontab/vertex_log/jobs/vertex_log_rotation/schedule/cron_expr';

    private const PREVIOUS_PATH = 'crontab/default/jobs/vertex_log_rotation/schedule/cron_expr';

    private $setup;

    public function __construct(ModuleDataSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    public function apply(): void
    {
        $this->setup->startSetup();
        $db = $this->setup->getConnection();
        $db->update(
            $this->setup->getTable('core_config_data'),
            ['path' => static::NEW_PATH],
            ['path = ?' => static::PREVIOUS_PATH]
        );
        $this->setup->endSetup();
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function revert(): void
    {
        $this->setup->startSetup();
        $db = $this->setup->getConnection();
        $db->update(
            $this->setup->getTable('core_config_data'),
            ['path' => static::PREVIOUS_PATH],
            ['path = ?' => static::NEW_PATH]
        );
        $this->setup->endSetup();
    }
}
