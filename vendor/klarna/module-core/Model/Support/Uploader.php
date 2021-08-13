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

namespace Klarna\Core\Model\Support;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Exception\FileSystemException;

class Uploader
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var UploaderFactory
     */
    private $fileUploaderFactory;
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param Filesystem       $filesystem
     * @param UploaderFactory  $fileUploaderFactory
     * @param ManagerInterface $messageManager
     * @codeCoverageIgnore
     */
    public function __construct(
        Filesystem       $filesystem,
        UploaderFactory  $fileUploaderFactory,
        ManagerInterface $messageManager
    ) {
        $this->filesystem          = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->messageManager      = $messageManager;
    }

    /**
     * We will save any uploads from the form to a directory under var/
     *
     * @return array
     * @throws FileSystemException
     */
    public function upload(): array
    {
        $result    = [];
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $target    = $directory->getAbsolutePath('/klarna');
        $uploader  = $this->fileUploaderFactory->create(['fileId' => 'attachment']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'txt' , 'log', 'pdf', 'zip', 'rar']);
        $uploader->setAllowRenameFiles(true);

        try {
            $result = $uploader->save($target);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $result;
    }
}
