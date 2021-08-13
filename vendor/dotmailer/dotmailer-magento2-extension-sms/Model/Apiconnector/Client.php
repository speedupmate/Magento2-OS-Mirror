<?php

namespace Dotdigitalgroup\Sms\Model\Apiconnector;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Email\Model\Apiconnector\Rest;

class Client extends Rest
{
    const REST_CPAAS_MESSAGES_API_URL = 'https://api-cpaas.dotdigital.com/cpaas/messages';
    const REST_CPAAS_DEDICATED_NUMBERS = 'https://api-cpaas.dotdigital.com/cpaas/sms/dedicatedNumbers';
    const REST_CPAAS_KEYWORDS = 'https://api-cpaas.dotdigital.com/cpaas/sms/keywords';
    const REST_CPAAS_SHORTCODES = 'https://api-cpaas.dotdigital.com/cpaas/sms/shortcodes';

    /**
     * @param $data
     * @return mixed|null
     */
    public function sendSmsSingle($data)
    {
        $this->setUrl(self::REST_CPAAS_MESSAGES_API_URL)
            ->setVerb('POST')
            ->buildPostBody($data);

        $response = $this->execute();

        if (isset($response->validationFailures)) {
            $this->addClientLog('SMS send failed')
                ->addClientLog('Validation failures', [
                    'data' => $response->validationFailures,
                ], Logger::DEBUG);
        }

        return $response;
    }

    /**
     * Retrieves data for a sent message.
     *
     * @param string $messageId
     *
     * @return null
     * @throws \Exception
     */
    public function getMessageByMessageId($messageId)
    {
        $this->setUrl(self::REST_CPAAS_MESSAGES_API_URL . '/' . $messageId)
            ->setVerb('GET');

        $response = $this->execute();

        if (!isset($response->messageId)) {
            $this->addClientLog('Error fetching message by ID', [
                'message_id' => $messageId,
                'response' => (string) $response
            ]);
        }

        return $response;
    }

    /**
     * @param $data
     * @return mixed|null
     */
    public function sendSmsBatch($data)
    {
        $this->setUrl(self::REST_CPAAS_MESSAGES_API_URL . '/batch')
            ->setVerb('POST')
            ->buildPostBody($data);

        $response = $this->execute();

        if (isset($response->message)) {
            $this->addClientLog('SMS send error: ' . $response->message);
        }

        if (isset($response->validationFailures)) {
            $this->addClientLog('SMS send failed')
                ->addClientLog('Validation failures', [
                    'data' => $response->validationFailures,
                ], Logger::DEBUG);
        }

        return $response;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDedicatedNumbers()
    {
        $url = self::REST_CPAAS_DEDICATED_NUMBERS;
        $this->setUrl($url)
            ->setVerb('GET');

        $response = $this->execute();
        if (isset($response->message)) {
            $this->addClientLog('Error getting account dedicated numbers');
            return [];
        }

        return $response;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getKeywords()
    {
        $url = self::REST_CPAAS_KEYWORDS;
        $this->setUrl($url)
            ->setVerb('GET');

        $response = $this->execute();
        if (isset($response->message)) {
            $this->addClientLog('Error getting account keywords');
            return [];
        }

        return $response;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getShortCodes()
    {
        $url = self::REST_CPAAS_SHORTCODES;
        $this->setUrl($url)
            ->setVerb('GET');

        $response = $this->execute();
        if (isset($response->message)) {
            $this->addClientLog('Error getting account shortcodes');
            return [];
        }

        return $response;
    }
}
