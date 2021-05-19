<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource Model for Quote ID Masks
 *
 * This model is necessary so that we do not have a dependency on the minor
 * version of magento/module-quote.  While the resource model is not API, the
 * table is.
 *
 * @see \Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask
 */
class QuoteIdMask extends AbstractDb
{
    const QUOTE_ATTR_ENTITY_ID = 'entity_id';
    const QUOTE_ATTR_IS_ACTIVE = 'is_active';
    const QUOTE_MASK_ATTR_ENTITY_ID = 'quote_id';
    const QUOTE_MASK_ATTR_MASKED_ID = 'masked_id';
    const TABLE_QUOTE = 'quote';
    const TABLE_QUOTE_ID_MASK = 'quote_id_mask';

    protected function _construct()
    {
        $this->_init(static::TABLE_QUOTE_ID_MASK, 'entity_id');
    }

    /**
     * Determine whether or not a masked ID is for a valid and active quote
     *
     * @param string $mask
     * @return bool
     */
    public function isQuoteActive(string $mask): bool
    {
        $db = $this->getConnection();

        $select = $db->select()
            ->from(static::TABLE_QUOTE_ID_MASK, [])
            ->joinLeft(
                static::TABLE_QUOTE,
                self::TABLE_QUOTE . '.' . static::QUOTE_ATTR_ENTITY_ID . '=' .
                self::TABLE_QUOTE_ID_MASK . '.' . static::QUOTE_MASK_ATTR_ENTITY_ID,
                [static::QUOTE_ATTR_IS_ACTIVE]
            )
            ->where(static::QUOTE_MASK_ATTR_MASKED_ID . '=?', $mask);

        return (int)$db->fetchOne($select) !== 0;
    }
}
