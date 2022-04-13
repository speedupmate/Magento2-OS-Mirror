<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Resources\Icons;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface;
use Ssch\TYPO3Rector\Helper\FilesFinder;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20211221\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Feature-77349-AdditionalLocationsForExtensionIcons.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Resources\Icons\IconsProcessor\IconsProcessorTest
 */
final class IconsFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var string
     */
    private const EXT_ICON_NAME = 'ext_icon';
    /**
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface[]
     */
    private $iconsRector;
    /**
     * @param IconRectorInterface[] $iconsRector
     */
    public function __construct(\Ssch\TYPO3Rector\Helper\FilesFinder $filesFinder, \RectorPrefix20211221\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, array $iconsRector)
    {
        $this->filesFinder = $filesFinder;
        $this->smartFileSystem = $smartFileSystem;
        $this->iconsRector = $iconsRector;
    }
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        foreach ($this->iconsRector as $iconRector) {
            $iconRector->refactorFile($file);
        }
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if ($this->shouldSkip($smartFileInfo->getFilenameWithoutExtension())) {
            return \false;
        }
        $extEmConfSmartFileInfo = $this->filesFinder->findExtEmConfRelativeFromGivenFileInfo($smartFileInfo);
        if (!$extEmConfSmartFileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
            return \false;
        }
        return !$this->smartFileSystem->exists($this->createIconPath($file));
    }
    public function getSupportedFileExtensions() : array
    {
        return ['png', 'gif', 'svg'];
    }
    private function createIconPath(\Rector\Core\ValueObject\Application\File $file) : string
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $realPath = $smartFileInfo->getRealPathDirectory();
        $relativeTargetFilePath = \sprintf('/Resources/Public/Icons/Extension.%s', $smartFileInfo->getExtension());
        return $realPath . $relativeTargetFilePath;
    }
    private function shouldSkip(string $filenameWithoutExtension) : bool
    {
        if (self::EXT_ICON_NAME === $filenameWithoutExtension) {
            return \false;
        }
        return !(\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun() && \strpos($filenameWithoutExtension, self::EXT_ICON_NAME) !== \false);
    }
}
