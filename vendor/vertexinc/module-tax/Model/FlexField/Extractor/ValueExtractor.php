<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\FlexField\Extractor;

use Exception;
use Vertex\Tax\Model\DateTimeImmutableFactory;
use Vertex\Tax\Model\ExceptionLogger;

/**
 * Extract item value
 */
class ValueExtractor
{
    /** @var DateTimeImmutableFactory */
    private $dateTimeFactory;

    /** @var ExceptionLogger */
    private $logger;

    /**
     * @param DateTimeImmutableFactory $dateTimeFactory
     * @param ExceptionLogger $logger
     */
    public function __construct(DateTimeImmutableFactory $dateTimeFactory, ExceptionLogger $logger)
    {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->logger = $logger;
    }

    /**
     * Retrieve value from an object
     *
     * @param object $item Object to call method against
     * @param string $attributeCode Method name to retrieve value
     * @param string $prefix Prefix on attribute code
     * @param string[] $dateFields Fields that should be processed as a date
     * @return string|int|null
     */
    public function extract($item, $attributeCode, $prefix, array $dateFields = [])
    {
        try {
            $optionGroup = $prefix . '.';
            if (strpos($attributeCode, $optionGroup) === 0) {
                $methodName = substr($attributeCode, strlen($optionGroup));
                $result = $item->{$methodName}();
                if (in_array($methodName, $dateFields, true)) {
                    return $result ? $this->dateTimeFactory->create($result) : null;
                }
                return $result;
            }
        } catch (Exception $exception) {
            $this->logger->warning($exception);
        }
        return null;
    }
}
