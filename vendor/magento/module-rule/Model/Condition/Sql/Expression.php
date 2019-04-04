<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract Rule sql condition
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Rule\Model\Condition\Sql;

class Expression extends \Zend_Db_Expr
{
    /**
     * Turn expression in this object into string
     *
     * @return string
     */
    public function __toString()
    {
        return empty($this->_expression) ? '' : '(' . $this->_expression . ')';
    }
}
