<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request\Type\Generic;

/**
 * Temando API Update Operation
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class UpdateOperation implements \JsonSerializable
{
    const OPERATION_REPLACE = 'replace';

    /**
     * @var string
     */
    private $operation;

    /**
     * @var string
     */
    private $path;

    /**
     * @var mixed
     */
    private $value;

    /**
     * UpdateOperation constructor.
     * @param string $operation
     * @param string $path
     * @param mixed $value
     */
    public function __construct($operation, $path, $value)
    {
        $this->operation = $operation;
        $this->path = $path;
        $this->value = $value;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'op' => $this->operation,
            'path' => $this->path,
            'value' => $this->value,
        ];
    }
}
