<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Vertex\Data\LineItemInterface;
use Vertex\Tax\Model\ResourceModel\InvoiceTextCode as InvoiceTextCodeResource;
use Vertex\Tax\Model\ResourceModel\TaxCode as TaxCodeResource;
use Vertex\Tax\Model\ResourceModel\VertexTaxCode as VertexTaxCodeResource;

/**
 * Attribute Manager class for helping to save and retrieve Vertex attributes
 */
class VertexTaxAttributeManager
{
    /** @var InvoiceTextCodeResource */
    private $invoiceTextCodeResource;

    /** @var TaxCodeResource */
    private $taxCodeResource;

    /** @var VertexTaxCodeResource */
    private $vertexTaxCodeResource;

    /**
     * @param InvoiceTextCodeResource $invoiceTextCodeResource
     * @param TaxCodeResource $taxCodeResource
     * @param VertexTaxCodeResource $vertexTaxCodeResource
     */
    public function __construct(
        InvoiceTextCodeResource $invoiceTextCodeResource,
        TaxCodeResource $taxCodeResource,
        VertexTaxCodeResource $vertexTaxCodeResource
    ) {
        $this->invoiceTextCodeResource = $invoiceTextCodeResource;
        $this->taxCodeResource = $taxCodeResource;
        $this->vertexTaxCodeResource = $vertexTaxCodeResource;
    }

    /**
     * Get Invoice Text Codes array
     *
     * @param int[] $itemIdArray
     * @return string[]
     */
    public function getInvoiceTextCodes(array $itemIdArray)
    {
        return $this->invoiceTextCodeResource->getInvoiceTextCodeByItemIdArray($itemIdArray);
    }

    /**
     * Get Tax Codes array
     *
     * @param int[] $itemIdArray
     * @return string[]
     */
    public function getTaxCodes(array $itemIdArray)
    {
        return $this->taxCodeResource->getTaxCodeByItemIdArray($itemIdArray);
    }

    /**
     * Get Vertex Tax Codes array
     *
     * @param int[] $itemIdArray
     * @return string[]
     */
    public function getVertexTaxCodes(array $itemIdArray)
    {
        return $this->vertexTaxCodeResource->getVertexTaxCodeByItemIdArray($itemIdArray);
    }

    /**
     * Store all Vertex Attributes from Vertex API response
     *
     * @param LineItemInterface[] $items
     * @return void
     */
    public function saveAllVertexAttributes(array $items)
    {
        $this->setInvoiceTextCodes($items);
        $this->setTaxCodes($items);
        $this->setVertexTaxCodes($items);
    }

    /**
     * Store Invoice Text Codes
     *
     * @param LineItemInterface[] $itemsArray
     * @return void
     */
    public function setInvoiceTextCodes(array $itemsArray)
    {
        $insertData = [];
        foreach ($itemsArray as $item) {
            if ($id = $item->getLineItemId()) {
                $taxArray = $item->getTaxes();
                foreach ($taxArray as $tax) {
                    if ($invoiceTextCode = $tax->getInvoiceTextCodes()) {
                        if ($invoiceTextCode !== null) {
                            foreach ($invoiceTextCode as $taxItem) {
                                $insertData[$id][] = [
                                    InvoiceTextCodeResource::FIELD_ID => $id,
                                    InvoiceTextCodeResource::FIELD_CODE => $taxItem
                                ];
                                $insertData[$id] = array_unique($insertData[$id], SORT_REGULAR);
                            }
                        } else {
                            $insertData[$id][] = [
                                InvoiceTextCodeResource::FIELD_ID => $id,
                                InvoiceTextCodeResource::FIELD_CODE => $invoiceTextCode
                            ];
                            $insertData[$id] = array_unique($insertData[$id], SORT_REGULAR);
                        }
                    }
                }
            }
        }

        if (count($insertData)) {
            $this->invoiceTextCodeResource->saveMultiple($insertData);
        }
    }

    /**
     * Store Tax Coxdes
     *
     * @param LineItemInterface[] $items
     * @return void
     */
    public function setTaxCodes(array $itemsArray)
    {
        $insertData = [];
        foreach ($itemsArray as $item) {
            if ($id = $item->getLineItemId()) {
                $taxArray = $item->getTaxes();
                foreach ($taxArray as $tax) {
                    if (!$tax->getTaxCode()) {
                        continue;
                    }
                    $insertData[$id][] = [
                        TaxCodeResource::FIELD_ID => $id,
                        TaxCodeResource::FIELD_TAX_CODE => $tax->getTaxCode()
                    ];
                    $insertData[$id] = array_unique($insertData[$id], SORT_REGULAR);
                }
            }
        }

        if (count($insertData)) {
            $this->taxCodeResource->saveMultiple($insertData);
        }
    }

    /**
     * Store Vertex Tax Codes
     *
     * @param LineItemInterface[] $itemsArray
     * @return void
     */
    public function setVertexTaxCodes(array $itemsArray)
    {
        $insertData = [];
        foreach ($itemsArray as $item) {
            if ($id = $item->getLineItemId()) {
                $taxArray = $item->getTaxes();
                foreach ($taxArray as $tax) {
                    if (!$tax->getVertexTaxCode()) {
                        continue;
                    }
                    $insertData[$id][] = [
                        VertexTaxCodeResource::FIELD_ID => $id,
                        VertexTaxCodeResource::FIELD_VERTEX_TAX_CODE => $tax->getVertexTaxCode()
                    ];
                    $insertData[$id] = array_unique($insertData[$id], SORT_REGULAR);
                }
            }
        }

        if (count($insertData)) {
            $this->vertexTaxCodeResource->saveMultiple($insertData);
        }
    }
}
