<?php

declare (strict_types=1);
namespace RectorPrefix20211221\Symplify\PackageBuilder\Console\Command;

use RectorPrefix20211221\Symfony\Component\Console\Command\Command;
use RectorPrefix20211221\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20211221\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20211221\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix20211221\Symplify\PackageBuilder\ValueObject\Option;
use RectorPrefix20211221\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20211221\Symplify\SmartFileSystem\Finder\SmartFinder;
use RectorPrefix20211221\Symplify\SmartFileSystem\SmartFileSystem;
abstract class AbstractSymplifyCommand extends \RectorPrefix20211221\Symfony\Component\Console\Command\Command
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $symfonyStyle;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    protected $smartFileSystem;
    /**
     * @var \Symplify\SmartFileSystem\Finder\SmartFinder
     */
    protected $smartFinder;
    /**
     * @var \Symplify\SmartFileSystem\FileSystemGuard
     */
    protected $fileSystemGuard;
    public function __construct()
    {
        parent::__construct();
        $this->addOption(\RectorPrefix20211221\Symplify\PackageBuilder\ValueObject\Option::CONFIG, 'c', \RectorPrefix20211221\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Path to config file');
    }
    /**
     * @required
     */
    public function autowire(\RectorPrefix20211221\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, \RectorPrefix20211221\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \RectorPrefix20211221\Symplify\SmartFileSystem\Finder\SmartFinder $smartFinder, \RectorPrefix20211221\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard) : void
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
        $this->smartFinder = $smartFinder;
        $this->fileSystemGuard = $fileSystemGuard;
    }
}
