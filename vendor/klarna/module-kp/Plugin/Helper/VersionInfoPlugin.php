<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Plugin\Helper;

use Klarna\Core\Helper\VersionInfo;

class VersionInfoPlugin
{
    /**
     * Adds own module name and version
     *
     * @param VersionInfo $subject
     * @param string $result
     * @param string $version
     * @param string $caller
     * @return string
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterGetModuleVersionString(
        VersionInfo $subject,
        string $result,
        string $version,
        string $caller
    ): string {
        if ($caller === 'Klarna_Kp') {
            return $result;
        }
        return sprintf(
            "%s;Kp/%s",
            $result,
            $subject->getVersion('Klarna_Kp')
        );
    }
}
