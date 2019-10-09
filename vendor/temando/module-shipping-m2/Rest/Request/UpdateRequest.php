<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request;

use Temando\Shipping\Rest\Adapter\OrderApiInterface;
use Temando\Shipping\Rest\Request\Type\Generic\UpdateOperation;
use Temando\Shipping\Rest\Request\Type\OrderRequestTypeInterface;

/**
 * Temando API Update Operation Parameters
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class UpdateRequest implements UpdateRequestInterface
{
    /**
     * The remote entity identifier to update
     *
     * @var string
     */
    private $entityId;

    /**
     * The operations to perform on the remote entity
     *
     * @var UpdateOperation[]
     */
    private $operations;

    /**
     * UpdateRequest constructor.
     * @param string $entityId
     * @param UpdateOperation[] $operations
     */
    public function __construct($entityId, array $operations)
    {
        $this->entityId = $entityId;
        $this->operations = $operations;
    }

    /**
     * @return string[]
     */
    public function getPathParams()
    {
        return [
            $this->entityId,
        ];
    }

    /**
     * @return string
     */
    public function getRequestBody()
    {
        return json_encode($this->operations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
