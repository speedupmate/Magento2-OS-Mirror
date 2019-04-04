<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Config\Backend\Active;

use Zend\Validator\Callback as CallbackValidator;
use Zend\Validator\Uri as UriValidator;

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
     * @return CallbackValidator
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

        $message = __('Please set API credentials before enabling Magento Shipping.');
        $validator = new CallbackValidator($callback);
        $validator->setMessage($message, CallbackValidator::INVALID_VALUE);

        return $validator;
    }

    /**
     * Check if API endpoint URI scheme is either "http" or "https" if given.
     *
     * @return CallbackValidator
     */
    public function getUriEndpointValidator()
    {
        $callback = function (\Magento\Framework\App\Config\Value $field, UriValidator $uriValidator) {
            // read session endpoint from current save operation
            $sessionUrl = $field->getFieldsetDataValue('session_endpoint');

            return (empty($sessionUrl) || $uriValidator->isValid($sessionUrl));
        };

        $uriValidator = new UriValidator(['uriHandler' => \Zend\Uri\Http::class]);
        $message = __('Please enter a valid URL. Protocol (http://, https://) is required.');

        $validator = new CallbackValidator($callback);
        $validator->setCallbackOptions([$uriValidator]);
        $validator->setMessage($message, CallbackValidator::INVALID_VALUE);

        return $validator;
    }

    /**
     * Check if credentials are valid.
     *
     * @return CallbackValidator
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

        $message = __('Magento Shipping authentication failed. Please check your credentials.');
        $validator = new CallbackValidator($callback);
        $validator->setMessage($message, CallbackValidator::INVALID_VALUE);

        return $validator;
    }
}
