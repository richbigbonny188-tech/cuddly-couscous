<?php
/* --------------------------------------------------------------
   CategoryServiceFactory.inc.php 2021-03-05
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use function Gambio\Core\Logging\logger;
/**
 * Class CategoryServiceFactory
 *
 * This class provides methods for creating the objects of the public category service api with its dependencies.
 *
 * @category   System
 * @package    Category
 * @subpackage Factories
 */
class CategoryServiceFactory extends AbstractCategoryServiceFactory
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var CategoryObjectService
     */
    protected $objectService;
    
    /**
     * @var CategoryReadService
     */
    protected $readService;
    
    /**
     * @var CategoryWriteService
     */
    protected $writeService;
    
    /**
     * @var CategoryServiceSettingsInterface
     */
    protected $settings;
    
    /**
     * @var CategoryRepository
     */
    protected $categoryRepo;
    
    /**
     * @var CategoryRepositoryReader
     */
    protected $reader;
    
    /**
     * @var CategoryRepositoryWriter
     */
    protected $writer;
    
    /**
     * @var CategoryRepositoryDeleter
     */
    protected $deleter;
    
    /**
     * @var CategorySettingsRepository
     */
    protected $settingsRepo;
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    
    /**
     * @var AddonValueService
     */
    protected $addonValueService;
    
    /**
     * @var CustomerStatusProvider
     */
    protected $customerStatusProvider;
    
    /**
     * @var UrlRewriteStorage
     */
    protected $urlRewriteStorage;
    
    /**
     * @var CategoryListProviderFactory
     */
    protected $categoryListProviderFactory;
    
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;
    
    /**
     * @var LanguageProvider
     */
    protected $languageProvider;
    
    /**
     * @var CategorySettingsRepositoryReader
     */
    protected $settingsReader;
    
    /**
     * @var CategorySettingsRepositoryWriter
     */
    protected $settingsWriter;
    
    /**
     * @var AddonValueStorageFactory
     */
    protected $addonValueStorageFactory;
    
    /**
     * @var ImageFileStorage
     */
    protected $categoryImageStorage;
    
    /**
     * @var ImageFileStorage
     */
    protected $categoryIconStorage;
    
    /**
     * @var ImageFileStorage
     */
    protected $categoryOpenGraphImageStorage;
    
    /**
     * @var ProductPermissionSetter
     */
    protected $productPermissionSetter;
    
    /**
     * @var GMSEOBoost_ORIGIN
     */
    protected $urlKeywordsRepairer;
    
    /**
     * @var CacheControl
     */
    protected $cacheControl;
    
    /**
     * @var \GMSEOBoost
     */
    protected $seoBoost;
    
    
    /**
     * CategoryServiceFactory constructor.
     *
     * @param CI_DB_query_builder              $db       Database connector.
     * @param CategoryServiceSettingsInterface $settings Category service settings.
     */
    public function __construct(
        CI_DB_query_builder $db,
        CategoryServiceSettingsInterface $settings,
        GMSEOBoost $seoBoost
    ) {
        $this->db       = $db;
        $this->settings = $settings;
        $this->seoBoost = $seoBoost;
    }
    
    
    /**
     * Creates a category object service.
     *
     * @return CategoryObjectServiceInterface
     */
    public function createCategoryObjectService()
    {
        if (null === $this->objectService) {
            $this->objectService = MainFactory::create('CategoryObjectService', MainFactory::create('CategoryFactory'));
        }
        
        return $this->objectService;
    }
    
    
    /**
     * Creates a category read service.
     *
     * @return CategoryReadService
     */
    public function createCategoryReadService()
    {
        if (null === $this->readService) {
            $this->readService = MainFactory::create('CategoryReadService',
                                                     $this->_createCategoryRepo(),
                                                     $this->_createCategoryListProviderFactory(),
                                                     $this->_createUrlRewriteStorage());
        }
        
        return $this->readService;
    }
    
    
    /**
     * Creates a category write service.
     *
     * @return CategoryWriteService
     */
    public function createCategoryWriteService()
    {
        // usage of gm seo boost without members because we get a singleton
        // with MainFactory::create_object('x', 'y', true)
        if (null === $this->writeService) {
            $this->writeService = MainFactory::create('CategoryWriteService',
                                                      $this->_createCategoryRepo(),
                                                      $this->_createCategoryImageStorage(),
                                                      $this->_createCategoryIconStorage(),
                                                      $this->_createCategoryOpenGraphImageStorage(),
                                                      $this->_createProductPermissionSetter(),
                                                      $this->seoBoost,
                                                      $this->_createCacheControl());
        }
        
        return $this->writeService;
    }
    
    
    /**
     * Creates a new instance of a CategoryRepository object.
     *
     * @return \CategoryRepository
     */
    protected function _createCategoryRepo()
    {
        if (null === $this->categoryRepo) {
            $this->categoryRepo = MainFactory::create('CategoryRepository',
                                                      $this->_createReader(),
                                                      $this->_createWriter(),
                                                      $this->_createDeleter(),
                                                      $this->_createSettingsRepo(),
                                                      $this->_createAddonValueService(),
                                                      $this->_createCustomerStatusProvider(),
                                                      $this->_createUrlRewriteStorage(),
                                                      DeleteHistoryServiceFactory::writeService());
        }
        
        return $this->categoryRepo;
    }
    
    
    /**
     * Creates a new instance of a CategoryRepositoryReader object.
     * Consecutive usage provides the same object.
     *
     * @return \CategoryRepositoryReader
     */
    protected function _createReader()
    {
        if (null === $this->reader) {
            $this->reader = MainFactory::create('CategoryRepositoryReader', $this->db, $this->_createCategoryFactory());
        }
        
        return $this->reader;
    }
    
    
    /**
     * Creates a new instance of a CategoryRepositoryWriter object.
     * Consecutive usage provides the same object.
     *
     * @return \CategoryRepositoryWriter
     */
    protected function _createWriter()
    {
        if (null === $this->writer) {
            $this->writer = MainFactory::create('CategoryRepositoryWriter',
                                                $this->db,
                                                $this->_createLanguageProvider());
        }
        
        return $this->writer;
    }
    
    
    /**
     * Creates a new instance of a CategoryRepositoryDeleter object.
     * Consecutive usage provides the same object.
     *
     * @return \CategoryRepositoryDeleter
     */
    protected function _createDeleter()
    {
        if (null === $this->deleter) {
            $this->deleter = MainFactory::create('CategoryRepositoryDeleter',
                                                 $this->db,
                                                 $this->_createProductRepository());
        }
        
        return $this->deleter;
    }
    
    
    /**
     * Creates a new instance of a CategorySettingsRepository object.
     * Consecutive usage provides the same object.
     *
     * @return \CategorySettingsRepository
     */
    protected function _createSettingsRepo()
    {
        if (null === $this->settingsRepo) {
            $this->settingsRepo = MainFactory::create('CategorySettingsRepository',
                                                      $this->_createSettingsReader(),
                                                      $this->_createSettingsWriter());
        }
        
        return $this->settingsRepo;
    }
    
    
    /**
     * Creates a new instance of a CategorySettingsRepositoryReader object.
     * Consecutive usage provides the same object.
     *
     * @return \CategorySettingsRepositoryReader
     */
    protected function _createSettingsReader()
    {
        if (null === $this->settingsReader) {
            $this->settingsReader = MainFactory::create('CategorySettingsRepositoryReader',
                                                        $this->db,
                                                        $this->_createCategoryFactory(),
                                                        $this->_createCustomerStatusProvider());
        }
        
        return $this->settingsReader;
    }
    
    
    /**
     * Creates a new instance of a CategorySettingsRepositoryWriter object.
     * Consecutive usage provides the same object.
     *
     * @return \CategorySettingsRepositoryWriter
     */
    protected function _createSettingsWriter()
    {
        if (null === $this->settingsWriter) {
            $this->settingsWriter = MainFactory::create('CategorySettingsRepositoryWriter',
                                                        $this->db,
                                                        $this->_createCustomerStatusProvider());
        }
        
        return $this->settingsWriter;
    }
    
    
    /**
     * Creates a new instance of a ProductServiceFactory object.
     * Consecutive usage provides the same object.
     *
     * @return ProductServiceFactory
     */
    protected function _createProductRepository()
    {
        if (null === $this->productRepository) {
            $productServiceFactory   = MainFactory::create('ProductServiceFactory', $this->db);
            $this->productRepository = $productServiceFactory->createProductRepository();
        }
        
        return $this->productRepository;
    }
    
    
    /**
     * Creates a new instance of a AddonValueService object.
     * Consecutive usage provides the same object.
     *
     * @return AddonValueService
     */
    protected function _createAddonValueService()
    {
        if (null === $this->addonValueService) {
            $this->addonValueService = MainFactory::create('AddonValueService',
                                                           $this->_createAddonValueStorageFactory());
        }
        
        return $this->addonValueService;
    }
    
    
    /**
     * Creates a new instance of a AddonValueStorageFactory object.
     * Consecutive usage provides the same object.
     *
     * @return AddonValueStorageFactory
     */
    protected function _createAddonValueStorageFactory()
    {
        if (null === $this->addonValueStorageFactory) {
            $this->addonValueStorageFactory = MainFactory::create('AddonValueStorageFactory', $this->db);
        }
        
        return $this->addonValueStorageFactory;
    }
    
    
    /**
     * Creates a new instance of a CustomerStatusProvider object.
     * Consecutive usage provides the same object.
     *
     * @return \CustomerStatusProvider
     */
    protected function _createCustomerStatusProvider()
    {
        if (null === $this->customerStatusProvider) {
            $this->customerStatusProvider = MainFactory::create('CustomerStatusProvider', $this->db);
        }
        
        return $this->customerStatusProvider;
    }
    
    
    /**
     * Creates a new instance of a UrlRewriteStorage object.
     * Consecutive usage provides the same object.
     *
     * @return \UrlRewriteStorage
     */
    protected function _createUrlRewriteStorage()
    {
        if (null === $this->urlRewriteStorage) {
            $this->urlRewriteStorage = MainFactory::create('UrlRewriteStorage',
                                                           new NonEmptyStringType('category'),
                                                           $this->db,
                                                           $this->_createLanguageProvider());
        }
        
        return $this->urlRewriteStorage;
    }
    
    
    /**
     * Creates a new instance of a CategoryListProviderFactory object.
     * Consecutive usage provides the same object.
     *
     * @return \CategoryListProviderFactory
     */
    protected function _createCategoryListProviderFactory()
    {
        if (null === $this->categoryListProviderFactory) {
            $this->categoryListProviderFactory = MainFactory::create('CategoryListProviderFactory',
                                                                     $this->_createCategoryRepo(),
                                                                     $this->db);
        }
        
        return $this->categoryListProviderFactory;
    }
    
    
    /**
     * Creates a new instance of a CategoryFactory object.
     * Consecutive usage provides the same object.
     *
     * @return \CategoryFactory
     */
    protected function _createCategoryFactory()
    {
        if (null === $this->categoryFactory) {
            $this->categoryFactory = MainFactory::create('CategoryFactory');
        }
        
        return $this->categoryFactory;
    }
    
    
    /**
     * Creates a new instance of a LanguageProvider object.
     * Consecutive usage provides the same object.
     *
     * @return \LanguageProvider
     */
    protected function _createLanguageProvider()
    {
        if (null === $this->languageProvider) {
            $this->languageProvider = MainFactory::create('LanguageProvider', $this->db);
        }
        
        return $this->languageProvider;
    }
    
    
    /**
     * Creates a new instance of a ImageFileStorage object.
     * Consecutive usage provides the same object.
     *
     * @return \ImageFileStorage
     */
    protected function _createCategoryImageStorage()
    {
        if (null === $this->categoryImageStorage) {
            $imageDirPath               = $this->settings->getImagesDirPath() . 'categories';
            $this->categoryImageStorage = MainFactory::create('ImageFileStorage',
                                                              MainFactory::create('WritableDirectory', $imageDirPath));
        }
        
        return $this->categoryImageStorage;
    }
    
    
    /**
     * Creates a new instance of a ImageFileStorage object.
     * Consecutive usage provides the same object.
     *
     * @return \ImageFileStorage
     */
    protected function _createCategoryIconStorage()
    {
        if (null === $this->categoryIconStorage) {
            $imageDirPath = $this->settings->getImagesDirPath() . 'categories';
            $iconDirPath  = $imageDirPath . DIRECTORY_SEPARATOR . 'icons';
            
            $this->categoryIconStorage = MainFactory::create('ImageFileStorage',
                                                             MainFactory::create('WritableDirectory', $iconDirPath));
        }
        
        return $this->categoryIconStorage;
    }
    
    
    /**
     * Creates a new instance of a ImageFileStorage object.
     * Consecutive usage provides the same object.
     *
     * @return \ImageFileStorage
     */
    protected function _createCategoryOpenGraphImageStorage()
    {
        if (null === $this->categoryOpenGraphImageStorage) {

            $imageDirPath = $this->settings->getImagesDirPath() . 'categories';
            $iconDirPath  = $imageDirPath . DIRECTORY_SEPARATOR . 'og';

            if(!file_exists($iconDirPath))
            {
                if(@mkdir($iconDirPath,0777,true) === false)
                {
                    logger('admin')->warning("Directory ".$iconDirPath." does not exists and could not be created automatically");
                }
            }

            try {
                $this->categoryOpenGraphImageStorage = MainFactory::create('ImageFileStorage',
                                                                           MainFactory::create('WritableDirectory',
                                                                                               $iconDirPath));
            }
            catch(InvalidArgumentException $e) {
                logger('admin')->warning($e->getMessage());
            }

        }
        
        return $this->categoryOpenGraphImageStorage;
    }
    
    
    /**
     * Creates a new instance of a ProductPermissionSetter object.
     * Consecutive usage provides the same object.
     *
     * @return \ProductPermissionSetter
     */
    protected function _createProductPermissionSetter()
    {
        if (null === $this->productPermissionSetter) {
            $this->productPermissionSetter = MainFactory::create('ProductPermissionSetter', $this->db);
        }
        
        return $this->productPermissionSetter;
    }
    
    
    /**
     * Creates a new instance of a CacheControl object.
     * Consecutive usage provides the same object.
     *
     * @return \CacheControl
     */
    protected function _createCacheControl()
    {
        if (null === $this->cacheControl) {
            $this->cacheControl = MainFactory::create('CacheControl');
        }
        
        return $this->cacheControl;
    }
}
