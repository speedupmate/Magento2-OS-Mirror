<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Logger\Api;

class Container
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $action;
    /**
     * @var array
     */
    private $request;
    /**
     * @var array
     */
    private $response;
    /**
     * @var string
     */
    private $klarnaId;
    /**
     * @var string
     */
    private $service;
    /**
     * @var string|null
     */
    private $incrementId;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return array
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * @param array $request
     */
    public function setRequest(array $request): void
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @param array $response
     */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }

    /**
     * @return string|null
     */
    public function getKlarnaId(): ?string
    {
        return $this->klarnaId;
    }

    /**
     * @param string|null $klarnaId
     */
    public function setKlarnaId(?string $klarnaId): void
    {
        $this->klarnaId = $klarnaId;
    }

    /**
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @param string $service
     */
    public function setService(string $service): void
    {
        $this->service = $service;
    }

    /**
     * @return string|null
     */
    public function getIncrementId(): ?string
    {
        return $this->incrementId;
    }

    /**
     * @param string|null $incrementId
     */
    public function setIncrementId(?string $incrementId): void
    {
        $this->incrementId = $incrementId;
    }
}
