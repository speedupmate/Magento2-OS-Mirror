<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Ordermanagement\Plugin\Helper;

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
        if ($caller === 'Klarna_Ordermanagement') {
            return $result;
        }
        return sprintf(
            "%s;OM/%s",
            $result,
            $subject->getVersion('Klarna_Ordermanagement')
        );
    }
}
