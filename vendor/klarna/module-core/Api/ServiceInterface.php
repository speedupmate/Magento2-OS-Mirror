<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Api;

interface ServiceInterface
{
    /**
     * The value for the HTTP POST request
     */
    const POST = 'post';

    /**
     * The value for the HTTP GET request
     */
    const GET = 'get';

    /**
     * The value for the HTTP PUT request
     */
    const PUT = 'put';

    /**
     * The value for the HTTP PATCH request
     */
    const PATCH = 'patch';

    /**
     * The value for the HTTP DELETE request
     */
    const DELETE = 'delete';

    /**
     * Make API call
     *
     * @param string $url
     * @param array $body
     * @param string $method HTTP request type
     * @param null|string $klarnaId
     * @return array Response body from API call
     */
    public function makeRequest($url, $body = [], $method = self::POST, $klarnaId = null);

    /**
     * Connect to API
     *
     * @param string $username
     * @param string $password
     * @param string $connectUrl
     * @return bool Whether connect succeeded or not
     */
    public function connect($username, $password, $connectUrl = null);

    /**
     * @param string $product
     * @param string $version
     * @param string $mageInfo
     * @return mixed
     */
    public function setUserAgent($product, $version, $mageInfo);

    /**
     * @param string      $header
     * @param string|null $value
     * @return mixed
     */
    public function setHeader($header, $value = null);
}
