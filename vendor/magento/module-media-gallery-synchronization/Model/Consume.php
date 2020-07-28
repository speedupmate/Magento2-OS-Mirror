<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\MediaGallerySynchronizationApi\Api\SynchronizeInterface;

/**
 * Media gallery image synchronization queue consumer.
 */
class Consume
{
    /**
     * @var SynchronizeInterface
     */
    private $synchronize;

    /**
     * @param SynchronizeInterface $synchronize
     */
    public function __construct(SynchronizeInterface $synchronize)
    {
        $this->synchronize = $synchronize;
    }

    /**
     * Run media files synchronization.
     */
    public function execute() : void
    {
        $this->synchronize->execute();
    }
}
