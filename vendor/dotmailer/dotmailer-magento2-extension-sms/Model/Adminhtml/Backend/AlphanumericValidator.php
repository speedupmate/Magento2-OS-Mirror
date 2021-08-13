<?php

namespace Dotdigitalgroup\Sms\Model\Adminhtml\Backend;

use Magento\Framework\App\Config\Value;

class AlphanumericValidator extends Value
{
    const ACCEPTANCE_REGEX = '/[^a-z0-9\._\-\s\#\!\&]/i';

    /**
     * @return AlphanumericValidator
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        if (preg_match(self::ACCEPTANCE_REGEX, $this->getValue())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Acceptable alpha numeric characters are: a-z, A-Z, 0-9, " " (space), ".", "_", "-", "!", "&", "#"')
            );
        } elseif (strlen($this->getValue()) < 3 || strlen($this->getValue()) > 11) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('From name must be at least 3 characters long and cannot be longer than 11 characters long')
            );
        } elseif (ctype_digit($this->getValue())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('From name cannot contain only digits')
            );
        }

        return parent::beforeSave();
    }
}
