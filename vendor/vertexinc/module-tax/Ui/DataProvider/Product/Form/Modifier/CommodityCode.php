<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\CommodityCodeProduct as DataModel;
use Vertex\Tax\Model\Repository\CommodityCodeProductRepository;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Config\Source\CommodityTypes;

/**
 * Adds "Vertex Commodity Code" input to Product Form
 */
class CommodityCode extends AbstractModifier
{
    const FIELDSET = 'vertex_commodity_code';

    /** @var Config */
    private $config;

    /** @var CommodityCodeProductRepository */
    private $repository;

    /** @var ExceptionLogger */
    private $logger;

    /** @var LocatorInterface */
    protected $locator;

    /** @var CommodityTypes */
    private $commodityTypes;

    /**
     * @param Config $config
     * @param CommodityCodeProductRepository $repository
     * @param ExceptionLogger $logger
     * @param LocatorInterface $locator
     * @param CommodityTypes $commodityTypes
     */
    public function __construct(
        Config $config,
        CommodityCodeProductRepository $repository,
        ExceptionLogger $logger,
        LocatorInterface $locator,
        CommodityTypes $commodityTypes
    ) {
        $this->config = $config;
        $this->repository = $repository;
        $this->logger = $logger;
        $this->locator = $locator;
        $this->commodityTypes = $commodityTypes;
    }

    /**
     * Fields data
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function modifyData(array $data): array
    {
        if (!$this->config->isVertexActive()) {
            return $data;
        }

        $productLoadId = $this->getProductLoadId();
        $productId = $this->locator->getProduct()->getId();

        try {
            $commodityCode = $this->repository->getByProductId($productLoadId);
        } catch (NoSuchEntityException $exception) {
            $commodityCode = null;
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $commodityCode = null;
        }

        if ($commodityCode !== null) {
            $data[$productId]['product'][static::FIELDSET][DataModel::FIELD_CODE] = $commodityCode->getCode();
            $data[$productId]['product'][static::FIELDSET][DataModel::FIELD_TYPE] = $commodityCode->getType();
        }

        return $data;
    }

    /**
     * Return product Id
     *
     * @return string
     * @throws \Exception
     */
    protected function getProductLoadId()
    {
        return $this->locator->getProduct()->getId();
    }

    /**
     * Fields meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        if (!$this->config->isVertexActive()) {
            return $meta;
        }

        $meta[static::FIELDSET] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Commodity Code'),
                        'collapsible' => true,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => 'data.product',
                        'sortOrder' => 90
                    ],
                ],
            ],
            'children' =>
                [
                    DataModel::FIELD_CODE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label'         => __('Code'),
                                    'componentType' => Field::NAME,
                                    'formElement'   => Input::NAME,
                                    'dataType'      => Text::NAME,
                                    'sortOrder'     => 10,
                                    'dataScope'     => static::FIELDSET . '.' . DataModel::FIELD_CODE
                                ],
                            ],
                        ],
                    ],
                    DataModel::FIELD_TYPE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label'         => __('Type'),
                                    'componentType' => Field::NAME,
                                    'formElement'   => Select::NAME,
                                    'dataType'      => Text::NAME,
                                    'sortOrder'     => 20,
                                    'options'       => $this->commodityTypes->toOptionArray(),
                                    'dataScope'     => static::FIELDSET . '.' . DataModel::FIELD_TYPE
                                ],
                            ],
                        ],
                    ]
                ]
            ];

        return $meta;
    }
}
