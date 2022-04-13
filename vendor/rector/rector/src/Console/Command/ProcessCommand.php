<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use PHPStan\Analyser\NodeScopeResolver;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Reporting\MissingRectorRulesReporter;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use Rector\Core\Validation\EmptyConfigurableRectorChecker;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use Rector\VersionBonding\Application\MissedRectorDueVersionChecker;
use RectorPrefix20211221\Symfony\Component\Console\Application;
use RectorPrefix20211221\Symfony\Component\Console\Command\Command;
use RectorPrefix20211221\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20211221\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20211221\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20211221\Symfony\Component\Console\Style\SymfonyStyle;
final class ProcessCommand extends \Rector\Core\Console\Command\AbstractProcessCommand
{
    /**
     * @readonly
     * @var \Rector\Core\Autoloading\AdditionalAutoloader
     */
    private $additionalAutoloader;
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @readonly
     * @var \Rector\Core\Reporting\MissingRectorRulesReporter
     */
    private $missingRectorRulesReporter;
    /**
     * @readonly
     * @var \Rector\Core\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    /**
     * @readonly
     * @var \Rector\Core\ValueObjectFactory\Application\FileFactory
     */
    private $fileFactory;
    /**
     * @readonly
     * @var \Rector\Core\Autoloading\BootstrapFilesIncluder
     */
    private $bootstrapFilesIncluder;
    /**
     * @readonly
     * @var \Rector\Core\ValueObjectFactory\ProcessResultFactory
     */
    private $processResultFactory;
    /**
     * @readonly
     * @var \PHPStan\Analyser\NodeScopeResolver
     */
    private $nodeScopeResolver;
    /**
     * @readonly
     * @var \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    /**
     * @readonly
     * @var \Rector\VersionBonding\Application\MissedRectorDueVersionChecker
     */
    private $missedRectorDueVersionChecker;
    /**
     * @readonly
     * @var \Rector\Core\Validation\EmptyConfigurableRectorChecker
     */
    private $emptyConfigurableRectorChecker;
    /**
     * @readonly
     * @var \Rector\Core\Console\Output\OutputFormatterCollector
     */
    private $outputFormatterCollector;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var \Rector\Core\Contract\Rector\RectorInterface[]
     * @readonly
     */
    private $rectors;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(\Rector\Core\Autoloading\AdditionalAutoloader $additionalAutoloader, \Rector\Caching\Detector\ChangedFilesDetector $changedFilesDetector, \Rector\Core\Reporting\MissingRectorRulesReporter $missingRectorRulesReporter, \Rector\Core\Application\ApplicationFileProcessor $applicationFileProcessor, \Rector\Core\ValueObjectFactory\Application\FileFactory $fileFactory, \Rector\Core\Autoloading\BootstrapFilesIncluder $bootstrapFilesIncluder, \Rector\Core\ValueObjectFactory\ProcessResultFactory $processResultFactory, \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver, \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator, \Rector\VersionBonding\Application\MissedRectorDueVersionChecker $missedRectorDueVersionChecker, \Rector\Core\Validation\EmptyConfigurableRectorChecker $emptyConfigurableRectorChecker, \Rector\Core\Console\Output\OutputFormatterCollector $outputFormatterCollector, \RectorPrefix20211221\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, array $rectors)
    {
        $this->additionalAutoloader = $additionalAutoloader;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->missingRectorRulesReporter = $missingRectorRulesReporter;
        $this->applicationFileProcessor = $applicationFileProcessor;
        $this->fileFactory = $fileFactory;
        $this->bootstrapFilesIncluder = $bootstrapFilesIncluder;
        $this->processResultFactory = $processResultFactory;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
        $this->missedRectorDueVersionChecker = $missedRectorDueVersionChecker;
        $this->emptyConfigurableRectorChecker = $emptyConfigurableRectorChecker;
        $this->outputFormatterCollector = $outputFormatterCollector;
        $this->symfonyStyle = $symfonyStyle;
        $this->rectors = $rectors;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setDescription('Upgrades or refactors source code with provided rectors');
        $names = $this->outputFormatterCollector->getNames();
        $description = \sprintf('Select output format: "%s".', \implode('", "', $names));
        $this->addOption(\Rector\Core\Configuration\Option::OUTPUT_FORMAT, \Rector\Core\Configuration\Option::OUTPUT_FORMAT_SHORT, \RectorPrefix20211221\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, $description, \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME);
        parent::configure();
    }
    protected function execute(\RectorPrefix20211221\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20211221\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $exitCode = $this->missingRectorRulesReporter->reportIfMissing();
        if ($exitCode !== null) {
            return $exitCode;
        }
        $configuration = $this->configurationFactory->createFromInput($input);
        // disable console output in case of json output formatter
        if ($configuration->getOutputFormat() === \Rector\ChangesReporting\Output\JsonOutputFormatter::NAME) {
            $this->symfonyStyle->setVerbosity(\RectorPrefix20211221\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET);
        }
        // register autoloaded and included files
        $this->bootstrapFilesIncluder->includeBootstrapFiles();
        $this->additionalAutoloader->autoloadInput($input);
        $this->additionalAutoloader->autoloadPaths();
        $paths = $configuration->getPaths();
        // 0. add files and directories to static locator
        $this->dynamicSourceLocatorDecorator->addPaths($paths);
        // 1. inform user about non-runnable rules
        $this->missedRectorDueVersionChecker->check($this->rectors);
        // 2. inform user about registering configurable rule without configuration
        $this->emptyConfigurableRectorChecker->check();
        // 3. collect all files from files+dirs provided paths
        $files = $this->fileFactory->createFromPaths($paths, $configuration);
        // 4. PHPStan has to know about all files too
        $this->configurePHPStanNodeScopeResolver($files);
        // MAIN PHASE
        // 5. run Rector
        $systemErrorsAndFileDiffs = $this->applicationFileProcessor->run($files, $configuration);
        // REPORTING PHASE
        // 6. reporting phase
        // report diffs and errors
        $outputFormat = $configuration->getOutputFormat();
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);
        $processResult = $this->processResultFactory->create($systemErrorsAndFileDiffs);
        $outputFormatter->report($processResult, $configuration);
        // invalidate affected files
        $this->invalidateCacheChangedFiles($processResult);
        return $this->resolveReturnCode($processResult, $configuration);
    }
    protected function initialize(\RectorPrefix20211221\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20211221\Symfony\Component\Console\Output\OutputInterface $output) : void
    {
        $application = $this->getApplication();
        if (!$application instanceof \RectorPrefix20211221\Symfony\Component\Console\Application) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $optionDebug = (bool) $input->getOption(\Rector\Core\Configuration\Option::DEBUG);
        if ($optionDebug) {
            $application->setCatchExceptions(\false);
        }
        // clear cache
        $optionClearCache = (bool) $input->getOption(\Rector\Core\Configuration\Option::CLEAR_CACHE);
        if ($optionDebug || $optionClearCache) {
            $this->changedFilesDetector->clear();
        }
    }
    private function invalidateCacheChangedFiles(\Rector\Core\ValueObject\ProcessResult $processResult) : void
    {
        foreach ($processResult->getChangedFileInfos() as $changedFileInfo) {
            $this->changedFilesDetector->invalidateFile($changedFileInfo);
        }
    }
    private function resolveReturnCode(\Rector\Core\ValueObject\ProcessResult $processResult, \Rector\Core\ValueObject\Configuration $configuration) : int
    {
        // some errors were found → fail
        if ($processResult->getErrors() !== []) {
            return \RectorPrefix20211221\Symfony\Component\Console\Command\Command::FAILURE;
        }
        // inverse error code for CI dry-run
        if (!$configuration->isDryRun()) {
            return \RectorPrefix20211221\Symfony\Component\Console\Command\Command::SUCCESS;
        }
        return $processResult->getFileDiffs() === [] ? \RectorPrefix20211221\Symfony\Component\Console\Command\Command::SUCCESS : \RectorPrefix20211221\Symfony\Component\Console\Command\Command::FAILURE;
    }
    /**
     * @param File[] $files
     */
    private function configurePHPStanNodeScopeResolver(array $files) : void
    {
        $filePaths = $this->resolvePhpFilePaths($files);
        $this->nodeScopeResolver->setAnalysedFiles($filePaths);
    }
    /**
     * @param File[] $files
     * @return string[]
     */
    private function resolvePhpFilePaths(array $files) : array
    {
        $filePaths = [];
        foreach ($files as $file) {
            $smartFileInfo = $file->getSmartFileInfo();
            $pathName = $smartFileInfo->getPathname();
            if (\substr_compare($pathName, '.php', -\strlen('.php')) === 0) {
                $filePaths[] = $pathName;
            }
        }
        return $filePaths;
    }
}
