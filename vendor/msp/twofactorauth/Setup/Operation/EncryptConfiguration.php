<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace MSP\TwoFactorAuth\Setup\Operation;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class EncryptConfiguration
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * Encrypt existing users configuration
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function execute(ModuleDataSetupInterface $moduleDataSetup): void
    {
        $tfaConfigTableName = $moduleDataSetup->getTable('msp_tfa_user_config');
        $connection = $moduleDataSetup->getConnection();

        $qry = $connection->select()->from($tfaConfigTableName);
        $configurations = $connection->fetchAll($qry);

        foreach ($configurations as $configuration) {
            $connection->update(
                $tfaConfigTableName,
                ['encoded_config' => $this->encryptor->encrypt($configuration['encoded_config'])],
                $connection->quoteInto('msp_tfa_user_config_id = ?', $configuration['msp_tfa_user_config_id'])
            );
        }
    }
}
