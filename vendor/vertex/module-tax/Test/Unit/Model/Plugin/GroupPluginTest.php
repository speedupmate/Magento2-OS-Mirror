<?php declare(strict_types=1);

namespace Vertex\Tax\Test\Unit\Model\Plugin;

use Magento\Config\Model\Config\Structure\Element\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Vertex\Tax\Model\ModuleManager;
use Vertex\Tax\Model\Plugin\GroupPlugin;
use Vertex\Tax\Test\Unit\TestCase;

class GroupPluginTest extends TestCase
{
    /** @var MockObject|GroupPlugin */
    private $plugin;

    /** @var MockObject|ModuleManager */
    private $moduleManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->moduleManager = $this->createPartialMock(ModuleManager::class, ['isEnabled']);
        $this->plugin = $this->getObject(GroupPlugin::class, ['moduleManager' => $this->moduleManager]);
    }

    public function testAroundSetData()
    {
        /** @var MockObject|Group $subject */
        $subject = $this->createMock(Group::class);
        $data = [
            'path' => 'tax',
            'id' => 'classes',
            'children' => [
                'giftwrap_order_class' => [
                    'showInDefault' => 1,
                    'showInWebsite' => 1,
                    'showInStore' => 1
                ],
                'giftwrap_order_code' => [
                    'showInDefault' => 1,
                    'showInWebsite' => 1,
                    'showInStore' => 1
                ],
                'giftwrap_item_class' => [
                    'showInDefault' => 1,
                    'showInWebsite' => 1,
                    'showInStore' => 1
                ],
                'giftwrap_item_code' => [
                    'showInDefault' => 1,
                    'showInWebsite' => 1,
                    'showInStore' => 1
                ],
                'printed_giftcard_class' => [
                    'showInDefault' => 1,
                    'showInWebsite' => 1,
                    'showInStore' => 1
                ],
                'printed_giftcard_code' => [
                    'showInDefault' => 1,
                    'showInWebsite' => 1,
                    'showInStore' => 1
                ],
                'reward_points_class' => [
                    'showInDefault' => 1,
                    'showInWebsite' => 1,
                    'showInStore' => 1
                ],
                'reward_points_code' => [
                    'showInDefault' => 1,
                    'showInWebsite' => 1,
                    'showInStore' => 1
                ]
            ]
        ];

        $scope = 'default';

        $proceed = function () use ($scope) {
            return $scope;
        };

        $this->moduleManager->method('isEnabled')
            ->with($this->logicalOr('Magento_GiftWrapping', 'Magento_Reward'))
            ->willReturn(false);

        $this->plugin->aroundSetData($subject, $proceed, $data, $scope);
    }
}
