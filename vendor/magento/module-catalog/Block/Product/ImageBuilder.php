<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Helper\ImageFactory as HelperFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\NotLoadInfoImageException;

class ImageBuilder
{
    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var string
     */
    protected $imageId;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param HelperFactory $helperFactory
     * @param ImageFactory $imageFactory
     */
    public function __construct(
        HelperFactory $helperFactory,
        ImageFactory $imageFactory
    ) {
        $this->helperFactory = $helperFactory;
        $this->imageFactory = $imageFactory;
    }

    /**
     * Set product
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Set image ID
     *
     * @param string $imageId
     * @return $this
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;
        return $this;
    }

    /**
     * Set custom attributes
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @return string
     */
    protected function getCustomAttributes()
    {
        $result = [];
        foreach ($this->attributes as $name => $value) {
            $result[] = $name . '="' . $value . '"';
        }
        return !empty($result) ? implode(' ', $result) : '';
    }

    /**
     * Calculate image ratio
     *
     * @param \Magento\Catalog\Helper\Image $helper
     * @return float|int
     */
    protected function getRatio(\Magento\Catalog\Helper\Image $helper)
    {
        $width = $helper->getWidth();
        $height = $helper->getHeight();
        if ($width && $height) {
            return $height / $width;
        }
        return 1;
    }

    /**
     * Create image block
     *
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function create()
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->helperFactory->create()
            ->init($this->product, $this->imageId);

        $template = $helper->getFrame()
            ? 'Magento_Catalog::product/image.phtml'
            : 'Magento_Catalog::product/image_with_borders.phtml';

        try {
            $imagesize = $helper->getResizedImageInfo();
        } catch (NotLoadInfoImageException $exception) {
            $imagesize = [$helper->getWidth(), $helper->getHeight()];
        }

        $data = [
            'data' => [
                'template' => $template,
                'image_url' => $helper->getUrl(),
                'width' => $helper->getWidth(),
                'height' => $helper->getHeight(),
                'label' => $helper->getLabel(),
                'ratio' =>  $this->getRatio($helper),
                'custom_attributes' => $this->getCustomAttributes(),
                'resized_image_width' => $imagesize[0],
                'resized_image_height' => $imagesize[1],
            ],
        ];

        return $this->imageFactory->create($data);
    }
}
