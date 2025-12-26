<?php

/* --------------------------------------------------------------
   StaticSeoUrlController.inc.php 2018-10-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

MainFactory::load_class('AdminHttpViewController');

/**
 * Class StaticSeoUrlController
 *
 * Bootstraps the staticSeoUrl overview page.
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class StaticSeoUrlController extends AdminHttpViewController
{
    /**
     * @var StaticSeoUrlReadService
     */
    protected $staticSeoUrlReadService;
    
    /**
     * @var StaticSeoUrlWriteService
     */
    protected $staticSeoUrlWriteService;
    
    /**
     * @var LanguageProvider
     */
    protected $languageProvider;
    
    private $staticSeoUrl;
    
    
    /**
     * Initialize Controller
     */
    public function init()
    {
        $this->staticSeoUrlReadService  = StaticGXCoreLoader::getService('StaticSeoUrlRead');
        $this->staticSeoUrlWriteService = StaticGXCoreLoader::getService('StaticSeoUrlWrite');
        $this->languageProvider         = MainFactory::create('LanguageProvider',
                                                              StaticGXCoreLoader::getDatabaseQueryBuilder());
    }
    
    
    /**
     * Renders the main static seoUrl overview seoUrl.
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function actionDefault()
    {
        $languageTextManager = MainFactory::create('LanguageTextManager', 'static_seo_urls', $_SESSION['languages_id']);
        $title               = new NonEmptyStringType($languageTextManager->get_text('PAGE_TITLE'));
        $template            = new ExistingFile(new NonEmptyStringType(DIR_FS_ADMIN
                                                                       . '/html/content/static_seo_urls/index.html'));
        
        $contentNavigation = MainFactory::create('ContentNavigationCollection', []);
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_GM_META', 'admin_menu')),
                                new StringType('gm_meta.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_ROBOTS', 'admin_menu')),
                                new StringType('robots_download.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_GM_SITEMAP', 'admin_menu')),
                                new StringType('gm_sitemap.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($languageTextManager->get_text('PAGE_TITLE', 'static_seo_urls')),
                                new StringType('admin.php?do=StaticSeoUrl'),
                                new BoolType(true));
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_GM_ANALYTICS', 'admin_menu')),
                                new StringType('admin.php?do=TrackingCodes'),
                                new BoolType(false));
        
        $staticSeoUrls = [];
        /** @var StaticSeoUrlInterface $staticSeoUrl */
        foreach ($this->staticSeoUrlReadService->getAllStaticSeoUrls() as $staticSeoUrl) {
            $staticSeoUrls[] = [
                'static_seo_url_id'     => $staticSeoUrl->getId(),
                'name'                  => $staticSeoUrl->getName(),
                'sitemap_entry'         => $staticSeoUrl->isInSitemapEntry(),
                'robots_disallow_entry' => $staticSeoUrl->isInRobotsFile()
            ];
        }
        $data   = MainFactory::create('KeyValueCollection', ['staticSeoUrls' => $staticSeoUrls]);
        $assets = MainFactory::create('AssetCollection',
                                      [MainFactory::create('Asset', 'static_seo_urls.lang.inc.php')]);
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $template,
                                   $data,
                                   $assets,
                                   $contentNavigation);
    }
    
    
    /**
     * Renders the static seo url details form page.
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function actionDetails()
    {
        
        $languageTextManager = MainFactory::create('LanguageTextManager', 'static_seo_urls', $_SESSION['languages_id']);
        $title               = new NonEmptyStringType($languageTextManager->get_text('PAGE_TITLE'));
        $template            = new ExistingFile(new NonEmptyStringType(DIR_FS_ADMIN
                                                                       . '/html/content/static_seo_urls/details.html'));
        
        $contentNavigation = MainFactory::create('ContentNavigationCollection', []);
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_GM_SEO_BOOST', 'admin_menu')),
                                new StringType('gm_seo_boost.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_GM_META', 'admin_menu')),
                                new StringType('gm_meta.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_ROBOTS', 'admin_menu')),
                                new StringType('robots_download.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_GM_SITEMAP', 'admin_menu')),
                                new StringType('gm_sitemap.php'),
                                new BoolType(false));
        $contentNavigation->add(new StringType($languageTextManager->get_text('PAGE_TITLE', 'static_seo_urls')),
                                new StringType('admin.php?do=StaticSeoUrl'),
                                new BoolType(true));
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_GM_ANALYTICS', 'admin_menu')),
                                new StringType('admin.php?do=TrackingCodes'),
                                new BoolType(false));
        
        $changeFrequencyOptions = [
            'always'  => $languageTextManager->get_text('CHANGEFREQ_ALWAYS'),
            'hourly'  => $languageTextManager->get_text('CHANGEFREQ_HOURLY'),
            'daily'   => $languageTextManager->get_text('CHANGEFREQ_DAILY'),
            'weekly'  => $languageTextManager->get_text('CHANGEFREQ_WEEKLY'),
            'monthly' => $languageTextManager->get_text('CHANGEFREQ_MONTHLY'),
            'yearly'  => $languageTextManager->get_text('CHANGEFREQ_YEARLY'),
            'never'   => $languageTextManager->get_text('CHANGEFREQ_NEVER'),
        ];
        $priorityOptions        = [
            '0.0' => '0.0',
            '0.1' => '0.1',
            '0.2' => '0.2',
            '0.3' => '0.3',
            '0.4' => '0.4',
            '0.5' => '0.5',
            '0.6' => '0.6',
            '0.7' => '0.7',
            '0.8' => '0.8',
            '0.9' => '0.9',
            '1.0' => '1.0',
        ];
        
        $staticSeoUrlId     = (int)$this->_getQueryParameter('id');
        $this->staticSeoUrl = $this->_getStaticSeoUrl($staticSeoUrlId);
        
        $fileManagerStorage = MainFactory::create('ResponsiveFileManagerConfigurationStorage');
        $useFileManager     = $fileManagerStorage->isInstalled()
                              && $fileManagerStorage->get('use_in_product_and_category_pages');
        
        /** @var StaticSeoUrlInterface $this ->staticSeoUrl */
        $data = MainFactory::create('KeyValueCollection',
                                    [
                                        'id'                       => $this->staticSeoUrl->getId(),
                                        'name'                     => $this->staticSeoUrl->getName(),
                                        'sitemapEntry'             => $this->staticSeoUrl->isInSitemapEntry(),
                                        'changefreq'               => $this->staticSeoUrl->getChangeFrequency(),
                                        'priority'                 => $this->staticSeoUrl->getPriority(),
                                        'robots_disallow_entry'    => $this->staticSeoUrl->isInRobotsFile(),
                                        'ogimage'                  => $this->staticSeoUrl->getOpenGraphImage()
                                            ->asString(),
                                        'ogimage_path'             => HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES,
                                        'staticSeoUrlContents'     => $this->_getStaticSeoUrlStaticSeoUrlContentsData($this->staticSeoUrl),
                                        'emptyStaticSeoUrlContent' => $this->_getStaticSeoUrlContentTemplateData(),
                                        'changefreqOptions'        => $changeFrequencyOptions,
                                        'priorityOptions'          => $priorityOptions,
                                        'use_filemanager'          => $useFileManager,
                                    ]);
        
        $assets = MainFactory::create('AssetCollection',
                                      [MainFactory::create('Asset', 'static_seo_urls.lang.inc.php')]);
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $template,
                                   $data,
                                   $assets,
                                   $contentNavigation);
    }
    
    
    /**
     * Performs save action
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function actionSave()
    {
        $data = $this->_getPostDataCollection()->getArray();
        
        $this->staticSeoUrl = $this->_getStaticSeoUrl((int)$data['id']);
        $this->staticSeoUrl->setName(new StringType(stripslashes($data['name'])))
            ->setIsInSitemapEntry(new BoolType(!empty($data['sitemapEntry'])
                                               && $data['sitemapEntry'] !== 'false'))
            ->setChangeFrequency(new StringType(stripslashes($data['changefreq'])))
            ->setPriority(new StringType(stripslashes($data['priority'])))
            ->setIsInRobotsFile(new BoolType(!empty($data['robotsEntry'])
                                             && $data['robotsEntry'] !== 'false'));
        
        if (isset($_FILES['opengraph_image'])) {
            $uploadMaxFilesize = $this->getUploadMaxFilesize();
            $imageFilesize     = $_FILES['opengraph_image']['size'];
            $imageTmpName      = $_FILES['opengraph_image']['tmp_name'];
            $uploadSuccess     = ($imageFilesize !== 0 && filesize($imageTmpName) < $uploadMaxFilesize);
        } else {
            $uploadSuccess = false;
        }
        
        if ($this->staticSeoUrl->getOpenGraphImage()->asString() !== ''
            && (isset($data['ogImageDelete'])
                || (isset($_FILES['opengraph_image'])
                    && $uploadSuccess))) {
            /*
            $imageFileStorage = MainFactory::create('ImageFileStorage',
                                                    MainFactory::create('WritableDirectory', DIR_FS_CATALOG_IMAGES));
            $imageFileStorage->deleteFile($this->staticSeoUrl->getOpenGraphImage());
            */
            $this->staticSeoUrl->setOpenGraphImage(new FilenameStringType(''));
        }
        
        if (isset($_FILES['opengraph_image'])) // conventional upload
        {
            
        } elseif (isset($data['ogImage'])) // upload via FileManager
        {
            $imageFileName = new FilenameStringType($data['ogImage']);
            $this->staticSeoUrl->setOpenGraphImage($imageFileName);
        }
        
        $staticSeoUrlContents = [];
        
        foreach ($data['staticSeoUrlContents'] as $languageCode => $staticSeoUrlContentListData) {
            $languageId = new IdType($this->languageProvider->getIdByCode(new LanguageCode(new StringType($languageCode))));
            
            foreach ($staticSeoUrlContentListData as $staticSeoUrlContentData) {
                $staticSeoUrlContent    = $this->_createStaticSeoUrlContentObject($languageId,
                                                                                  $staticSeoUrlContentData);
                $staticSeoUrlContents[] = $staticSeoUrlContent;
            }
        }
        $this->staticSeoUrl->setStaticSeoUrlContentCollection(MainFactory::create('StaticSeoUrlContentCollection',
                                                                                  $staticSeoUrlContents));
        
        $staticSeoUrl = $this->staticSeoUrlWriteService->saveStaticSeoUrl($this->staticSeoUrl);
        
        $this->_addSuccessMessage();
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => true, 'id' => $staticSeoUrl->getId()]);
    }
    
    
    protected function getUploadMaxFilesize()
    {
        $uploadMaxFilesize = (int)ini_get('upload_max_filesize') !== 0 ? ini_get('upload_max_filesize') : '2M';
        $sizeModifier      = strtoupper(substr($uploadMaxFilesize, -1));
        $uploadMaxFilesize = (int)$uploadMaxFilesize;
        $modifiers         = ['K' => 1, 'M' => 2, 'G' => 3];
        $exponent          = 0;
        if (array_key_exists($sizeModifier, $modifiers)) {
            $exponent = $modifiers[$sizeModifier];
        }
        $uploadMaxFilesize *= (int)pow(1024, $exponent);
        
        return $uploadMaxFilesize;
    }
    
    
    /**
     * Fetches a static seo url.
     *
     * If no ID has been passed, a new static seo url will be created.
     *
     * @param int|null $id Static SeoUrl ID (optional).
     *
     * @return StaticSeoUrlInterface Fetched static seo url.
     */
    protected function _getStaticSeoUrl($id)
    {
        return $id ? $this->staticSeoUrlReadService->getStaticSeoUrlById(new IdType($id)) : MainFactory::create('StaticSeoUrl');
    }
    
    
    /**
     * Returns a static seo url content object.
     *
     * @param IdType $languageId              Language ID.
     * @param array  $staticSeoUrlContentData data.
     *
     * @return StaticSeoUrlContentInterface
     */
    protected function _createStaticSeoUrlContentObject(IdType $languageId, array $staticSeoUrlContentData)
    {
        $staticSeoUrlContent = MainFactory::create('StaticSeoUrlContent');
        
        if (array_key_exists('id', $staticSeoUrlContentData)) {
            $staticSeoUrlContent->setId(new IdType($staticSeoUrlContentData['id']));
        }
        
        $staticSeoUrlContent->setTitle(new StringType($staticSeoUrlContentData['title']))
            ->setDescription(new StringType((string)$staticSeoUrlContentData['description']))
            ->setKeywords(new StringType((string)$staticSeoUrlContentData['keywords']))
            ->setLanguageId($languageId);
        
        return $staticSeoUrlContent;
    }
    
    
    /**
     * Returns the static seoUrl content data.
     * New static seoUrl contents for each language will be created if not existing.
     *
     * @param StaticSeoUrlInterface $staticSeoUrl staticSeoUrl.
     *
     * @return array StaticSeoUrlContents data.
     */
    protected function _getStaticSeoUrlStaticSeoUrlContentsData(StaticSeoUrlInterface $staticSeoUrl)
    {
        // Get existing Contents for Static seoUrl
        $staticSeoUrlContentsData = [];
        foreach ($this->staticSeoUrl->getStaticSeoUrlContentCollection()->getArray() as $staticSeoUrlContent) {
            $staticSeoUrlContentData                                             = $this->_getStaticSeoUrlContentData($staticSeoUrlContent);
            $staticSeoUrlContentsData[$staticSeoUrlContentData['language_code']] = $staticSeoUrlContentData;
        }
        
        // Make sure a content exists for every existing language
        // Otherwise create new content
        foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
            if (!array_key_exists($languageCode->asString(), $staticSeoUrlContentsData)) {
                $languageId          = new IdType($this->languageProvider->getIdByCode($languageCode));
                $staticSeoUrlContent = MainFactory::create('StaticSeoUrlContent');
                $staticSeoUrlContent->setLanguageId($languageId);
                $staticSeoUrlContentsData[$languageCode->asString()] = $this->_getStaticSeoUrlContentData($staticSeoUrlContent);
            }
        }
        
        return $staticSeoUrlContentsData;
    }
    
    
    /**
     * Returns the staticSeoUrlContent data.
     *
     * @param StaticSeoUrlContentInterface $staticSeoUrlContent StaticSeoUrlContent.
     *
     * @return array Extracted data from staticSeoUrlContent object.
     */
    protected function _getStaticSeoUrlContentData(StaticSeoUrlContentInterface $staticSeoUrlContent)
    {
        $staticSeoUrlContentLanguageId = new IdType($staticSeoUrlContent->getLanguageId());
        
        $staticSeoUrlContentData = [
            'id'            => $staticSeoUrlContent->getId(),
            'language_code' => $this->languageProvider->getCodeById($staticSeoUrlContentLanguageId)->asString(),
            'title'         => $staticSeoUrlContent->getTitle(),
            'description'   => $staticSeoUrlContent->getDescription(),
            'keywords'      => $staticSeoUrlContent->getKeywords()
        ];
        
        return $staticSeoUrlContentData;
    }
    
    
    /**
     * Returns the content template data.
     *
     * @return array StaticSeoUrlContent template data.
     */
    protected function _getStaticSeoUrlContentTemplateData()
    {
        $languageId = new IdType((int)$_SESSION['languages_id']);
        
        $staticSeoUrlContent = MainFactory::create('StaticSeoUrlContent');
        
        $staticSeoUrlContent->setLanguageId($languageId);
        
        $staticSeoUrlContentData = $this->_getStaticSeoUrlContentData($staticSeoUrlContent);
        
        return $staticSeoUrlContentData;
    }
    
    
    /**
     * Adds a new success message.
     */
    protected function _addSuccessMessage()
    {
        $languageTextManager = MainFactory::create('LanguageTextManager', 'static_seo_urls', $_SESSION['languages_id']);
        $languageCode        = $this->languageProvider->getCodeById(new IdType((int)$_SESSION['languages_id']));
        
        $messageSource      = 'adminAction';
        $messageIdentifier  = uniqid('adminActionSuccess-', true);
        $messageStatus      = 'new';
        $messageType        = 'success';
        $messageVisibility  = 'removable';
        $messageButtonLink  = '';
        $messageText        = $languageTextManager->get_text('GM_LANGUAGE_CONFIGURATION_SUCCESS', 'languages');
        $messageHeadLine    = $languageTextManager->get_text('success', 'messages');
        $messageButtonLabel = '';
        
        $message = MainFactory::create('InfoBoxMessage');
        
        $message->setSource(new StringType($messageSource))
            ->setIdentifier(new StringType($messageIdentifier))
            ->setStatus(new StringType($messageStatus))
            ->setType(new StringType($messageType))
            ->setVisibility(new StringType($messageVisibility))
            ->setButtonLink(new StringType($messageButtonLink))
            ->setCustomerId(new IdType((int)$_SESSION['customer_id']))
            ->setMessage(new StringType($messageText), $languageCode)
            ->setHeadLine(new StringType($messageHeadLine), $languageCode)
            ->setButtonLabel(new StringType($messageButtonLabel), $languageCode);
        
        /** @var InfoBoxService $service */
        $service = StaticGXCoreLoader::getService('InfoBox');
        $service->addMessage($message);
    }
}
