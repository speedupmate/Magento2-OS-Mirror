<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;
use Magento\Framework\ShellInterface;

/**
 * Manager works with cron tasks
 */
class CrontabManager implements CrontabManagerInterface
{
    /**
     * @var ShellInterface
     */
    private $shell;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param ShellInterface $shell
     * @param Filesystem $filesystem
     */
    public function __construct(
        ShellInterface $shell,
        Filesystem $filesystem
    ) {
        $this->shell = $shell;
        $this->filesystem = $filesystem;
    }

    /**
     * @return string
     */
    private function getTasksBlockStart()
    {
        $tasksBlockStart = self::TASKS_BLOCK_START;
        if (defined('BP')) {
            $tasksBlockStart .= ' ' . md5(BP);
        }
        return $tasksBlockStart;
    }

    /**
     * @return string
     */
    private function getTasksBlockEnd()
    {
        $tasksBlockEnd = self::TASKS_BLOCK_END;
        if (defined('BP')) {
            $tasksBlockEnd .= ' ' . md5(BP);
        }
        return $tasksBlockEnd;
    }

    /**
     * {@inheritdoc}
     */
    public function getTasks()
    {
        $this->checkSupportedOs();
        $content = $this->getCrontabContent();
        $pattern = '!(' . $this->getTasksBlockStart() . ')(.*?)(' . $this->getTasksBlockEnd() . ')!s';

        if (preg_match($pattern, $content, $matches)) {
            $tasks = trim($matches[2], PHP_EOL);
            $tasks = explode(PHP_EOL, $tasks);
            return $tasks;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function saveTasks(array $tasks)
    {
        if (!$tasks) {
            throw new LocalizedException(new Phrase('List of tasks is empty'));
        }

        $this->checkSupportedOs();
        $baseDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        $logDir = $this->filesystem->getDirectoryRead(DirectoryList::LOG)->getAbsolutePath();

        foreach ($tasks as $key => $task) {
            if (empty($task['expression'])) {
                $tasks[$key]['expression'] = '* * * * *';
            }

            if (empty($task['command'])) {
                throw new LocalizedException(new Phrase('Command should not be empty'));
            }

            $tasks[$key]['command'] = str_replace(
                ['{magentoRoot}', '{magentoLog}'],
                [$baseDir, $logDir],
                $task['command']
            );
        }

        $content = $this->getCrontabContent();
        $content = $this->cleanMagentoSection($content);
        $content = $this->generateSection($content, $tasks);

        $this->save($content);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function removeTasks()
    {
        $this->checkSupportedOs();
        $content = $this->getCrontabContent();
        $content = $this->cleanMagentoSection($content);
        $this->save($content);
    }

    /**
     * Generate Magento Tasks Section
     *
     * @param string $content
     * @param array $tasks
     * @return string
     */
    private function generateSection($content, $tasks = [])
    {
        if ($tasks) {
            $content .= $this->getTasksBlockStart() . PHP_EOL;
            foreach ($tasks as $task) {
                $content .= $task['expression'] . ' ' . PHP_BINARY . ' ' . $task['command'] . PHP_EOL;
            }
            $content .= $this->getTasksBlockEnd() . PHP_EOL;
        }

        return $content;
    }

    /**
     * Clean Magento Tasks Section in crontab content
     *
     * @param string $content
     * @return string
     */
    private function cleanMagentoSection($content)
    {
        $content = preg_replace(
            '!' . preg_quote($this->getTasksBlockStart()) . '.*?'
            . preg_quote($this->getTasksBlockEnd() . PHP_EOL) . '!s',
            '',
            $content
        );

        return $content;
    }

    /**
     * Get crontab content without Magento Tasks Section
     *
     * In case of some exceptions the empty content is returned
     *
     * @return string
     */
    private function getCrontabContent()
    {
        try {
            $content = (string)$this->shell->execute('crontab -l');
        } catch (LocalizedException $e) {
            return '';
        }

        return $content;
    }

    /**
     * Save crontab
     *
     * @param string $content
     * @return void
     * @throws LocalizedException
     */
    private function save($content)
    {
        $content = str_replace(['%', '"'], ['%%', '\"'], $content);

        try {
            $this->shell->execute('echo "' . $content . '" | crontab -');
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                new Phrase('Error during saving of crontab: %1', [$e->getPrevious()->getMessage()]),
                $e
            );
        }
    }

    /**
     * Check that OS is supported
     *
     * If OS is not supported then no possibility to work with crontab
     *
     * @return void
     * @throws LocalizedException
     */
    private function checkSupportedOs()
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            throw new LocalizedException(
                new Phrase('Your operating system is not supported to work with this command')
            );
        }
    }
}
