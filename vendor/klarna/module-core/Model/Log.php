<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Model;

use Klarna\Core\Api\Data\LogInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Klarna\Core\Model\ResourceModel\Log as ResourceModel;

class Log extends AbstractModel implements IdentityInterface, LogInterface
{
    const CACHE_TAG = 'klarna_core_log';

    /**
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheridoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheridoc
     */
    public function getLogId(): int
    {
        return $this->getData('log_id');
    }

    /**
     * @inheridoc
     */
    public function setLogId(int $id): LogInterface
    {
        return $this->setData('log_id', $id);
    }

    /**
     * @inheridoc
     */
    public function getStatus(): int
    {
        return $this->getData('status');
    }

    /**
     * @inheridoc
     */
    public function setStatus(int $status): LogInterface
    {
        return $this->setData('status', $status);
    }

    /**
     * @inheridoc
     */
    public function getAction(): string
    {
        return $this->getData('action');
    }

    /**
     * @inheridoc
     */
    public function setAction(string $action): LogInterface
    {
        return $this->setData('action', $action);
    }

    /**
     * @inheridoc
     */
    public function getKlarnaId(): ?string
    {
        return $this->getData('klarna_id');
    }

    /**
     * @inheridoc
     */
    public function setKlarnaId(?string $klarnaId): LogInterface
    {
        return $this->setData('klarna_id', $klarnaId);
    }

    /**
     * @inheridoc
     */
    public function getUrl(): string
    {
        return $this->getData('url');
    }

    /**
     * @inheridoc
     */
    public function setUrl(string $url): LogInterface
    {
        return $this->setData('url', $url);
    }

    /**
     * @inheridoc
     */
    public function getMethod(): string
    {
        return $this->getData('method');
    }

    /**
     * @inheridoc
     */
    public function setMethod(string $method): LogInterface
    {
        return $this->setData('method', $method);
    }

    /**
     * @inheridoc
     */
    public function getService(): string
    {
        return $this->getData('service');
    }

    /**
     * @inheridoc
     */
    public function setService(string $service): LogInterface
    {
        return $this->setData('service', $service);
    }

    /**
     * @inheridoc
     */
    public function getRequest(): string
    {
        return $this->getData('request');
    }

    /**
     * @inheridoc
     */
    public function setRequest(string $request): LogInterface
    {
        return $this->setData('request', $request);
    }

    /**
     * @inheridoc
     */
    public function getResponse(): string
    {
        return $this->getData('response');
    }

    /**
     * @inheridoc
     */
    public function setResponse(string $response): LogInterface
    {
        return $this->setData('response', $response);
    }

    /**
     * @inheridoc
     */
    public function getCreatedAt(): string
    {
        return $this->getData('created_at');
    }

    /**
     * @inheridoc
     */
    public function setCreatedAt(string $createdAt): LogInterface
    {
        return $this->setData('created_at', $createdAt);
    }

    /**
     * @inheridoc
     */
    public function setIncrementId(string $incrementId = null): LogInterface
    {
        return $this->setData('increment_id', $incrementId);
    }

    /**
     * @inheridoc
     */
    public function getIncrementId(): ?string
    {
        return $this->getData('increment_id');
    }
}
