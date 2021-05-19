<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Unit\Model\Config;

use Vertex\Tax\Model\Config\DisableMessage;
use Vertex\Tax\Test\Unit\TestCase;

class DisableMessageTest extends TestCase
{
    /**
     * Test get message results
     *
     * @param string $message
     * @param array $data
     * @dataProvider getMessageDataProvider
     */
    public function testGetMessage($message, $data)
    {
        list($affectedScopes, $scopeId, $showAffectedStores, $expected) = $data;
        /** @var DisableMessage $disableMessage */
        $disableMessage = $this->getObject(DisableMessage::class);
        $this->setInaccessibleProperty($disableMessage, 'affectedScopes', $affectedScopes);
        $result = $disableMessage->getMessage($scopeId, $showAffectedStores);
        if ($expected === '') {
            $this->assertEmpty($result);
        } else {
            $this->assertStringContainsString($expected, $result, $message);
        }
    }

    /**
     * Data Provider for test @see DisableMessageTest::testGetMessage()
     *
     * @return array
     */
    public function getMessageDataProvider()
    {
        return [
            [
                'test if specific scope is disable',
                [
                    [
                        0 => 'Admin (Admin)',
                        1 => 'Main Website (Default Store View)',
                        2 => 'newWebsite (testview)',
                    ],
                    2,
                    false,
                    'disabled',
                ],
            ],
            [
                'get message for a not affect scope',
                [
                    [2 => 'newWebsite (testview)'],
                    1,
                    false,
                    '',
                ],
            ],
            [
                'get message with all stores affected',
                [
                    [
                        0 => 'Admin (Admin)',
                        1 => 'Main Website (Default Store View)',
                        2 => 'newWebsite (testview)',
                    ],
                    1,
                    true,
                    'newWebsite (testview)',
                ],
            ],
        ];
    }
}
