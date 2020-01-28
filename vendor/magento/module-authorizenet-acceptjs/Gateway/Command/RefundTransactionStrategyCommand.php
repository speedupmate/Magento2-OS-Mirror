<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Chooses the best method of returning the payment based on the status of the transaction
 *
 * @deprecated Starting from Magento 2.2.11 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class RefundTransactionStrategyCommand implements CommandInterface
{
    /**
     * @var string
     */
    private static $refund = 'refund_settled';

    /**
     * @var string
     */
    private static $void = 'void';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader
    ) {
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        $command = $this->getCommand($commandSubject);

        $this->commandPool->get($command)
            ->execute($commandSubject);
    }

    /**
     * Determines the command that should be used based on the status of the transaction
     *
     * @param array $commandSubject
     * @return string
     * @throws CommandException
     */
    private function getCommand(array $commandSubject): string
    {
        $details = $this->commandPool->get('get_transaction_details')
            ->execute($commandSubject)
            ->get();

        if ($details['transaction']['transactionStatus'] === 'capturedPendingSettlement') {
            return self::$void;
        } elseif ($details['transaction']['transactionStatus'] !== 'settledSuccessfully') {
            throw new CommandException(__('This transaction cannot be refunded with its current status.'));
        }

        return self::$refund;
    }
}
