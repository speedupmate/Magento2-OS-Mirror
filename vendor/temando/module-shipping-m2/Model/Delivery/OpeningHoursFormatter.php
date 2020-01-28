<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Delivery;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Temando\Shipping\Model\Config\ConfigAccessor;

/**
 * Temando Delivery Location Opening Hours Formatter
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OpeningHoursFormatter
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var ConfigAccessor
     */
    private $config;

    /**
     * @var TimezoneInterface
     */
    private $date;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * OpeningHoursFormatter constructor.
     *
     * @param ScopeResolverInterface $scopeResolver
     * @param ResolverInterface $localeResolver
     * @param ConfigAccessor $config
     * @param TimezoneInterface $date
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ScopeResolverInterface $scopeResolver,
        ResolverInterface $localeResolver,
        ConfigAccessor $config,
        TimezoneInterface $date,
        SerializerInterface $serializer
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->localeResolver = $localeResolver;
        $this->config = $config;
        $this->date = $date;
        $this->serializer = $serializer;
    }

    /**
     * Return first day of the week
     *
     * @return int
     */
    private function getFirstDay()
    {
        return (int)$this->config->getConfigValue(
            'general/locale/firstday',
            $this->scopeResolver->getScope()->getId()
        );
    }

    /**
     * Format general opening hours, e.g.:
     * - Monday, Tuesday | 9:00 AM - 8:00 PM
     * - Montag, Dienstag | 09:00 - 20:00
     *
     * @param string[] $openingHours
     * @param string $locale
     * @return string[]
     */
    private function formatGeneralOpenings(array $openingHours, string $locale): array
    {
        $firstDay = $this->getFirstDay();

        // sort opening hours by day
        $fnDaySort = function ($dayA, $dayB) use ($firstDay, $locale) {
            $dayA = (int)$this->date->date($dayA, $locale, false, false)->format('w');
            $dayB = (int)$this->date->date($dayB, $locale, false, false)->format('w');

            if ($dayA < $firstDay) {
                $dayA = $firstDay + 7;
            }

            if ($dayB < $firstDay) {
                $dayB = $firstDay + 7;
            }

            return $dayA > $dayB;
        };

        // sort one day's opening hours by start time
        $fnTimeSort = function ($rangeA, $rangeB) {
            return $rangeA['from'] > $rangeB['from'];
        };

        uksort($openingHours, $fnDaySort);

        // aggregate days with the same opening hours
        $hoursMap = [];
        foreach ($openingHours as $day => $ranges) {
            usort($ranges, $fnTimeSort);
            $key = crc32($this->serializer->serialize($ranges));

            if (!isset($hoursMap[$key])) {
                $hoursMap[$key] = [
                    'days' => [],
                    'ranges' => $ranges,
                ];
            }

            $day = $this->date->date($day, $locale, false, false)->format('l');
            $hoursMap[$key]['days'][] = $day;
        }

        // localize times
        $generalOpenings = [];
        foreach ($hoursMap as $key => $details) {
            $days = implode(', ', $details['days']);
            $times = [];

            foreach ($details['ranges'] as $range) {
                $dateOpens = $this->date->date($range['from'], $locale, false, true);
                $dateCloses = $this->date->date($range['to'], $locale, false, true);

                $timeOpens = $this->date->formatDateTime(
                    $dateOpens,
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::SHORT,
                    $locale,
                    'UTC'
                );
                $timeCloses = $this->date->formatDateTime(
                    $dateCloses,
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::SHORT,
                    $locale,
                    'UTC'
                );

                $times[]= sprintf('%s - %s', $timeOpens, $timeCloses);
            }

            $generalOpenings[] = [
                'days' => $days,
                'times' => implode(', ', $times),
            ];
        }

        return $generalOpenings;
    }

    /**
     * Format specifics, e.g.:
     * - Dec 24 9:00 AM - Jan 1 8:00 PM
     * - Jul 4 9:00 AM - 8:00 PM
     * - 24.12. 09:00 - 01.01. 20:00
     * - 03.10. 09:00 - 20:00
     *
     * @param string[] $openingHours
     * @param string $locale
     * @param int $offset
     * @return string[]
     */
    private function formatSpecifics(array $openingHours, string $locale, $offset = 7): array
    {
        $formattedSpecifics = [];
        $today = $this->date->date();

        $fnDateSort = function ($dateA, $dateB) {
            $dateA = preg_filter('/[^\d]/', '', $dateA['from']);
            $dateB = preg_filter('/[^\d]/', '', $dateB['from']);
            return $dateA > $dateB;
        };
        usort($openingHours, $fnDateSort);

        foreach ($openingHours as $opening) {
            $dateFrom = $this->date->date($opening['from'], $locale, false, true);
            $dateTo = $this->date->date($opening['to'], $locale, false, true);

            $timeZone = preg_match('/([\-\+]{1}[0-9]{2}:[0-9]{2})$/', $opening['from'], $matches) ?
                sprintf('GMT%s', array_pop($matches)) :
                'UTC';

            $diff = $today->diff($dateFrom);
            if (!$diff->invert && ($diff->days > $offset)) {
                // do not display any specifics beginning more than $offset days in the future
                continue;
            }

            $diff = $today->diff($dateTo);
            if ($diff->invert) {
                // do not display any specifics ending in the past
                continue;
            }

            // extract year, will be stripped off later
            $dateFromYear = $dateFrom->format('Y');
            $dateToYear = $dateTo->format('Y');

            // start date, date part
            $dateFromDate = $this->date->formatDateTime(
                $dateFrom,
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE,
                $locale,
                $timeZone
            );
            // end date, date part
            $dateToDate = $this->date->formatDateTime(
                $dateTo,
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE,
                $locale,
                $timeZone
            );

            // start date, time part
            $dateFromTime = $timeOpens = $this->date->formatDateTime(
                $dateFrom,
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::SHORT,
                $locale,
                $timeZone
            );
            // end date, time part
            $dateToTime = $timeOpens = $this->date->formatDateTime(
                $dateTo,
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::SHORT,
                $locale,
                $timeZone
            );

            $dateFromDate = trim(str_replace($dateFromYear, '', $dateFromDate), ' ,');
            $dateToDate = trim(str_replace($dateToYear, '', $dateToDate), ' ,');

            $formattedDateFrom = sprintf('%s %s', $dateFromDate, $dateFromTime);
            $formattedDateTo = sprintf('%s %s', $dateToDate, $dateToTime);
            if ($dateFromDate == $dateToDate) {
                $formattedDateTo = $dateToTime;
            }

            $formattedSpecifics[] = [
                'description' => __($opening['description']),
                'from' => $formattedDateFrom,
                'to' => $formattedDateTo,
            ];
        }

        return $formattedSpecifics;
    }

    /**
     * Combine and format opening hours.
     *
     * @param string[] $openingHours
     *
     * @return string[]
     */
    public function format(array $openingHours): array
    {
        $locale = $this->localeResolver->getLocale();

        $formattedOpenings = [];
        if (isset($openingHours['general'])) {
            $formattedOpenings = $this->formatGeneralOpenings($openingHours['general'], $locale);
        }

        $formattedSpecifics = [
            'openings' => [],
            'closures' => [],
        ];
        if (isset($openingHours['specific']) && isset($openingHours['specific']['openings'])) {
            $formattedSpecifics['openings'] = $this->formatSpecifics($openingHours['specific']['openings'], $locale);
        }
        if (isset($openingHours['specific']) && isset($openingHours['specific']['closures'])) {
            $formattedSpecifics['closures'] = $this->formatSpecifics($openingHours['specific']['closures'], $locale);
        }

        $formatted = [
            'general'  => $formattedOpenings,
            'specific' => $formattedSpecifics,
        ];

        return $formatted;
    }
}
