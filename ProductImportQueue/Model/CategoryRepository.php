<?php
namespace Webkul\ProductImportQueue\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Catalog\Model\Category;

class CategoryRepository implements \Webkul\ProductImportQueue\Api\CategoryRepositoryInterface
{
    /**
     * @var Category[]
     */
    protected $instances = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResource;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * List of fields that can used config values in case when value does not defined directly
     *
     * @var array
     */
    protected $useConfigFields = ['available_sort_by', 'default_sort_by', 'filter_price_range'];

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $file;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->file = $file;
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Catalog\Api\Data\CategoryInterface $category)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $existingData = $this->getExtensibleDataObjectConverter()
            ->toNestedArray($category, [], \Magento\Catalog\Api\Data\CategoryInterface::class);
        $existingData = array_diff_key($existingData, array_flip(['path', 'level', 'parent_id']));
        $existingData['store_id'] = $storeId;
        if (isset($existingData['image']) && is_string($existingData['image']) && strpos($existingData['image'], '://')) {
            $imageUrl = $existingData['image'];
            $imageFilename = basename($imageUrl);
            $image_type = substr(strrchr($imageFilename, "."), 1);
            $filename = md5($imageUrl . strtotime('now')).'.'.$image_type;
            $tmpDir = $this->getMediaDirTmpDir();
            $filepath = $tmpDir . '/'. $filename;
            $ggh=file_get_contents(trim($imageUrl));
            $fp = fopen($filepath, "w");
            fwrite($fp, $ggh);
            fclose($fp);
            $filepath = $this->getImagePath($filepath);
            $existingData['image'] = $filepath;
        }

        if ($category->getId()) {
            $metadata = $this->getMetadataPool()->getMetadata(
                CategoryInterface::class
            );

            $category = $this->get($category->getId(), $storeId);
            $existingData[$metadata->getLinkField()] = $category->getData(
                $metadata->getLinkField()
            );

            if (isset($existingData['image']) && is_array($existingData['image'])) {
                if (!empty($existingData['image']['delete'])) {
                    $existingData['image'] = null;
                } else {
                    if (isset($existingData['image'][0]['name']) && isset($existingData['image'][0]['tmp_name'])) {
                        $existingData['image'] = $existingData['image'][0]['name'];
                    } else {
                        unset($existingData['image']);
                    }
                }
            }
        } else {
            $parentId = $category->getParentId() ?: $this->storeManager->getStore()->getRootCategoryId();
            $parentCategory = $this->get($parentId, $storeId);
            $existingData['path'] = $parentCategory->getPath();
            $existingData['parent_id'] = $parentId;
        }
        $category->addData($existingData);
        try {
            $this->validateCategory($category);
            
            $this->categoryResource->save($category);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save category: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
        if (isset($filepath) && $filepath) {
            $categoryFact = $this->categoryFactory->create()->load($category->getId());
            $categoryFact->setImage($filepath)->save();
        }
        unset($this->instances[$category->getId()]);
        return $this->get($category->getId(), $storeId);
    }

    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    protected function getMediaDirTmpDir()
    {
        $directoryPath = $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'catalog/category';
        
        mkdir($directoryPath);

        return $directoryPath;
    }

    /**
     * Lazy loader for the converter.
     *
     * @return \Magento\Framework\Api\ExtensibleDataObjectConverter
     *
     * @deprecated 101.0.0
     */
    private function getExtensibleDataObjectConverter()
    {
        if ($this->extensibleDataObjectConverter === null) {
            $this->extensibleDataObjectConverter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\ExtensibleDataObjectConverter::class);
        }
        return $this->extensibleDataObjectConverter;
    }

    /**
     * Lazy loader for the metadata pool.
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * Validate category process
     *
     * @param  Category $category
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateCategory(Category $category)
    {
        $useConfigFields = [];
        foreach ($this->useConfigFields as $field) {
            if (!$category->getData($field)) {
                $useConfigFields[] = $field;
            }
        }
        $category->setData('use_post_data_config', $useConfigFields);
        $validate = $category->validate();
        if ($validate !== true) {
            foreach ($validate as $code => $error) {
                if ($error === true) {
                    $attribute = $this->categoryResource->getAttribute($code)->getFrontend()->getLabel();
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The "%1" attribute is required. Enter and try again.', $attribute)
                    );
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__($error));
                }
            }
        }
        $category->unsetData('use_post_data_config');
    }

    /**
     * @inheritdoc
     */
    public function get($categoryId, $storeId = null)
    {
        $cacheKey = $storeId ?? 'all';
        if (!isset($this->instances[$categoryId][$cacheKey])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();
            if (null !== $storeId) {
                $category->setStoreId($storeId);
            }
            $category->load($categoryId);
            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('id', $categoryId);
            }
            $this->instances[$categoryId][$cacheKey] = $category;
        }
        return $this->instances[$categoryId][$cacheKey];
    }

    /**
     * Get Image Path
     *
     * @param string $absolutePath
     * @return string
     */
    public function getImagePath($absolutePath)
    {
        $baseUrl = trim($this->storeManager->getStore()->getBaseUrl(), '/');
        $rootPath = trim($this->directoryList->getRoot(), '/');
        $baseUrlArr = array_reverse(explode('/', $baseUrl));
        $rootPathArr = array_reverse(explode('/', $rootPath));
        $resultArr = [];
        for ($i=0; $i<count($baseUrlArr); $i++) {
            if($rootPathArr[$i] == $baseUrlArr[$i]) {
                $resultArr[]= $rootPathArr[$i];
            }
        }
        $resultStr = '/';
        if (!empty($resultArr)) {
            $resultArr = array_reverse($resultArr);
            $resultStr = '/'.implode('/',$resultArr).'/';
        }
        return $resultStr.ltrim($absolutePath, $this->directoryList->getRoot());
    }
}
