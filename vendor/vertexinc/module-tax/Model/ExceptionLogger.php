<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

/**
 * Logs Exceptions
 *
 * We use this class to log exceptions while taking type-safety into account.
 */
class ExceptionLogger
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log a Warning
     *
     * @param \Exception $exception
     * @return void
     */
    public function warning(\Exception $exception)
    {
        do {
            $this->logger->warning($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        } while ($exception = $exception->getPrevious());
    }

    /**
     * Log a Critical Issue
     *
     * @param \Exception $exception
     * @return void
     */
    public function critical(\Exception $exception)
    {
        do {
            $this->logger->critical($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        } while ($exception = $exception->getPrevious());
    }

    /**
     * Log an Error
     *
     * @param \Exception $exception
     * @return void
     */
    public function error(\Exception $exception)
    {
        do {
            $this->logger->error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        } while ($exception = $exception->getPrevious());
    }
}
