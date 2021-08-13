<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Test\Integration\Logging;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterfaceFactory;
use Vertex\RequestLoggingApi\Api\LogEntryRepositoryInterface;
use Vertex\RequestLogging\Cron\LogRotate;
use Vertex\RequestLogging\Test\Integration\TestCase;

/**
 * Test that log rotation works as designed
 */
class LogEntryRotatorTest extends TestCase
{
    /** @var LogRotate */
    private $logRotate;

    /** @var LogEntryRepositoryInterface */
    private $repository;

    /** @var SearchCriteriaInterface */
    private $emptyCriteria;

    /**
     * Perform test setup.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->logRotate = $this->getObject(LogRotate::class);
        $this->repository = $this->getObject(LogEntryRepositoryInterface::class);
        $this->emptyCriteria = $this->getObject(SearchCriteriaInterface::class);
    }

    /**
     * Test that when the entry lifetime is 1 logs older than 1 day are rotated
     *
     * @magentoConfigFixture default/tax/vertex_logging/enable_rotation 1
     * @magentoConfigFixture default_store tax/vertex_logging/entry_lifetime 1
     * @magentoDataFixture loadFixture
     * @magentoDbIsolation enabled
     *
     * @return void
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function testDeletionWithLifetimeOfOne(): void
    {
        // Sanity Assertion
        $beforeResults = $this->repository->getList($this->emptyCriteria);
        if ($beforeResults->getTotalCount() !== 3) {
            $this->fail('Fixture should have created three entries.  Total amount: '. $beforeResults->getTotalCount());
        }

        $this->logRotate->execute();

        $afterResults = $this->repository->getList($this->emptyCriteria);
        $this->assertEquals(1, $afterResults->getTotalCount());
    }

    /**
     * Test that when the entry lifetime is 2 logs older than 2 days are rotated
     *
     * @magentoConfigFixture default/tax/vertex_logging/enable_rotation 1
     * @magentoConfigFixture default_store tax/vertex_logging/entry_lifetime 2
     * @magentoDataFixture loadFixture
     * @magentoDbIsolation enabled
     *
     * @return void
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function testDeletionWithLifetimeOfTwo(): void
    {
        // Sanity Assertion
        $beforeResults = $this->repository->getList($this->emptyCriteria);
        if ($beforeResults->getTotalCount() !== 3) {
            $this->fail('Fixture should have created three entries.  Total amount: '. $beforeResults->getTotalCount());
        }

        $this->logRotate->execute();

        $afterResults = $this->repository->getList($this->emptyCriteria);
        $this->assertEquals(2, $afterResults->getTotalCount());
    }

    /**
     * Load in test log entries
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public static function loadFixture(): void
    {
        /** @var ObjectManagerInterface $om */
        $om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var LogEntryRepositoryInterface $repository */
        $repository = $om->get(LogEntryRepositoryInterface::class);

        /** @var LogEntryInterfaceFactory $factory */
        $factory = $om->get(LogEntryInterfaceFactory::class);

        $periodTwoHours = new \DateInterval('PT2H');
        $periodTwoDaysTwoHours = new \DateInterval('P1DT2H');
        $periodThreeDaysTwoHours = new \DateInterval('P2DT2H');

        /** @var TimezoneInterface $magentoTimezone */
        $magentoTimezone = $om->get(TimezoneInterface::class);
        $timezone = new \DateTimeZone($magentoTimezone->getConfigTimezone());

        $now = new \DateTimeImmutable('now', $timezone);
        $twoHoursAgo = $now->sub($periodTwoHours);
        $oneDayTwoHoursAgo = $now->sub($periodTwoDaysTwoHours);
        $twoDaysTwoHoursAgo = $now->sub($periodThreeDaysTwoHours);

        /** @var LogEntryInterface $twoHoursAgoEntry */
        $twoHoursAgoEntry = $factory->create();
        $twoHoursAgoEntry->setDate($twoHoursAgo->format('Y-m-d H:i:s'));
        $repository->save($twoHoursAgoEntry);

        /** @var LogEntryInterface $oneDayTwoHoursAgoEntry */
        $oneDayTwoHoursAgoEntry = $factory->create();
        $oneDayTwoHoursAgoEntry->setDate($oneDayTwoHoursAgo->format('Y-m-d H:i:s'));
        $repository->save($oneDayTwoHoursAgoEntry);

        /** @var LogEntryInterface $twoDaysTwoHoursAgoEntry */
        $twoDaysTwoHoursAgoEntry = $factory->create();
        $twoDaysTwoHoursAgoEntry->setDate($twoDaysTwoHoursAgo->format('Y-m-d H:i:s'));
        $repository->save($twoDaysTwoHoursAgoEntry);
    }
}
