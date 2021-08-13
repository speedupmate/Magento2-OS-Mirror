<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    /**
     * The status column will be rendered according to the http status response code.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            switch ($item['status']) {
                case 200:
                case 201:
                case 204:
                    $class = 'notice';
                    break;
                case 404:
                case 500:
                    $class = 'critical';
                    break;
                default:
                    $class = 'minor';
                    break;
            }
            $item['status'] = '<span class="grid-severity-' . $class . '">' . $item['status'] . '</span>';
        }

        return $dataSource;
    }
}
