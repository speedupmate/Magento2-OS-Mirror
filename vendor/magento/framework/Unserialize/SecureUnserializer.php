<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Unserialize;

/**
 * Class provides functionality to unserialize data.
 *
 * @deprecated
 */
class SecureUnserializer
{
    /**
     * Unserialize data from string.
     *
     * @param string $string
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function unserialize($string)
    {
        if (preg_match('/[oc]:[+\-]?\d+:"/i', $string)) {
            throw new \InvalidArgumentException('Data contains serialized object and cannot be unserialized');
        }

        return unserialize($string);
    }
}
