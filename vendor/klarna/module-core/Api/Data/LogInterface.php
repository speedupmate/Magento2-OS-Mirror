<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Api\Data;

interface LogInterface
{
    /**
     * Get log ID.
     *
     * @return int
     */
    public function getLogId(): int;

    /**
     * Set log ID.
     *
     * @param int $id
     * @return LogInterface $this
     */
    public function setLogId(int $id): LogInterface;

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set status.
     *
     * @param int $status
     * @return LogInterface $this
     */
    public function setStatus(int $status): LogInterface;

    /**
     * Get Action.
     *
     * @return string
     */
    public function getAction(): string;

    /**
     * Set action.
     *
     * @param string $action
     * @return LogInterface $this
     */
    public function setAction(string $action): LogInterface;

    /**
     * Get Klarna ID.
     *
     * @return string|null
     */
    public function getKlarnaId(): ?string;

    /**
     * Set Klarna ID.
     *
     * @param string|null $klarnaId
     * @return LogInterface $this
     */
    public function setKlarnaId(?string $klarnaId): LogInterface;

    /**
     * Get Url.
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Set Url.
     *
     * @param string $url
     * @return LogInterface $this
     */
    public function setUrl(string $url): LogInterface;

    /**
     * Get method.
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Set method.
     *
     * @param string $method
     * @return LogInterface $this
     */
    public function setMethod(string $method): LogInterface;

    /**
     * Get services.
     *
     * @return string
     */
    public function getService(): string;

    /**
     * Set service.
     *
     * @param string $service
     * @return LogInterface $this
     */
    public function setService(string $service): LogInterface;

    /**
     * Get request.
     *
     * @return string
     */
    public function getRequest(): string;

    /**
     * Set request.
     *
     * @param string $request
     * @return LogInterface $this
     */
    public function setRequest(string $request): LogInterface;

    /**
     * Get response.
     *
     * @return string
     */
    public function getResponse(): string;

    /**
     * Set response.
     *
     * @param string $response
     * @return LogInterface $this
     */
    public function setResponse(string $response): LogInterface;

    /**
     * Get created at.
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set created at.
     *
     * @param string $createdAt
     * @return LogInterface $this
     */
    public function setCreatedAt(string $createdAt): LogInterface;

    /**
     * Setting the increment id
     *
     * @param string|null $incrementId
     * @return LogInterface
     */
    public function setIncrementId(string $incrementId = null): LogInterface;

    /**
     * Getting back the increment id
     *
     * @return null|string
     */
    public function getIncrementId(): ?string;
}
