<?php

namespace Dotdigitalgroup\Chat\Model\Api\Token;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * JSON Web Token decoder.
 * Borrowed from Magento\CardinalCommerce\Model\JwtManagement.
 */
class JwtDecoder
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * Converts JWT string into array (without supplying a key)
     * in order to check the token expiry.
     *
     * @param string $jwt The JWT
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function decode(string $jwt): array
    {
        if (empty($jwt)) {
            throw new \InvalidArgumentException('JWT is empty');
        }

        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            throw new \InvalidArgumentException('Wrong number of segments in JWT');
        }

        $payloadB64 = $parts[1];

        $payloadJson  = $this->urlSafeB64Decode($payloadB64);
        $payload = $this->json->unserialize($payloadJson);

        return $payload;
    }

    /**
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string
     */
    private function urlSafeB64Decode(string $input): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return base64_decode(
            str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT)
        );
    }
}
