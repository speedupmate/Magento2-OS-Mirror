<?php

namespace Laminas\View\Helper;

/**
 * Helper for escaping values
 */
class EscapeCss extends Escaper\AbstractHelper
{
    /**
     * Escape a value for current escaping strategy
     *
     * @param  string $value
     * @return string
     */
    protected function escape($value)
    {
        return $this->getEscaper()->escapeCss($value);
    }
}
