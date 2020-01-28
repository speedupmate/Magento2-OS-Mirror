<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\FunctionalTestingFramework\Console;

use Magento\FunctionalTestingFramework\Exceptions\TestFrameworkException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RunManifestCommand extends Command
{
    /**
     * The return code. Determined by all tests that run.
     *
     * @var integer
     */
    private $returnCode = 0;

    /**
     * A list of tests that failed.
     * Eg: "tests/functional/tests/MFTF/_generated/default/AdminLoginTestCest.php:AdminLoginTest"
     *
     * @var string[]
     */
    private $failedTests = [];

    /**
     * Configure the run:manifest command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName("run:manifest")
            ->setDescription("runs a manifest file")
            ->addArgument("path", InputArgument::REQUIRED, "path to a manifest file");
    }

    /**
     * Executes the run:manifest command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws TestFrameworkException
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument("path");

        if (!file_exists($path)) {
            throw new TestFrameworkException("Could not find file $path. Check the path and try again.");
        }

        $manifestFile = file($path, FILE_IGNORE_NEW_LINES);

        // Delete the Codeception failed file just in case it exists from any previous test runs
        $this->deleteFailedFile();

        foreach ($manifestFile as $manifestLine) {
            if (empty($manifestLine)) {
                continue;
            }

            $this->runManifestLine($manifestLine, $output);
            $this->aggregateFailed();
        }

        if (!empty($this->failedTests)) {
            $this->deleteFailedFile();
            $this->writeFailedFile();
        }

        return $this->returnCode;
    }

    /**
     * Runs a test (or group) line from the manifest file
     *
     * @param string          $manifestLine
     * @param OutputInterface $output
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) Need this because of the unused $type variable in the closure
     */
    private function runManifestLine(string $manifestLine, OutputInterface $output)
    {
        $codeceptionCommand = realpath(PROJECT_ROOT . "/vendor/bin/codecept")
            . " run functional --verbose --steps "
            . $manifestLine;

        // run the codecept command in a sub process
        $process = new Process($codeceptionCommand);
        $process->setWorkingDirectory(TESTS_BP);
        $process->setIdleTimeout(600);
        $process->setTimeout(0);
        $subReturnCode = $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });
        $this->returnCode = max($this->returnCode, $subReturnCode);
    }

    /**
     * Keeps track of any tests that failed while running the manifest file.
     *
     * Each codecept command executions overwrites the failed file. Since we are running multiple codecept commands,
     * we need to hold on to any failures in order to write a final failed file containing all tests.
     *
     * @return void
     */
    private function aggregateFailed()
    {
        if (file_exists(RunTestFailedCommand::TESTS_FAILED_FILE)) {
            $currentFile = file(RunTestFailedCommand::TESTS_FAILED_FILE, FILE_IGNORE_NEW_LINES);
            $this->failedTests = array_merge(
                $this->failedTests,
                $currentFile
            );
        }
    }

    /**
     * Delete the Codeception failed file.
     *
     * @return void
     */
    private function deleteFailedFile()
    {
        if (file_exists(RunTestFailedCommand::TESTS_FAILED_FILE)) {
            unlink(RunTestFailedCommand::TESTS_FAILED_FILE);
        }
    }

    /**
     * Writes any tests that failed to the Codeception failed file.
     *
     * @return void
     */
    private function writeFailedFile()
    {
        foreach ($this->failedTests as $test) {
            file_put_contents(RunTestFailedCommand::TESTS_FAILED_FILE, $test . "\n", FILE_APPEND);
        }
    }
}
