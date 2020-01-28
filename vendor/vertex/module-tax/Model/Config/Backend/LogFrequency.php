<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Backend;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ConfigFactory;

/**
 * Backend for frequency of log rotation
 */
class LogFrequency extends Value
{
    /** @var Config */
    private $configReader;

    /** @var WriterInterface */
    private $configWriter;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param WriterInterface $configWriter
     * @param ConfigFactory $vertexConfigFactory
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        WriterInterface $configWriter,
        ConfigFactory $vertexConfigFactory,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configWriter = $configWriter;
        $this->configReader = $vertexConfigFactory->create(['scopeConfig' => $config]); // Support snapshot config

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Convert the saved configuration values into a valid cron schedule.
     *
     * @return Value
     * @throws CouldNotSaveException
     */
    public function afterSave()
    {
        $time = explode(',', $this->configReader->getCronRotationTime());
        $frequency = $this->configReader->getCronRotationFrequency();
        $cronExprString = $this->createCronExpression($time, $frequency);

        try {
            $this->configWriter->save(Config::CRON_STRING_PATH, $cronExprString);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Failed to write crontab expression.'), $e);
        }

        return parent::afterSave();
    }

    /**
     * Generate a crontab expression from the given data.
     *
     * @param array $timeComponents
     * @param string $frequency
     * @return string
     */
    private function createCronExpression(array $timeComponents = [], $frequency = null)
    {
        $timeComponents = array_pad($timeComponents, 3, 0);

        $expression = [
            (int)$timeComponents[1],
            (int)$timeComponents[0],
            $frequency === Frequency::CRON_MONTHLY ? '1' : '*',
            '*',
            $frequency === Frequency::CRON_WEEKLY ? '1' : '*',
        ];

        return \implode(' ', $expression);
    }
}
