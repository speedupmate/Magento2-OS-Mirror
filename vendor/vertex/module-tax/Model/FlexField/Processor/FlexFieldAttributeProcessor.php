<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Framework\Exception\NotFoundException;
use Vertex\Tax\Model\FlexField\Cache\ProcessorKeyManager;
use Vertex\Tax\Model\FlexField\Exception\ProcessorNotFoundException;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;

/**
 * Flexible Field attribute processor
 */
class FlexFieldAttributeProcessor implements ProcessorInterface
{
    /** @var ProcessorKeyManager */
    private $processorKeyManager;

    /** @var array */
    private $processors;

    /** @var FlexFieldProcessableAttribute[]|null */
    private $attributes;

    /** @var  ProcessorInterface[] */
    private $processorCache;

    /**
     * @param array $processors
     * @param ProcessorKeyManager $processorKeyManager
     */
    public function __construct(ProcessorKeyManager $processorKeyManager, array $processors = [])
    {
        $this->processorKeyManager = $processorKeyManager;
        $this->processors = $processors;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        if ($this->attributes !== null) {
            return $this->attributes;
        }

        $processorsHash = $this->processorKeyManager->createProcessorsHash($this->processors);
        if ($this->attributes = $this->processorKeyManager->get($processorsHash)) {
            return $this->attributes;
        }

        $this->attributes = [];

        // sort processors
        usort(
            $this->processors,
            static function ($a, $b) {
                return $a['sort-order'] - $b['sort-order'];
            }
        );

        foreach ($this->processors as $processorData) {
            /** @var ProcessorInterface $processor */
            $processor = $processorData['processor'];
            $this->attributes[] = $processor->getAttributes();
        }

        $this->attributes = !empty($this->attributes) ? array_merge(...$this->attributes) : [];

        $this->processorKeyManager->set($processorsHash, $this->attributes);

        return $this->attributes;
    }

    /**
     * Retrieve processor by attribute code
     *
     * @param string $attributeCode
     * @return ProcessorInterface
     * @throws NotFoundException
     */
    public function getProcessorByAttributeCode($attributeCode)
    {
        if (!empty($this->processorCache[$attributeCode])) {
            return $this->processorCache[$attributeCode];
        }
        $attributes = $this->getAttributes();
        $processors = array_column($this->processors, 'processor');
        foreach ($processors as $key => $value) {
            $processors[get_class($value)] = $value;
            unset($processors[$key]);
        }

        if (!isset($attributes[$attributeCode])) {
            throw new NotFoundException(
                __(
                    'Attribute code %1 is not available or the attributes array from its processor is not '
                    . 'properly indexed by attribute code',
                    $attributeCode
                )
            );
        }
        $processorClassName = $attributes[$attributeCode]->getProcessor();
        if ($this->processorCache[$attributeCode] = $processors[$processorClassName]) {
            return $this->processorCache[$attributeCode];
        }
        throw new NotFoundException(__('Processor for attribute code %1 not found', $attributeCode));
    }

    /**
     * Retrieve attribute by code
     *
     * @param string $code
     * @return FlexFieldProcessableAttribute
     * @throws NotFoundException
     */
    public function getAttributeByCode($code)
    {
        $attributes = $this->getAttributes();
        if (isset($attributes[$code])) {
            return $attributes[$code];
        }
        throw new NotFoundException(__('Attribute code %1 not found ', $code));
    }
}
