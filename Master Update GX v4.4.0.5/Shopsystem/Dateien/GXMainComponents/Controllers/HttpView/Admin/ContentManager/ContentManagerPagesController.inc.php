<?php
/* --------------------------------------------------------------
 ContentManagerPagesController.inc.php 2020-03-31
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

require_once __DIR__ . '/ContentManagerContentNavigationTrait.inc.php';

/**
 * Class ContentManagerPagesController
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class ContentManagerPagesController extends AdminHttpViewController
{
    use ContentManagerContentNavigationTrait;
    
    /**
     * @var \UserConfigurationService
     */
    protected $userConfigurationService;
    
    /**
     * @var \LanguageTextManager
     */
    protected $languageTextManager;
    
    /**
     * @var \LanguageProvider
     */
    protected $languageProvider;
    
    /**
     * @var \CI_DB_query_builder
     */
    protected $queryBuilder;
    
    /**
     * @var \UrlRewriteStorage
     */
    protected $urlRewriteStorage;
    
    /**
     * @var array
     */
    protected $fieldMap = [
        'content_name',
        'content_title',
        'content_heading',
        'content_text',
        'contents_meta_title',
        'contents_meta_keywords',
        'contents_meta_description',
        'gm_url_keywords',
        'url_rewrite',
        'gm_robots_entry',
        'gm_sitemap_entry',
        'gm_priority',
        'gm_changefreq',
        'gm_link',
        'gm_link_target',
        'content_file',
        'download_file',
        'content_status',
        'content_file',
        'content_type',
        'content_version',
        'opengraph_image',
    ];
    
    /**
     * @var array
     */
    protected $switcherFields = [
        'content_status',
        'gm_robots_entry',
        'gm_sitemap_entry'
    ];
    
    /**
     * @var array
     */
    protected $typeMap = [
        'content' => 'infopage',
        'link'    => 'linkpage',
        'file'    => 'scriptpage'
    ];
    
    /**
     * @var NonEmptyStringType
     */
    protected $title;
    
    /**
     * @var SliderWriteServiceInterface
     */
    protected $sliderWriteService;
    
    /**
     * @var SliderReadServiceInterface
     */
    protected $sliderReadService;
    
    
    /**
     * Initialize Controller
     */
    public function init()
    {
        $this->userConfigurationService = StaticGXCoreLoader::getService('UserConfiguration');
        $this->languageTextManager      = MainFactory::create('LanguageTextManager',
                                                              'content_manager',
                                                              $_SESSION['languages_id']);
        $this->queryBuilder             = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $this->languageProvider         = MainFactory::create('LanguageProvider', $this->queryBuilder);
        
        $this->title = new NonEmptyStringType($this->languageTextManager->get_text('HEADING_TITLE'));
        
        $this->urlRewriteStorage = MainFactory::create('UrlRewriteStorage',
                                                       new NonEmptyStringType('content'),
                                                       $this->queryBuilder,
                                                       $this->languageProvider);
        
        $this->sliderWriteService = StaticGXCoreLoader::getService('SliderWrite');
        $this->sliderReadService  = StaticGXCoreLoader::getService('SliderRead');
    }
    
    
    /**
     * Default actions, renders the content manager elements overview.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionDefault()
    {
        $this->_setExpertMode();
        
        $contentData = $this->_getPagesData();
        
        $data = MainFactory::create('KeyValueCollection',
                                    [
                                        'mainCategoryData'        => $contentData['main'],
                                        'secondaryNavigationData' => $contentData['secondary'],
                                        'other'                   => $contentData['other'],
                                        'infoBox'                 => $contentData['infoBox'],
                                        'contentStatus'           => $contentData['contentStatus'],
                                        'additional'              => $contentData['additional'],
                                    ]);
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $this->title,
                                   $this->_getTemplate('pages', 'overview'),
                                   $data,
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'pages'));
    }
    
    
    /**
     * Renders the editing form for content manager pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionEdit()
    {
        $this->_setExpertMode();
        
        $formData = $this->_getEditFormData();
        
        $contentType  = $this->typeMap[$formData->getValue('contentType')];
        $languageCode = $this->languageProvider->getCodeById(new IdType($_SESSION['languages_id']));
        $contentData  = $formData->getValue('contentManager')[$contentType];
        
        $title = new NonEmptyStringType($this->languageTextManager->get_text('CONTENT_TITLE') . ': '
                                        . $contentData['content_title'][$languageCode->asString()]);
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $this->_getTemplate('pages', 'edit'),
                                   $formData,
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'pages'));
    }
    
    
    /**
     * Stores content manager info page data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSaveInfoPage()
    {
	    $this->_verifyPageToken();
        $this->_setExpertMode();
        
        $data = $this->_prepareData('infopage');
        
        // special case for withdrawal and withdrawal form modal (content group id 3889895)
        if ((int)$data['contentGroupId'] === 3889895) {
            $withdrawalFormFile = $this->_getPostData('content_manager')['infopage']['withdrawal_form_file'];
            
            foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
                $languageId   = $this->languageProvider->getIdByCode($languageCode);
                $languageCode = $languageCode->asString();
                
                gm_set_content('WITHDRAWAL_FORM_FILE', $withdrawalFormFile[$languageCode], $languageId);
            }
        }
        
        return $this->_insertContentData($this->queryBuilder, $data['contentData'])
            ->_storeUrlRewrites($data['urlRewrites'],
                                $data['contentGroupId'])
            ->_updateSlider($data)
            ->_repairUrlKeywords()
            ->_getUpdateResponse('ContentManagerPages',
                                 $data['contentGroupId']);
    }
    
    
    /**
     * Updates content manager info pages data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionUpdateContentPage()
    {
	    $this->_verifyPageToken();
        $this->_setExpertMode();
        
        $data = $this->_prepareData('infopage');
        
        // special case for withdrawal and withdrawal form modal (content group id 3889895)
        if ((int)$data['contentGroupId'] === 3889895) {
            $withdrawalFormFile = $this->_getPostData('content_manager')['infopage']['withdrawal_form_file'];
            
            foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
                $languageId   = $this->languageProvider->getIdByCode($languageCode);
                $languageCode = $languageCode->asString();
                
                gm_set_content('WITHDRAWAL_FORM_FILE', $withdrawalFormFile[$languageCode], $languageId);
            }
        }
        
        return $this->_updateContentData($this->queryBuilder, $data['contentData'], $data['contentGroupId'])
            ->_storeUrlRewrites($data['urlRewrites'], $data['contentGroupId'])
            ->_updateSlider($data)
            ->_repairUrlKeywords()
            ->_getUpdateResponse('ContentManagerPages', $data['contentGroupId']);
    }
    
    
    /**
     * Stores content manager link page data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSaveScriptPage()
    {
	    $this->_verifyPageToken();
        $this->_setExpertMode();
        
        $data = $this->_prepareData('scriptpage');
        
        // special case for withdrawal and withdrawal form modal (content group id 3889895)
        if ((int)$data['contentGroupId'] === 3889895) {
            $withdrawalFormFile = $this->_getPostData('content_manager')['scriptpage']['withdrawal_form_file'];
            
            foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
                $languageId   = $this->languageProvider->getIdByCode($languageCode);
                $languageCode = $languageCode->asString();
                
                gm_set_content('WITHDRAWAL_FORM_FILE', $withdrawalFormFile[$languageCode], $languageId);
            }
        }
        
        return $this->_insertContentData($this->queryBuilder, $data['contentData'])
            ->_storeUrlRewrites($data['urlRewrites'],
                                $data['contentGroupId'])
            ->_updateSlider($data)
            ->_repairUrlKeywords()
            ->_getUpdateResponse('ContentManagerPages',
                                 $data['contentGroupId']);
    }
    
    
    /**
     * Updates content manager script pages data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionUpdateFilePage()
    {
	    $this->_verifyPageToken();
        $this->_setExpertMode();
        
        $data = $this->_prepareData('scriptpage');
        
        // special case for withdrawal and withdrawal form modal (content group id 3889895)
        if ((int)$data['contentGroupId'] === 3889895) {
            $withdrawalFormFile = $this->_getPostData('content_manager')['scriptpage']['withdrawal_form_file'];
            
            foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
                $languageId   = $this->languageProvider->getIdByCode($languageCode);
                $languageCode = $languageCode->asString();
                
                gm_set_content('WITHDRAWAL_FORM_FILE', $withdrawalFormFile[$languageCode], $languageId);
            }
        }
        
        return $this->_updateContentData($this->queryBuilder, $data['contentData'], $data['contentGroupId'])
            ->_storeUrlRewrites($data['urlRewrites'], $data['contentGroupId'])
            ->_updateSlider($data)
            ->_repairUrlKeywords()
            ->_getUpdateResponse('ContentManagerPages', $data['contentGroupId']);
    }
    
    
    /**
     * Stores content manager link page data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSaveLinkPage()
    {
	    $this->_verifyPageToken();
        $this->_setExpertMode();
        
        $data = $this->_prepareLinkPagePostData();
        
        return $this->_insertContentData($this->queryBuilder, $data['contentData'])
            ->_getUpdateResponse('ContentManagerPages',
                                 $data['contentGroupId']);
    }
    
    
    /**
     * Updates content manager link pages data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionUpdateLinkPage()
    {
	    $this->_verifyPageToken();
        $this->_setExpertMode();
        
        $data = $this->_prepareLinkPagePostData();
        
        return $this->_updateContentData($this->queryBuilder, $data['contentData'], $data['contentGroupId'])
            ->_getUpdateResponse('ContentManagerPages', $data['contentGroupId']);
    }
    
    
    /**
     * Renders the creation form for content manager main pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateMain()
    {
        return $this->_getCreationResponse('main');
    }
    
    
    /**
     * Renders the creation form for content manager secondary pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateSecondary()
    {
        return $this->_getCreationResponse('secondary');
    }
    
    
    /**
     * Renders the creation form for content manager info pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateInfo()
    {
        return $this->_getCreationResponse('info');
    }
    
    
    /**
     * Renders the creation form for content manager info box pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateInfoBox()
    {
        return $this->_getCreationResponse('info_box');
    }

    /**
     * Renders the creation form for content manager info box pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateAdditional()
    {
        return $this->_getCreationResponse('additional');
    }

    
    /**
     * Prepares $_POST data for content manager link pages.
     *
     * @return array Prepared data set for updating or inserting into the database.
     */
    protected function _prepareLinkPagePostData()
    {
        $data = $this->_prepareData('linkpage');
        foreach ($data['contentData'] as $key => $dataSet) {
            $data['contentData'][$key]['gm_link_target'] = array_key_exists('gm_link_target',
                                                                            $dataSet) ? '_blank' : '_self';
        }
        
        return $data;
    }
    
    
    /**
     * Stores the given url rewrites in the database.
     *
     * @param array $urlRewrites    Content data array.
     * @param int   $contentGroupId Content group id.
     *
     * @return $this|\ContentManagerPagesController Same instance for chained method calls.
     */
    protected function _storeUrlRewrites(array $urlRewrites, $contentGroupId)
    {
        $urlRewriteContentId = new IdType($contentGroupId);
        $this->urlRewriteStorage->delete($urlRewriteContentId);
        $urlRewriteCollection = [];
        
        foreach ($urlRewrites as $languageId => $urlRewrite) {
            $languageId      = new IdType($languageId);
            $languageCode    = $this->languageProvider->getCodeById($languageId);
            $rewriteUrl      = new NonEmptyStringType($urlRewrite);
            $targetUrlString = 'shop_content.php?coID=' . $urlRewriteContentId->asInt() . '&language='
                               . strtolower($languageCode->asString());
            $targetUrl       = new NonEmptyStringType($targetUrlString);
            
            $urlRewrite = MainFactory::create('UrlRewrite',
                                              new NonEmptyStringType('content'),
                                              $urlRewriteContentId,
                                              $languageId,
                                              $rewriteUrl,
                                              $targetUrl);
            
            $urlRewriteCollection[$languageCode->asString()] = $urlRewrite;
        }
        
        $this->urlRewriteStorage->set($urlRewriteContentId,
                                      MainFactory::create('UrlRewriteCollection', $urlRewriteCollection));
        
        return $this;
    }
    
    
    /**
     * Update content slider
     *
     * @param array $data
     *
     * @return $this Same instance for chained method calls.
     */
    protected function _updateSlider(array $data)
    {
        $sliderId       = new IdType((int)$data['slider_id']);
        $contentGroupId = new IdType((int)$data['contentGroupId']);
        
        $sliderId->asInt() ? $this->sliderWriteService->saveSliderAssignmentForContentId($sliderId,
                                                                                         $contentGroupId) : $this->sliderWriteService->deleteSliderAssignmentByContentId($contentGroupId);
        
        return $this;
    }
    
    
    /**
     * Repairs gm_url_keywords for contents
     *
     * @return $this Same instance for chained method calls.
     */
    protected function _repairUrlKeywords()
    {
        $seoBoost = MainFactory::create_object('GMSEOBoost', [], true);
        $seoBoost->repair('contents');
        
        return $this;
    }
    
    
    /**
     * Renders the creation form for content manager main pages.
     *
     * @param string $type Content manager type to be created, whether "main", "secondary" or "info".
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    protected function _getCreationResponse($type)
    {
        $contentManager = [
            'filemanager_available' => $this->_isFilemanagerAvailable(),
            'infopage'              => [
                'form_action' => 'admin.php?do=ContentManagerPages/saveInfoPage&type=' . $type
            ],
            'linkpage'              => [
                'form_action' => 'admin.php?do=ContentManagerPages/saveLinkPage&type=' . $type
            ],
            'scriptpage'            => [
                'form_action' => 'admin.php?do=ContentManagerPages/saveScriptPage&type=' . $type,
                'filelist'    => $this->_getScriptPageFiles()
            ]
        ];
        
        $ckIdentifier = [];
        $ckTypes      = [];
        foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
            $languageCode                = $languageCode->asString();
            $ckIdentifier[$languageCode] = 'content-manager-infopage-new-content-' . $languageCode;
            $ckTypes[$languageCode]      = $this->userConfigurationService->getUserConfiguration(new IdType(0),
                                                                                                 $ckIdentifier[$languageCode]) ? : 'ckeditor';
        }
        $contentManager['infopage']['ckeditor'] = [
            'identifier' => $ckIdentifier,
            'type'       => $ckTypes,
        ];
        
        $contentManager['sliders'] = [];
        
        /** @var \SliderInterface $slider */
        foreach ($this->sliderReadService->getAllSlider()->getArray() as $slider) {
            $contentManager['sliders'][] = ['id' => $slider->getId(), 'name' => $slider->getName()];
        }
        
        $contentManager['sliderId'] = 0;
        
        $contentManager['page_token'] = $_SESSION['coo_page_token']->generate_token();
        
        $data = MainFactory::create('KeyValueCollection',
                                    [
                                        'contentType'    => $this->_getQueryParameter('contentType'),
                                        'contentManager' => $contentManager
                                    ]);
        
        $title = new NonEmptyStringType($this->languageTextManager->get_text('NEW_CONTENT_TITLE'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $this->_getTemplate('pages', 'edit'),
                                   $data,
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'pages'));
    }
    
    
    /**
     * Prepares the given content manager data to use it in the edit form.
     *
     * @param array $contentManagerData Content manager data from sql query.
     *
     * @return array Prepared content manager data.
     */
    protected function _getContentManagerEditData(array $contentManagerData)
    {
        $data = [];
        
        foreach ($contentManagerData as $key => $contentManagerDataSet) {
            try {
                $languageCode = $this->languageProvider->getCodeById(new IdType($contentManagerData[$key]['languages_id']))
                    ->asString();
                foreach ($contentManagerDataSet as $field => $value) {
                    $data[$field][$languageCode] = $value;
                }
            } catch (UnexpectedValueException $e) {
            }
        }
        
        return $data;
    }
    
    
    /**
     * Returns the required data for the edit form of content manager pages.
     *
     * @return \KeyValueCollection Contains data for the edit form of content manager entries.
     */
    protected function _getEditFormData()
    {
        $contentId   = $this->_getQueryParameter('id');
        $contentData = $this->_getContentDataById($contentId);
        
        $data = MainFactory::create('KeyValueCollection', $this->_getContentEditData($contentId, $contentData));
        
        return $data;
    }
    
    
    /**
     * Returns the content edit data array, which will be
     * converted to a key value collection and assigned to the edit form.
     *
     * @param int   $contentId   Id of content manager entry to be edited.
     * @param array $contentData Content manager data to be edited.
     *
     * @return array
     */
    protected function _getContentEditData($contentId, $contentData)
    {
        $data            = [];
        $contentPosition = $this->_getContentPosition($contentData);
        $contentType     = $this->_getContentType($contentData);
        
        // set form actions
        foreach ($this->typeMap as $type => $uiType) {
            $data['contentManager'][$this->typeMap[$type]]                = $this->_getEditData($contentId,
                                                                                                $contentData);
            $data['contentManager'][$this->typeMap[$type]]['form_action'] = 'admin.php?do=ContentManagerPages/update'
                                                                            . ucfirst($type) . 'Page&id=' . $contentId
                                                                            . '&type=' . $contentPosition;
            $data['contentManager'][$this->typeMap[$type]]['filelist']    = $this->_getScriptPageFiles();
        }
        
        $data['contentManager'][$this->typeMap[$contentType]]['ckeditor'] = $this->_getCkEditorData($contentId,
                                                                                                    $contentType);
        $data['contentManager']['filemanager_available']                  = $this->_isFilemanagerAvailable();
        $data['contentType']                                              = $contentType;
        
        foreach ($contentData as $dataSet) {
            $groupIds = implode(',',
                                array_map(function ($element) {
                                    return str_replace(['_group', 'c_'], '', $element);
                                },
                                    array_filter(explode(',', $dataSet['group_ids']))));
            
            $data['contentManager']['groupCheck']   = $groupIds;
            $data['contentManager']['contentGroup'] = $dataSet['content_group'];
            break;
        }
        
        $data['contentManager']['sliders'] = [];
        
        /** @var \SliderInterface $slider */
        foreach ($this->sliderReadService->getAllSlider()->getArray() as $slider) {
            $data['contentManager']['sliders'][] = ['id' => $slider->getId(), 'name' => $slider->getName()];
        }
        
        // special case for withdrawal and withdrawal form modal (content group id 3889895)
        if ((int)$contentId === 3889895) {
            foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
                $languageId   = $this->languageProvider->getIdByCode($languageCode);
                $languageCode = $languageCode->asString();
                
                $data['contentManager']['infopage']['withdrawal_form_file'][$languageCode]   = gm_get_content('WITHDRAWAL_FORM_FILE',
                                                                                                              $languageId);
                $data['contentManager']['scriptpage']['withdrawal_form_file'][$languageCode] = gm_get_content('WITHDRAWAL_FORM_FILE',
                                                                                                              $languageId);
            }
        }
        
        $data['contentManager']['sliderId'] = $this->sliderReadService->findAssignedSliderIdForContentId(new IdType((int)$contentData[0]['content_group']));
	
	    $data['contentManager']['page_token'] = $_SESSION['coo_page_token']->generate_token();
	    
	    $data['contentManager']['themeSystemIsActive'] = $this->getThemeControlService()->isThemeSystemActive();
	    $data['contentManager']['styleEditIsInstalled'] = $this->getStyleEditService()->styleEditIsInstalled();
    
        $data['contentManager']['shop_page_link'] = xtc_catalog_href_link(
            'shop_content.php',
            "coID={$contentId}"
        );
        
        return $data;
    }
    
    
    /**
     * Executes an sql query again the url_rewrites table and returns the result.
     *
     * @param int $contentId Url rewrites table "content_id" value.
     *
     * @return UrlRewriteCollection Url rewrite collection.
     */
    protected function _getUrlRewriteEditData($contentId)
    {
        return $this->urlRewriteStorage->get(new IdType($contentId));
    }
    
    
    /**
     * Returns content manager data to be edited, prepared for the edit form.
     *
     * @param int   $contentId   Content manager tables "content_group" value.
     * @param array $contentData Raw data set from sql query.
     *
     * @return array Prepared data set array for edit forms of content manager pages.
     */
    protected function _getEditData($contentId, array $contentData)
    {
        $contentManagerEditData = $this->_getContentManagerEditData($contentData);
        
        /** @var UrlRewrite $urlRewrite */
        foreach ($this->_getUrlRewriteEditData($contentId) as $urlRewrite) {
            $languageCode = $this->languageProvider->getCodeById(new IdType($urlRewrite->getLanguageId()));
            
            $contentManagerEditData['url_rewrite'][$languageCode->asString()] = $urlRewrite->getRewriteUrl();
        }
        
        return $contentManagerEditData;
    }
    
    
    /**
     * Returns required data for CkEditor settings.
     *
     * @param int    $contentId Content manager tables "content_group" value.
     * @param string $type      Content type, whether "content", "file" or "link".
     *
     * @return array
     */
    protected function _getCkEditorData($contentId, $type)
    {
        $ckIdentifier = [];
        $ckTypes      = [];
        
        foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
            $languageCode                = $languageCode->asString();
            $ckIdentifier[$languageCode] = 'content-manager-' . $this->typeMap[$type] . '-content-' . $contentId . '-'
                                           . $languageCode;
            $ckTypes[$languageCode]      = $this->userConfigurationService->getUserConfiguration(new IdType(0),
                                                                                                 $ckIdentifier[$languageCode]) ? : 'ckeditor';
        }
        
        return [
            'identifier' => $ckIdentifier,
            'type'       => $ckTypes,
        ];
    }
    
    
    /**
     * Executes an sql query again the content_manager table and returns the result.
     *
     * @param int $contentId Content manager tables "content_group" value.
     *
     * @return array Content manager table data.
     */
    protected function _getContentDataById($contentId)
    {
        return $this->queryBuilder->select()
            ->from('content_manager')
            ->where('content_group', $contentId)
            ->get()
            ->result_array();
    }
    
    
    /**
     * Returns the content position of the given query result.
     *
     * @param array $queryResult Data sets of query for content_manager table.
     *
     * @return string Whether "pages_main", "pages_secondary" or "pages_info".
     */
    protected function _getContentPosition(array $queryResult)
    {
        foreach ($queryResult as $result) {
            return str_replace('pages_', '', $result['content_position']);
        }
        
        return 'main';
    }
    
    
    /**
     * Prepares $_POST data for updating or inserting into the database.
     *
     * @param string $contentManagerType Whether "infopage", "linkpage" or "scriptpage".
     *
     * @return array Contains data sets for content_manager- and url_rewrites table and an additional content group id.
     */
    protected function _prepareData($contentManagerType)
    {
        $contentGroupId = $this->_getQueryParameter('id') ? : $this->_createNewContentGroupId($this->queryBuilder);
        $data           = $this->_prepareContentManagerData($contentGroupId, $contentManagerType);
        $urlRewrites    = [];
        
        // prepare url keywords and rewrite data sets
        // and remove content_file if its not a scriptpage
        foreach ($data as $languageId => &$datum) {
            if (array_key_exists('url_rewrite', $datum) && $datum['url_rewrite'] !== '') {
                $urlRewrites[$datum['languages_id']] = $datum['url_rewrite'];
            }
            unset($data[$languageId]['url_rewrite']);
            
            if (array_key_exists('gm_url_keywords', $datum)) {
                $datum['gm_url_keywords'] = xtc_cleanName($datum['gm_url_keywords']);
            }
            
            if ($contentManagerType !== 'scriptpage') {
                $datum['content_file'] = '';
            }
        }
        
        return [
            'contentData'    => $data,
            'urlRewrites'    => $urlRewrites,
            'contentGroupId' => $contentGroupId,
            'slider_id'      => $this->_getPostData('content_manager')['slider_id']
        ];
    }
    
    
    /**
     * Prepares $_POST data for updating or inserting into the content_manager table.
     *
     * @param int    $contentGroupId     Content manager tables "content_group" value.
     * @param string $contentManagerType Whether "infopage", "linkpage" or "scriptpage".
     *
     * @return array Data set for the content_manager table.
     */
    protected function _prepareContentManagerData($contentGroupId, $contentManagerType)
    {
        $contentManagerData  = $this->_getPostData('content_manager')[$contentManagerType];
        $newContentGroupId   = $this->_getPostData('content_manager')['content_group_id'];
        $data                = [];
        $adminLanguageCodes  = array_map(function ($languageCode) {
            return (string)$languageCode;
        },
            $this->languageProvider->getAdminCodes()->getArray());
        $defaultLanguageId   = $this->languageProvider->getDefaultLanguageId();
        $defaultLanguageCode = strtoupper($this->languageProvider->getDefaultLanguageCode());
        $isNewEntry          = $this->_getQueryParameter('id') === null;
        
        if ($this->_isFilemanagerAvailable() === false && $contentManagerType === 'scriptpage') {
            $contentManagerData['content_file'] = $this->_checkScriptpageFileUploads();
        }
        
        foreach ($this->fieldMap as $field) {
            if (array_key_exists($field, $contentManagerData)) {
                foreach ($contentManagerData[$field] as $languageCode => $value) {
                    $languageId      = $this->languageProvider->getIdByCode(new LanguageCode(new StringType($languageCode)));
                    $contentPosition = 'pages_' . $this->_getQueryParameter('type');
                    
                    $data[$languageId]['languages_id']     = $languageId;
                    $data[$languageId]['content_group']    = $newContentGroupId ? : $contentGroupId;
                    $data[$languageId]['content_position'] = $contentPosition;
                    $data[$languageId]['group_ids']        = $this->_prepareContentManagerGroupCheckData();
                    $data[$languageId]['file_flag']        = array_flip($this->fileTypMap)[$this->contentTypeFileFlagMap[$contentPosition]];
                    
                    if ($isNewEntry && empty($value) && $languageId !== $defaultLanguageId
                        && !in_array($languageCode,
                                     $adminLanguageCodes,
                                     true)) {
                        $value = $contentManagerData[$field][$defaultLanguageCode];
                    }
                    $data[$languageId][$field] = $value;
                }
            }
        }
        
        foreach ($data as $languageId => $datum) {
            foreach ($this->switcherFields as $switcherField) {
                if (!array_key_exists($switcherField, $datum)) {
                    $data[$languageId][$switcherField] = '0';
                }
            }
        }
        
        return $data;
    }
    
    
    /**
     * Fetches and returns the content data for the content manager pages.
     *
     * @return array
     */
    protected function _getPagesData()
    {
        $main            = [];
        $secondary       = [];
        $other           = [];
        $infoBox         = [];
        $contentStatus   = [];
        $additional      = [];

        $queryResult = $this->queryBuilder->select()
            ->from('content_manager')
            ->order_by('sort_order')
            ->get()
            ->result_array();
        
        foreach ($queryResult as $result) {
            if ((int)$result['languages_id'] === (int)$_SESSION['languages_id']) {
                if ($result['content_position'] === 'pages_main') {
                    $main[] = [
                        'contentId'   => $result['content_id'],
                        'link'        => $result['gm_link'],
                        'sortOrder'   => $result['sort_order'],
                        'id'          => $result['content_group'],
                        'name'        => $result['content_name'],
                        'title'       => $result['content_title'],
                        'type'        => $result['content_type'],
                        'description' => $this->_getContentDescription($result['content_type']),
                        'deletable'   => (int)$result['content_delete'] === 1,
                        'public_link' => xtc_catalog_href_link(
                            'shop_content.php',
                            "coID={$result['content_group']}"
                        )
                    ];
                }
                if ($result['content_position'] === 'pages_secondary') {
                    $secondary[] = [
                        'contentId'   => $result['content_id'],
                        'link'        => $result['gm_link'],
                        'sortOrder'   => $result['sort_order'],
                        'id'          => $result['content_group'],
                        'name'        => $result['content_name'],
                        'title'       => $result['content_title'],
                        'type'        => $result['content_type'],
                        'description' => $this->_getContentDescription($result['content_type']),
                        'deletable'   => (int)$result['content_delete'] === 1,
                        'public_link' => xtc_catalog_href_link(
                            'shop_content.php',
                            "coID={$result['content_group']}"
                        )
                    ];
                }
                if ($result['content_position'] === 'pages_info') {
                    $other[] = [
                        'contentId'   => $result['content_id'],
                        'link'        => $result['gm_link'],
                        'sortOrder'   => $result['sort_order'],
                        'id'          => $result['content_group'],
                        'name'        => $result['content_name'],
                        'title'       => $result['content_title'],
                        'type'        => $result['content_type'],
                        'description' => $this->_getContentDescription($result['content_type']),
                        'deletable'   => (int)$result['content_delete'] === 1,
                        'public_link' => xtc_catalog_href_link(
                            'shop_content.php',
                            "coID={$result['content_group']}"
                        )
                    ];
                }
                if ($result['content_position'] === 'pages_info_box') {
                    $infoBox[] = [
                        'contentId'   => $result['content_id'],
                        'link'        => $result['gm_link'],
                        'sortOrder'   => $result['sort_order'],
                        'id'          => $result['content_group'],
                        'name'        => $result['content_name'],
                        'title'       => $result['content_title'],
                        'type'        => $result['content_type'],
                        'description' => $this->_getContentDescription($result['content_type']),
                        'deletable'   => (int)$result['content_delete'] === 1,
                        'public_link' => xtc_catalog_href_link(
                            'shop_content.php',
                            "coID={$result['content_group']}"
                        )
                    ];
                }
                if ($result['content_position'] === 'pages_additional') {
                    $additional[] = [
                        'contentId'   => $result['content_id'],
                        'link'        => $result['gm_link'],
                        'sortOrder'   => $result['sort_order'],
                        'id'          => $result['content_group'],
                        'name'        => $result['content_name'],
                        'title'       => $result['content_title'],
                        'type'        => $result['content_type'],
                        'description' => $this->_getContentDescription($result['content_type']),
                        'deletable'   => (int)$result['content_delete'] === 1,
                        'public_link' => xtc_catalog_href_link(
                            'shop_content.php',
                            "coID={$result['content_group']}"
                        )
                    ];
                }
            }
            
            if (!isset($contentStatus[(int)$result['content_group']])) {
                $contentStatus[(int)$result['content_group']] = 0;
            }
            if ((int)$result['content_status'] === 1) {
                $contentStatus[(int)$result['content_group']]++;
            }
        }
        
        return [
            'main'            => $main,
            'secondary'       => $secondary,
            'other'           => $other,
            'infoBox'         => $infoBox,
            'contentStatus'   => $contentStatus,
            'additional'      => $additional,
        ];
    }
    
    
    /**
     * Detects the content type names from the queries result data.
     *
     * @param string $contentType Must be whether "link", "file" or "content".
     *
     * @return string Names of content types for whether "link", "file" or "content".
     */
    protected function _getContentDescription($contentType)
    {
        $descriptions = [
            'link'    => $this->languageTextManager->get_text('DESCRIPTION_LINK'),
            'file'    => $this->languageTextManager->get_text('DESCRIPTION_FILE'),
            'content' => $this->languageTextManager->get_text('DESCRIPTION_CONTENT')
        ];
        
        return $descriptions[$contentType];
    }
    
    
    /**
     * Detects file uploads for scriptpages and returns the array for new content_file post data.
     *
     * @return array
     */
    protected function _checkScriptpageFileUploads()
    {
        $return = $this->_getPostData('content_manager')['scriptpage']['content_file'];
        if (count($_FILES['content_manager']['name']['scriptpage']['content_file']) > 0) {
            foreach ($_FILES['content_manager']['name']['scriptpage']['content_file'] as $key => $filename) {
                if (!empty($filename)
                    && $_FILES['content_manager']['error']['scriptpage']['content_file'][$key] === 0) {
                    // move uploaded file into media/content directory
                    $directory    = DIR_FS_CATALOG . 'media' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR;
                    $tempFilename = $_FILES['content_manager']['tmp_name']['scriptpage']['content_file'][$key];
                    move_uploaded_file($tempFilename, $directory . $filename);
                    
                    // update post data for selected content_file. set this value to the uploaded file.
                    $return[$key] = $filename;
                }
            }
        }
        
        return $return;
    }
	
	
	/**
	 * Verifies the page token and stops script if the token is invalid.
	 */
	protected function _verifyPageToken()
	{
		$_SESSION['coo_page_token']->is_valid($_POST['page_token']);
	}
    
    
    /**
     * @return \ThemeControl
     */
    protected function getThemeControlService()
    {
        return StaticGXCoreLoader::getThemeControl();
    }
    
    
    /**
     * @return StyleEditServiceInterface
     */
    protected function getStyleEditService()
    {
        return StyleEditServiceFactory::service();
    }
    
}
