<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data;

use Exception;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Psr\Log\LoggerInterface;
use Vertex\Data\FlexibleCodeFieldInterface;
use Vertex\Data\FlexibleCodeFieldInterfaceFactory;
use Vertex\Data\FlexibleDateFieldInterface;
use Vertex\Data\FlexibleDateFieldInterfaceFactory;
use Vertex\Data\FlexibleFieldInterface;
use Vertex\Data\FlexibleNumericFieldInterface;
use Vertex\Data\FlexibleNumericFieldInterfaceFactory;
use Vertex\Exception\ConfigurationException;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\FlexibleCodeFieldMapperInterface;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\FlexField\Processor\FlexFieldAttributeProcessor;
use Vertex\Tax\Model\FlexField\Processor\InvoiceFlexFieldProcessorInterface;
use Vertex\Tax\Model\FlexField\Processor\TaxCalculationFlexFieldProcessorInterface;

/**
 * Builds the Flexible Fields for each line item to Vertex SDK
 */
class FlexFieldBuilder
{
    /** @var Config */
    private $config;

    /** @var FlexibleDateFieldInterfaceFactory */
    private $dateFieldFactory;

    /** @var ExceptionLogger */
    private $exceptionLogger;

    /** @var FlexFieldAttributeProcessor */
    private $flexFieldAttributeProcessor;

    /** @var FlexibleCodeFieldInterfaceFactory */
    private $flexibleCodeFieldFactory;

    /** @var FlexibleNumericFieldInterfaceFactory */
    private $flexibleNumericFieldFactory;

    /** @var LoggerInterface */
    private $logger;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /**
     * @param Config $config
     * @param FlexFieldAttributeProcessor $flexFieldAttributeProcessor
     * @param FlexibleCodeFieldInterfaceFactory $flexibleCodeFieldFactory
     * @param FlexibleDateFieldInterfaceFactory $dateFieldFactory
     * @param FlexibleNumericFieldInterfaceFactory $flexibleNumericFieldFactory
     * @param ExceptionLogger $exceptionLogger
     * @param LoggerInterface $logger
     * @param MapperFactoryProxy $mapperFactory
     */
    public function __construct(
        Config $config,
        FlexFieldAttributeProcessor $flexFieldAttributeProcessor,
        FlexibleCodeFieldInterfaceFactory $flexibleCodeFieldFactory,
        FlexibleDateFieldInterfaceFactory $dateFieldFactory,
        FlexibleNumericFieldInterfaceFactory $flexibleNumericFieldFactory,
        ExceptionLogger $exceptionLogger,
        LoggerInterface $logger,
        MapperFactoryProxy $mapperFactory
    ) {
        $this->config = $config;
        $this->flexFieldAttributeProcessor = $flexFieldAttributeProcessor;
        $this->flexibleCodeFieldFactory = $flexibleCodeFieldFactory;
        $this->dateFieldFactory = $dateFieldFactory;
        $this->flexibleNumericFieldFactory = $flexibleNumericFieldFactory;
        $this->exceptionLogger = $exceptionLogger;
        $this->logger = $logger;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * Retrieve Flexible Fields From Quote
     *
     * @param CreditmemoItemInterface $item
     * @param string|null $storeId
     * @return FlexibleFieldInterface[]
     */
    public function buildAllFromCreditMemoItem(CreditmemoItemInterface $item, $storeId = null)
    {
        $fields = $this->config->getFlexFieldsList($storeId);
        $flexFields = [];
        foreach ($fields as $field) {
            $attributeCode = $field['field_source'];
            $fieldId = $field['field_id'];

            try {
                $processor = $this->flexFieldAttributeProcessor->getProcessorByAttributeCode($attributeCode);
                $attribute = $this->flexFieldAttributeProcessor->getAttributeByCode($attributeCode);
            } catch (NotFoundException $e) {
                $this->exceptionLogger->error($e);
                continue;
            }
            if (!$processor instanceof InvoiceFlexFieldProcessorInterface) {
                $this->logger->warning(
                    sprintf(
                        'Flexible Field attribute %s resolved to processor %s - Expected instance of '
                        . 'InvoiceFlexFieldProcessorInterface.  Skipping attribute.',
                        $attributeCode,
                        get_class($processor)
                    )
                );
                continue;
            }
            $value = $processor->getValueFromCreditmemo($item, $attributeCode, $attribute->getType(), $fieldId);
            if ($value === null) {
                continue;
            }
            try {
                $flexField = $this->createFlexibleField($attribute->getType(), $fieldId, $value, $storeId);
                $flexFields[] = $flexField;
            } catch (Exception $exception) {
                $this->exceptionLogger->error($exception);
            }
        }

        return $flexFields;
    }

    /**
     * Retrieve Flexible Fields From Quote
     *
     * @param InvoiceItemInterface $item
     * @param string|null $storeId
     * @return FlexibleFieldInterface[]
     */
    public function buildAllFromInvoiceItem(InvoiceItemInterface $item, $storeId = null)
    {
        $fields = $this->config->getFlexFieldsList($storeId);
        $flexFields = [];
        foreach ($fields as $field) {
            $attributeCode = $field['field_source'];
            $fieldId = $field['field_id'];

            try {
                $processor = $this->flexFieldAttributeProcessor->getProcessorByAttributeCode($attributeCode);
                $attribute = $this->flexFieldAttributeProcessor->getAttributeByCode($attributeCode);
            } catch (NotFoundException $e) {
                $this->exceptionLogger->error($e);
                continue;
            }
            if (!$processor instanceof InvoiceFlexFieldProcessorInterface) {
                $this->logger->warning(
                    sprintf(
                        'Flexible Field attribute %s resolved to processor %s - Expected instance of '
                        . 'InvoiceFlexFieldProcessorInterface.  Skipping attribute.',
                        $attributeCode,
                        get_class($processor)
                    )
                );
                continue;
            }
            $value = $processor->getValueFromInvoice($item, $attributeCode, $attribute->getType(), $fieldId);
            if ($value === null) {
                continue;
            }
            try {
                $flexField = $this->createFlexibleField($attribute->getType(), $fieldId, $value, $storeId);
                $flexFields[] = $flexField;
            } catch (Exception $exception) {
                $this->exceptionLogger->error($exception);
            }
        }

        return $flexFields;
    }

    /**
     * Retrieve Flexible Fields From Quote
     *
     * @param QuoteDetailsItemInterface $item
     * @return FlexibleFieldInterface[]
     */
    public function buildAllFromQuoteDetailsItem(QuoteDetailsItemInterface $item)
    {
        $fields = $this->config->getFlexFieldsList($item->getExtensionAttributes()->getStoreId());
        $flexFields = [];

        foreach ($fields as $field) {
            $attributeCode = $field['field_source'];
            $fieldId = $field['field_id'];
            try {
                $processor = $this->flexFieldAttributeProcessor->getProcessorByAttributeCode($attributeCode);
                $attribute = $this->flexFieldAttributeProcessor->getAttributeByCode($attributeCode);
            } catch (NotFoundException $exception) {
                $this->exceptionLogger->error($exception);
                continue;
            }
            if (!$processor instanceof TaxCalculationFlexFieldProcessorInterface) {
                $this->logger->warning(
                    sprintf(
                        'Flexible Field attribute %s resolved to processor %s - Expected instance of '
                        . 'TaxCalculationFlexFieldProcessorInterface.  Skipping attribute.',
                        $attributeCode,
                        get_class($processor)
                    )
                );
                continue;
            }
            $value = $processor->getValueFromQuote($item, $attributeCode, $attribute->getType(), $fieldId);
            if ($value === null) {
                continue;
            }
            try {
                $flexField = $this->createFlexibleField(
                    $attribute->getType(),
                    $fieldId,
                    $value,
                    $item->getExtensionAttributes() ? $item->getExtensionAttributes()->getStoreId() : null
                );
                $flexFields[] = $flexField;
            } catch (Exception $exception) {
                $this->exceptionLogger->error($exception);
            }
        }

        return $flexFields;
    }

    /**
     * Retrieve FlexibleFields from Order Item
     *
     * @param OrderItemInterface $item
     * @param string|null $storeId
     * @return FlexibleFieldInterface[]
     */
    public function buildAllFromOrderItem(OrderItemInterface $item, $storeId = null)
    {
        $fields = $this->config->getFlexFieldsList($storeId);
        $flexFields = [];

        foreach ($fields as $field) {
            $attributeCode = $field['field_source'];
            $fieldId = $field['field_id'];
            try {
                $processor = $this->flexFieldAttributeProcessor->getProcessorByAttributeCode($attributeCode);
                $attribute = $this->flexFieldAttributeProcessor->getAttributeByCode($attributeCode);
            } catch (NotFoundException $exception) {
                $this->exceptionLogger->error($exception);
                continue;
            }
            if (!$processor instanceof InvoiceFlexFieldProcessorInterface) {
                $this->logger->warning(
                    sprintf(
                        'Flexible Field attribute %s resolved to processor %s - Expected instance of '
                        . 'InvoiceFlexFieldProcessorInterface.  Skipping attribute.',
                        $attributeCode,
                        get_class($processor)
                    )
                );
                continue;
            }
            $value = $processor->getValueFromOrder($item, $attributeCode, $attribute->getType(), $fieldId);
            if ($value === null) {
                continue;
            }
            try {
                $flexField = $this->createFlexibleField($attribute->getType(), $fieldId, $value, $storeId);
                $flexFields[] = $flexField;
            } catch (Exception $exception) {
                $this->exceptionLogger->error($exception);
            }
        }

        return $flexFields;
    }

    /**
     * Create a flexible field object
     *
     * Returns one of {@see FlexibleCodeFieldInterface}, {@see FlexibleDateFieldInterface}, or
     * {@see FlexibleNumericFieldInterface}
     *
     * @param string $type Type of flexible field.  One of {@see FlexFieldFactory::TYPE_CODE},
     *        {@see FlexFieldFactory::TYPE_NUMERIC}, {@see FlexFieldFactory::TYPE_DATE}
     * @param string $fieldId Numeric field ID
     * @param string|int $value Value of flexible field
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return FlexibleFieldInterface
     * @throws ConfigurationException
     * @throws ValidationException
     */
    private function createFlexibleField(
        $type,
        $fieldId,
        $value,
        $scopeCode = null,
        $scopeType = ScopeInterface::SCOPE_STORE
    ) {
        switch ($type) {
            case 'code':
                /** @var FlexibleCodeFieldMapperInterface $codeMapper */
                $codeMapper = $this->mapperFactory->getForClass(
                    FlexibleCodeFieldInterface::class,
                    $scopeCode,
                    $scopeType
                );
                $flexField = $this->flexibleCodeFieldFactory->create();
                $value = substr($value, 0, $codeMapper->getValueMaximumLength());
                $codeMapper->validateValue($value);
                break;
            case 'numeric':
                $flexField = $this->flexibleNumericFieldFactory->create();
                break;
            case 'date':
                $flexField = $this->dateFieldFactory->create();
                break;
        }

        /** @var FlexibleFieldInterface $flexField */
        $flexField->setFieldId($fieldId);
        $flexField->setFieldValue($value);

        return $flexField;
    }
}
