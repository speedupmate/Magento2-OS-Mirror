<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Factory for wrapper of GraphQl ResolveInfo
 */
class ResolveInfoFactory
{
    /**
     * Create a wrapper resolver info from the instance of the library object
     *
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @return ResolveInfo
     */
    public function create(\GraphQL\Type\Definition\ResolveInfo $info) : ResolveInfo
    {
        $values = [];
        foreach (get_object_vars($info) as $key => $value) {
            $values[$key] = $value;
        }

        return new ResolveInfo($values);
    }
}
