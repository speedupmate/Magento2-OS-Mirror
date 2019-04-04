<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Config\Backend\Active;

/**
 * Validator functions for merchant account credentials.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CredentialsValidator
{
    /**
     * @var ApiConnection
     */
    private $connection;

    /**
     * CredentialsValidator constructor.
     *
     * @param ApiConnection $connection
     */
    public function __construct(ApiConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Check if credentials are available in config.
     *
     * @return \Zend_Validate_Callback
     */
    public function getInputValidator()
    {
        $callback = function (\Magento\Framework\App\Config\Value $field) {
            $enabled = $field->getValue();

            // read account id from current save operation
            $accountId = $field->getFieldsetDataValue('account_id');
            // read bearer token from current save operation
            $bearerToken = $field->getFieldsetDataValue('bearer_token');

            if (!$enabled && !$accountId && !$bearerToken) {
                // it's ok to leave credentials empty as long as shipping method is disabled.
                return true;
            }

            if ($enabled && (!$accountId || !$bearerToken)) {
                // once shipping method is enabled, credentials must be given.
                return false;
            }

            return true;
        };

        $validator = new \Zend_Validate_Callback($callback);
        $message = __('Please set API credentials before enabling Magento Shipping.');
        $validator->setMessage($message);

        return $validator;
    }

    /**
     * Check if API endpoint URI scheme is either "http" or "https" if given.
     *
     * @return \Zend_Validate_Callback
     * @throws \Zend_Validate_Exception
     */
    public function getUriEndpointValidator()
    {
        $callback = function (\Magento\Framework\App\Config\Value $field) {
            // read session endpoint from current save operation
            $sessionUrl = $field->getFieldsetDataValue('session_endpoint');

            return (empty($sessionUrl) || \Zend_Uri::check($sessionUrl));
        };

        $validator = new \Zend_Validate_Callback($callback);
        $message = __('Please enter a valid URL. Protocol (http://, https://) is required.');
        $validator->setMessage($message);

        return $validator;
    }

    /**
     * Check if credentials are valid.
     *
     * @return \Zend_Validate_Callback
     */
    public function getAuthenticationValidator()
    {
        $callback = function (\Magento\Framework\App\Config\Value $field) {
            $enabled = $field->getValue();

            // read session endpoint from current save operation
            $sessionUrl = $field->getFieldsetDataValue('session_endpoint');
            // read account id from current save operation
            $accountId = $field->getFieldsetDataValue('account_id');
            // read bearer token from current save operation
            $bearerToken = $field->getFieldsetDataValue('bearer_token');

            if (!$enabled && !$accountId && !$bearerToken) {
                // it's ok to leave credentials empty as long as shipping method is disabled.
                return true;
            }

            try {
                return $this->connection->test($sessionUrl, $accountId, $bearerToken);
            } catch (\Exception $e) {
                return false;
            }
        };

        $validator = new \Zend_Validate_Callback($callback);
        $message = __('Magento Shipping authentication failed. Please check your credentials.');
        $validator->setMessage($message);

        return $validator;
    }
}
