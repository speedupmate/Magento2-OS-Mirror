<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Mapper\Api60;

use Vertex\Data\Tax;
use Vertex\Data\TaxInterface;
use Vertex\Mapper\JurisdictionMapperInterface;
use Vertex\Mapper\MapperUtilities;
use Vertex\Mapper\TaxMapperInterface;

/**
 * API Level 60 implementation of {@see TaxMapperInterface}
 */
class TaxMapper implements TaxMapperInterface
{
    /**
     * Maximum length for an imposition name
     */
    const IMPOSITION_MAX = 60;

    /**
     * Minimum length for an imposition name
     */
    const IMPOSITION_MIN = 1;

    /**
     * Maximum length for an imposition type
     */
    const IMPOSITION_TYPE_MAX = 60;

    /**
     * Minimum length for an imposition type
     */
    const IMPOSITION_TYPE_MIN = 1;

    /** @var JurisdictionMapper */
    private $jurisdictionMapper;

    /** @var MapperUtilities */
    private $utilities;

    /**
     * @param MapperUtilities|null $utilities
     * @param JurisdictionMapperInterface|null $jurisdictionMapper
     */
    public function __construct(
        MapperUtilities $utilities = null,
        JurisdictionMapperInterface $jurisdictionMapper = null
    ) {
        $this->utilities = $utilities ?: new MapperUtilities();
        $this->jurisdictionMapper = $jurisdictionMapper ?: new JurisdictionMapper();
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function build(\stdClass $map)
    {
        $object = new Tax();
        if (isset($map->taxResult)) {
            $object->setResult($map->taxResult);
        }
        if (isset($map->taxType)) {
            $object->setType($map->taxType);
        }
        if (isset($map->inputOutputType)) {
            $object->setInputOutputType($map->inputOutputType);
        }
        if (isset($map->taxCollectedFromParty)) {
            $object->setCollectedFromParty($map->taxCollectedFromParty);
        }
        if (isset($map->Jurisdiction)) {
            $object->setJurisdiction($this->jurisdictionMapper->build($map->Jurisdiction));
        }
        if (isset($map->Imposition)) {
            if ($map->Imposition instanceof \stdClass) {
                $object->setImposition($map->Imposition->_);
                if (isset($map->Imposition->impositionType)) {
                    $object->setImpositionType($map->Imposition->impositionType);
                }
            } else {
                $object->setImposition($map->Imposition);
            }
        }
        if (isset($map->CalculatedTax)) {
            $object->setAmount(
                $map->CalculatedTax instanceof \stdClass
                    ? $map->CalculatedTax->_
                    : $map->CalculatedTax
            );
        }
        if (isset($map->EffectiveRate)) {
            $object->setEffectiveRate(
                $map->EffectiveRate instanceof \stdClass
                    ? $map->EffectiveRate->_
                    : $map->EffectiveRate
            );
        }
        if (isset($map->InvoiceTextCode)) {
            $textCodes = is_array($map->InvoiceTextCode) ? $map->InvoiceTextCode : [$map->InvoiceTextCode];
            $ourTextCodes = array_map(function ($textCode) {
                return $textCode instanceof \stdClass ? $textCode->_ : $textCode;
            }, $textCodes);
            $object->setInvoiceTextCodes($ourTextCodes);
        }
        if (isset($map->vertexTaxCode)) {
            $object->setVertexTaxCode($map->vertexTaxCode);
        }
        if (isset($map->taxCode)) {
            $object->setTaxCode($map->taxCode);
        }

        return $object;
    }

    /**
     * @inheritdoc
     */
    public function map(TaxInterface $object)
    {
        $map = new \stdClass();

        $map = $this->utilities->addToMapWithEnumerationValidation(
            $map,
            $object->getResult(),
            'taxResult',
            TaxInterface::TAX_RESULTS,
            true,
            'Tax Result'
        );
        $map = $this->utilities->addToMapWithEnumerationValidation(
            $map,
            $object->getType(),
            'taxType',
            TaxInterface::TAX_TYPES,
            true,
            'Tax Type'
        );
        $map = $this->utilities->addToMapWithEnumerationValidation(
            $map,
            $object->getInputOutputType(),
            'inputOutputType',
            [
                TaxInterface::TYPE_INPUT,
                TaxInterface::TYPE_IMPORT,
                TaxInterface::TYPE_OUTPUT,
                TaxInterface::TYPE_INPUT_OUTPUT,
            ],
            true,
            'Input/Output Type'
        );
        $map = $this->utilities->addToMapWithEnumerationValidation(
            $map,
            $object->getCollectedFromParty(),
            'taxCollectedFromParty',
            [
                TaxInterface::PARTY_SELLER,
                TaxInterface::PARTY_BUYER,
            ],
            true,
            'Tax Collected From Party'
        );
        if ($object->getJurisdiction() !== null) {
            $map->Jurisdiction = $this->jurisdictionMapper->map($object->getJurisdiction());
        }
        if ($object->getImposition() !== null || $object->getImpositionType() !== null) {
            $map->Imposition = new \stdClass();
            $map->Imposition = $this->utilities->addToMapWithLengthValidation(
                $map->Imposition,
                $object->getImposition(),
                '_',
                self::IMPOSITION_MIN,
                self::IMPOSITION_MAX
            );
            $map->Imposition = $this->utilities->addToMapWithLengthValidation(
                $map->Imposition,
                $object->getImpositionType(),
                'impositionType',
                self::IMPOSITION_TYPE_MIN,
                self::IMPOSITION_TYPE_MAX
            );
        }
        $map = $this->utilities->addToMapWithDecimalValidation(
            $map,
            $object->getAmount(),
            'CalculatedTax',
            PHP_INT_MIN,
            PHP_INT_MAX,
            true,
            'Calculated Tax'
        );
        $map = $this->utilities->addToMapWithDecimalValidation(
            $map,
            $object->getEffectiveRate(),
            'EffectiveRate',
            0,
            PHP_INT_MAX,
            true,
            'Effective Rate'
        );
        $this->addInvoiceTaxCode($object, $map);

        return $map;
    }

    /**
     * Add invoice tax Codes
     *
     * @param TaxInterface $object
     * @param \stdClass $map
     * @return \stdClass
     * @throws \Vertex\Exception\ValidationException
     */
    private function addInvoiceTaxCode(TaxInterface $object, \stdClass $map)
    {
        if (!empty($object->getInvoiceTextCodes())) {
            $map->InvoiceTextCode = [];
            foreach ($object->getInvoiceTextCodes() as $invoiceTextCode) {
                $mapTaxCode = new \stdClass();
                $mapTaxCode = $this->utilities->addToMapWithEnumerationValidation(
                    $mapTaxCode,
                    $invoiceTextCode,
                    '_',
                    TaxInterface::INVOICE_TEXT_CODES,
                    true,
                    'Text Code'
                );
                $map->InvoiceTextCode[] = $mapTaxCode;
            }
        }
        if ($object->getTaxCode()) {
            $map = $this->utilities->addToMapWithLengthValidation(
                $map,
                $object->getTaxCode(),
                'taxCode',
                0,
                PHP_INT_MAX,
                true,
                'Tax Code'
            );
        }
        if ($object->getVertexTaxCode() !== null) {
            $map = $this->utilities->addToMapWithLengthValidation(
                $map,
                $object->getVertexTaxCode(),
                'vertexTaxCode',
                0,
                PHP_INT_MAX,
                true,
                'Vertex Tax Code'
            );
        }

        return $map;
    }
}
