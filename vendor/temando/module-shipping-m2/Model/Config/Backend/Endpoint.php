<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Temando\Shipping\Model\Config\Backend\Active\CredentialsValidator;

/**
 * Validate Temando API endpoint.
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Endpoint extends Value
{
    /**
     * @var CredentialsValidator
     */
    private $validationRules;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param CredentialsValidator $validationRules
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CredentialsValidator $validationRules,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->validationRules = $validationRules;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Obtain validation rules for establishing the api connection
     *
     * @return \Zend\Validator\ValidatorInterface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        $uriValidator = $this->validationRules->getUriEndpointValidator();

        $validatorChain = new \Zend\Validator\ValidatorChain();
        $validatorChain->attach($uriValidator, true);

        return $validatorChain;
    }
}
