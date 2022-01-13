<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use JetBrains\PhpStorm\ArrayShape;
use Magento\Config\Model\Config\Structure\Element\Group;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ModuleManager;

/**
 * Hides likely unused tax classes from the store configuration
 *
 * @see Group
 */
class GroupPlugin
{
    /** @var Config */
    private $config;

    /** @var ModuleManager */
    private $moduleManager;

    public function __construct(ModuleManager $moduleManager, Config $config)
    {
        $this->moduleManager = $moduleManager;
        $this->config = $config;
    }

    /**
     * Hides likely unused tax classes
     * MEQP2 Warning: Unused Parameter $subject necessary for plugins
     *
     * @param Group $subject
     * @param array $data
     * @param string $scope
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) $subject is a necessary part of a plugin
     * @see Group::setData()
     */
    #[ArrayShape(['array', 'string'])]
    public function beforeSetData(
        Group $subject,
        array $data,
        $scope
    ): array {
        $taxClasses = isset($data['path'], $data['id']) && $data['path'] === 'tax' && $data['id'] === 'classes';
        if ($taxClasses && !$this->moduleManager->isEnabled('Magento_GiftWrapping')) {
            $data = $this->hide(
                $data,
                [
                    'giftwrap_order_class',
                    'giftwrap_order_code',
                    'giftwrap_item_class',
                    'giftwrap_item_code',
                    'printed_giftcard_class',
                    'printed_giftcard_code',
                ]
            );
        }

        if ($taxClasses && !$this->moduleManager->isEnabled('Magento_Reward')) {
            $data = $this->hide(
                $data,
                [
                    'reward_points_class',
                    'reward_points_code',
                ]
            );
        }

        return [$data, $scope];
    }

    /**
     * Updates the data array to hide a path
     *
     * @param array &$data
     * @param array $toHide
     */
    private function hide(array $data, array $toHide)
    {
        $result = $data;
        if (isset($data['path'], $data['id']) && $data['path'] === 'tax' && $data['id'] === 'classes') {
            foreach ($toHide as $code) {
                if (is_array($data['children'][$code])) {
                    $result['children'][$code]['showInDefault'] = 0;
                    $result['children'][$code]['showInWebsite'] = 0;
                    $result['children'][$code]['showInStore'] = 0;
                }
            }
        }
        return $result;
    }
}
