<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Data;

use Vertex\Tax\Api\Data\CommodityCodeInterface;

/**
 * Class for Commodity Code extension attributes.
 * Generic class to be used in dependency injection.
 */
class CommodityCode implements CommodityCodeInterface
{
    /** @var string */
    private $code;

    /** @var string */
    private $type;

    /**
     * Get commodity code
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Get commodity code type
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set commodity code
     *
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * Get commodity code type
     *
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }
}
