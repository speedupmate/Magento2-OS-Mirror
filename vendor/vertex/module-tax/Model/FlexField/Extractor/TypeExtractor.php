<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\FlexField\Extractor;

use Magento\Framework\Exception\NotFoundException;
use ReflectionException;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FlexibleFieldSource;

/**
 * Extract return type of a method
 */
class TypeExtractor
{
    /**
     * Retrieve the return type of a method
     *
     * @param $className
     * @param $method
     * @return string
     * @throws NotFoundException
     */
    public function extract($className, $method)
    {
        try {
            $refClass = new \ReflectionClass($className);
            preg_match('/@return\s+(\S+)/', $refClass->getMethod($method)->getDocComment(), $matches);
            $type = explode('|', $matches['1']);

            return in_array('int', $type, true)
            || in_array('float', $type, true) ? FlexibleFieldSource::TYPE_NUMERIC : FlexibleFieldSource::TYPE_CODE;
        } catch (ReflectionException $e) {
            throw new NotFoundException(__('Provided class name %1 was not located', $className));
        }
    }
}
