<?php

namespace Dotdigitalgroup\Sms\Model\Message;

use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterface;
use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;
use Dotdigitalgroup\Sms\Model\Config\Source\FromName;
use Dotdigitalgroup\Sms\Model\Message\Text\Compiler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class MessageBuilder
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Compiler
     */
    private $messageCompiler;

    /**
     * @var array
     */
    private $smsTemplates = [];

    /**
     * MessageBuilder constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Compiler $messageCompiler
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Compiler $messageCompiler
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->messageCompiler = $messageCompiler;
    }

    /**
     * @param SmsOrderInterface[] $items
     * @return array
     */
    public function makeBatch(array $items)
    {
        $batch = [];

        foreach ($items as $item) {
            $buildMessage = [
                'to' => [
                    'phoneNumber' => $item->getPhoneNumber()
                ],
                'rules' => [
                    'sms'
                ],
                'channelOptions' => [
                    'sms' => [
                        'allowUnicode' => true,
                        'unicodeConversion' => [
                            'convertUnicodeToGsm' => false
                        ]
                    ]
                ],
                'body' => $this->getCompiledMessageText($item)
            ];

            $fromNumber = $this->getFromNumber($item->getStoreId());

            if ($fromNumber) {
                $buildMessage['channelOptions']['sms']['from'] = $fromNumber;
            }

            $batch[] = $buildMessage;
        }

        return $batch;
    }

    /**
     * @param SmsOrderInterface $item
     * @return string
     */
    private function getCompiledMessageText($item)
    {
        if (!isset($this->smsTemplates[$item->getStoreId()][$item->getTypeId()])) {
            $this->setRawMessageText($item->getStoreId(), $item->getTypeId());
        }
        return $this->messageCompiler->compile(
            $this->smsTemplates[$item->getStoreId()][$item->getTypeId()],
            $item
        );
    }

    /**
     * @param $storeId
     * @param $typeId
     */
    private function setRawMessageText($storeId, $typeId)
    {
        $this->smsTemplates[$storeId][$typeId] = $this->scopeConfig->getValue(
            ConfigInterface::TRANSACTIONAL_SMS_MESSAGE_TYPES_MAP[$typeId],
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return string|null
     */
    private function getFromNumber($storeId)
    {
        $fromName = $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_TRANSACTIONAL_SMS_DEFAULT_FROM_NAME,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );

        if ($fromName === FromName::SHARED_POOL_NUMBER) {
            return null;
        }

        if ($fromName === FromName::ALPHANUMERIC_NUMBER) {
            return $this->scopeConfig->getValue(
                ConfigInterface::XML_PATH_TRANSACTIONAL_SMS_ALPHANUMERIC_FROM_NAME,
                ScopeInterface::SCOPE_STORES,
                $storeId
            );
        }

        return $fromName;
    }
}
