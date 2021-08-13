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

namespace Klarna\Core\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Klarna\Core\Model\Support\Uploader;

class Upload implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param Uploader    $uploader
     * @codeCoverageIgnore
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        Uploader    $uploader
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->uploader          = $uploader;
    }

    /**
     * @return ResultInterface
     * @throws FileSystemException
     */
    public function execute(): ResultInterface
    {
        $result   = $this->uploader->upload();
        $response = $this->resultJsonFactory->create();
        $response->setData($result);
        return $response;
    }
}
