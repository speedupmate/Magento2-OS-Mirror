<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Stdlib\DateTime;
use Vertex\Tax\Api\Data\LogEntryInterface;

/**
 * Write LogEntryInterface data to flat file data.
 */
class LogEntryExport
{
    /** Open a file and empty its contents if they exists */
    const MODE_CLEAN_FILE = 'w';

    /** Open a file and append its contents if they exist */
    const MODE_APPEND_FILE = 'a';

    /** @var DateTime */
    private $dateTime;

    /** @var WriteInterface */
    private $directoryWrite;

    /** @var string */
    private $file;

    /** @var \Magento\Framework\Filesystem\File\WriteInterface */
    private $stream;

    /**
     * @param Filesystem $fileSystem
     * @param DateTime $dateTime
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $fileSystem,
        DateTime $dateTime
    ) {
        $this->directoryWrite = $fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->dateTime = $dateTime;
    }

    /**
     * Close the export file and return its final path.
     *
     * @return string
     */
    public function close()
    {
        $path = $this->file;

        $this->stream->close();
        $this->file = '';

        return $path;
    }

    /**
     * Open a new export file for writing.
     *
     * @param string|null $filename
     * @param string $mode One of {@see LogEntryArchive::MODE_APPEND_FILE}, {@see LogEntryArchive::MODE_CLEAN_FILE}
     * @return void
     * @throws FileSystemException
     */
    public function open($filename = null, $mode = self::MODE_APPEND_FILE)
    {
        if (!empty($this->file)) {
            return;
        }

        if ($filename === null) {
            $filename = $this->getFilename();
        }

        $this->file = rtrim($this->getBasePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        $this->directoryWrite->touch(
            $this->directoryWrite->getRelativePath($this->file)
        );

        $this->stream = $this->directoryWrite->openFile($this->file, $mode);
    }

    /**
     * Write the given log entry record to the current target file.
     *
     * @param LogEntryInterface|\Vertex\Tax\Model\Data\LogEntry $record
     * @return void
     * @throws NotFoundException
     * @throws FileSystemException
     */
    public function write(LogEntryInterface $record)
    {
        if (empty($this->file)) {
            throw new NotFoundException(__('Cannot write log entry because no export file is open.'));
        }

        $this->stream->writeCsv(
            [
                $record->getType(),
                $record->getDate(),
                $record->getSubTotal(),
                $record->getTotalTax(),
                $record->getTotal(),
                $record->getRequestXml(),
                $record->getResponseXml()
            ]
        );
    }

    /**
     * Write the header to the current target file
     *
     * @return void
     * @throws FileSystemException
     * @throws NotFoundException
     */
    public function writeHeader()
    {
        if (empty($this->file)) {
            throw new NotFoundException(__('Cannot write log entry because no export file is open.'));
        }

        $this->stream->writeCsv(
            [
                'Request Type',
                'Request Date',
                'Subtotal',
                'Total Tax',
                'Total',
                'Request XML',
                'Response XML'
            ]
        );
    }

    /**
     * Get the log entry base storage path.
     *
     * @return string
     */
    private function getBasePath()
    {
        return $this->directoryWrite->getAbsolutePath();
    }

    /**
     * Generate a log entry filename.
     *
     * @return string
     */
    private function getFilename()
    {
        return sprintf('vertexlog_%s.csv', $this->dateTime->formatDate(time(), false));
    }
}
