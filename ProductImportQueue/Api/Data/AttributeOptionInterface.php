<?php
namespace Webkul\ProductImportQueue\Api\Data;

interface AttributeOptionInterface extends \Magento\Eav\Api\Data\AttributeOptionInterface
{
    /**
     * Constants used as data array keys
     */
    const IMAGE_URL = 'image_url';
    const COLOR_CODE = 'color_code';

    /**
     * Get Image Url
     *
     * @return string|null
     */
    public function getImageUrl();

    /**
     * Set Image Url
     *
     * @param $imageUrl
     * @return $this
     */
    public function setImageUrl($imageUrl = '');

    /**
     * Get Color Code
     *
     * @return string|null
     */
    public function getColorCode();

    /**
     * Set Color Code
     *
     * @param $colorCode
     * @return $this
     */
    public function setColorCode($colorCode = '');
}
