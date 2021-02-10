<?php

namespace Dotdigitalgroup\Sms\Model\Config;

interface ConfigInterface
{
    /*
     * Config paths for SMS config fields
     */
    const XML_PATH_TRANSACTIONAL_SMS_ENABLED = 'transactional_sms/sms_settings/enabled';
    const XML_PATH_SMS_PHONE_NUMBER_VALIDATION = 'transactional_sms/sms_settings/phone_number_validation';
    const XML_PATH_TRANSACTIONAL_SMS_BATCH_SIZE = 'transactional_sms/sms_settings/batch_size';

    const XML_PATH_SMS_NEW_ORDER_ENABLED = 'transactional_sms/sms_templates/new_order_confirmation_enabled';
    const XML_PATH_SMS_NEW_ORDER_MESSAGE = 'transactional_sms/sms_templates/new_order_confirmation_message';

    const XML_PATH_SMS_ORDER_UPDATE_ENABLED = 'transactional_sms/sms_templates/order_update_enabled';
    const XML_PATH_SMS_ORDER_UPDATE_MESSAGE = 'transactional_sms/sms_templates/order_update_message';

    const XML_PATH_SMS_NEW_SHIPMENT_ENABLED = 'transactional_sms/sms_templates/new_shipment_enabled';
    const XML_PATH_SMS_NEW_SHIPMENT_MESSAGE = 'transactional_sms/sms_templates/new_shipment_message';

    const XML_PATH_SMS_SHIPMENT_UPDATE_ENABLED = 'transactional_sms/sms_templates/shipment_update_enabled';
    const XML_PATH_SMS_SHIPMENT_UPDATE_MESSAGE = 'transactional_sms/sms_templates/shipment_update_message';

    const XML_PATH_SMS_NEW_CREDIT_MEMO_ENABLED = 'transactional_sms/sms_templates/new_credit_memo_enabled';
    const XML_PATH_SMS_NEW_CREDIT_MEMO_MESSAGE = 'transactional_sms/sms_templates/new_credit_memo_message';

    const SMS_TYPE_NEW_ORDER = 1;
    const SMS_TYPE_UPDATE_ORDER = 2;
    const SMS_TYPE_NEW_SHIPMENT = 3;
    const SMS_TYPE_UPDATE_SHIPMENT = 4;
    const SMS_TYPE_NEW_CREDIT_MEMO = 5;

    const TRANSACTIONAL_SMS_MESSAGE_TYPES_MAP = [
        self::SMS_TYPE_NEW_ORDER => self::XML_PATH_SMS_NEW_ORDER_MESSAGE,
        self::SMS_TYPE_UPDATE_ORDER => self::XML_PATH_SMS_ORDER_UPDATE_MESSAGE,
        self::SMS_TYPE_NEW_SHIPMENT => self::XML_PATH_SMS_NEW_SHIPMENT_MESSAGE,
        self::SMS_TYPE_UPDATE_SHIPMENT => self::XML_PATH_SMS_SHIPMENT_UPDATE_MESSAGE,
        self::SMS_TYPE_NEW_CREDIT_MEMO => self::XML_PATH_SMS_NEW_CREDIT_MEMO_MESSAGE
    ];
}
