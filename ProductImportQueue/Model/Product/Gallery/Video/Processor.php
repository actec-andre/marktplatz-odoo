<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_ProductImportQueue
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\ProductImportQueue\Model\Product\Gallery\Video;

use Magento\Framework\Exception\LocalizedException;

class Processor extends \Magento\Catalog\Model\Product\Gallery\Processor
{
    /**
     * @var \Magento\Catalog\Model\Product\Gallery\CreateHandler
     */
    protected $createHandler;

    /**
     * Processor constructor.
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\MediaStorage\Helper\File\Storage\Database       $fileStorageDb
     * @param \Magento\Catalog\Model\Product\Media\Config              $mediaConfig
     * @param \Magento\Framework\Filesystem                            $filesystem
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery     $resourceModel
     * @param \Magento\Catalog\Model\Product\Gallery\CreateHandler     $createHandler
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel,
        \Magento\Catalog\Model\Product\Gallery\CreateHandler $createHandler
    ) {
        parent::__construct($attributeRepository, $fileStorageDb, $mediaConfig, $filesystem, $resourceModel);
        $this->createHandler = $createHandler;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array                          $videoData
     * @param [type]                         $mediaAttribute
     * @param boolean                        $move
     * @param boolean                        $exclude
     */
    public function addVideo(
        \Magento\Catalog\Model\Product $product,
        array $videoData,
        $mediaAttribute = null,
        $move = false,
        $exclude = true
    ) {
        $file = $this->mediaDirectory->getRelativePath('catalog/product/'.$videoData['file']);
        $pathinfo = pathinfo($file);
        
        $fileName = \Magento\MediaStorage\Model\File\Uploader::getCorrectFileName($pathinfo['basename']);
        $dispretionPath = \Magento\MediaStorage\Model\File\Uploader::getDispretionPath($fileName);

        $fileName = $dispretionPath . '/' . $fileName;
        $fileName = $this->getNotDuplicatedFilename($fileName, $dispretionPath);
        $destinationFile = $this->mediaConfig->getTmpMediaPath($fileName);

        try {
            $storageHelper = $this->fileStorageDb;
            $this->mediaDirectory->renameFile($file, $destinationFile);
            $storageHelper->saveFile($this->mediaConfig->getTmpMediaShortUrl($fileName));
        } catch (\Exception $e) {
            throw new LocalizedException(__('We couldn\'t move this file: %1.', $e->getMessage()));
        }

        $fileName = str_replace('\\', '/', $fileName);

        $attrCode = $this->getAttribute()->getAttributeCode();
        $mediaGalleryData = $product->getData($attrCode);
        $position = 0;
        if (!is_array($mediaGalleryData))
        {
            $mediaGalleryData = ['images' => []];
        }

        foreach ($mediaGalleryData['images'] as &$image)
        {
            if (isset($image['position']) && $image['position'] > $position)
            {
                $position = $image['position'];
            }
        }
        $position++;
        
        unset($videoData['file']);
        $mediaGalleryData['images'][] = array_merge([
            'file' => $fileName,
            'label' => $videoData['video_title'],
            'position' => $position,
            'disabled' => (int)$exclude
        ], $videoData);

        $product->setData($attrCode, $mediaGalleryData);
        if (in_array('thumbnail', $mediaAttribute)) {
            $product->setThumbnail($fileName);
        }
        if (in_array('image', $mediaAttribute)) {
            $product->setImage($fileName);
        }
        if (in_array('small_image', $mediaAttribute)) {
            $product->setSmallImage($fileName);
        }
        if (in_array('swatch_image', $mediaAttribute)) {
            $product->setSwatchImage($fileName);
        }
        if ($mediaAttribute !== null) {
            $product->setMediaAttribute($product, $mediaAttribute, $fileName);
        }
        $this->createHandler->execute($product);
    }
}
