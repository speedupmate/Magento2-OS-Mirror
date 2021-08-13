<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Klarna\Core\Model\ResourceModel\Log\CollectionFactory;

class Logs extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @param string            $name
     * @param string            $primaryFieldName
     * @param string            $requestFieldName
     * @param CollectionFactory $logsCollectionFactory
     * @param array             $meta
     * @param array             $data
     * @codeCoverageIgnore
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $logsCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $logsCollectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $log) {
            $this->loadedData[$log->getId()] = $this->formatData($log->getData());
        }
        return $this->loadedData;
    }

    /**
     * Formatting data to a more readable structure
     *
     * @param array $data
     * @return array
     */
    private function formatData(array $data): array
    {
        $keysPretty = [
            'request',
            'response'
        ];

        foreach ($keysPretty as $key) {
            $data[$key] = json_encode(json_decode($data[$key], true), JSON_PRETTY_PRINT);
        }

        return $data;
    }
}
