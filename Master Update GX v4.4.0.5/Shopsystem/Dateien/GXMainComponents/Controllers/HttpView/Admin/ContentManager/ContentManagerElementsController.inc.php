<?php
/* --------------------------------------------------------------
 ContentManagerPagesController.inc.php 2021-04-23
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

/**
 * Class ContentManagerPagesController
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class ContentManagerElementsController extends AdminHttpViewController
{
    use ContentManagerContentNavigationTrait;
    
    /**
     * @var \UserConfigurationService
     */
    private $userConfigurationService;
    
    /**
     * @var \LanguageTextManager
     */
    private $languageTextManager;
    
    /**
     * @var \CI_DB_query_builder
     */
    protected $queryBuilder;
    
    /**
     * @var \LanguageProvider
     */
    protected $languageProvider;
    
    /**
     * @var \NonEmptyStringType
     */
    protected $title;
    
    /**
     * @var array
     */
    protected $fieldMap = [
        'content_title',
        'content_heading',
        'content_text',
        'content_status',
        'content_file',
        'content_type'
    ];
    
    /**
     * @var array
     */
    protected $switcherFields = [
        'content_status'
    ];
    
    /**
     * @var array
     */
    protected $typeMap = [
        'home'   => 'elements_start',
        'header' => 'elements_header',
        'footer' => 'elements_footer',
        'boxes'  => 'elements_boxes'
    ];
    
    
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
        $this->title                    = new NonEmptyStringType($this->languageTextManager->get_text('HEADING_TITLE'));
    }
    
    
    /**
     * Default actions, renders the content manager elements overview.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionDefault()
    {
        $this->_setExpertMode();
        
        $contentData = $this->_getContentData();
        $data        = MainFactory::create('KeyValueCollection',
                                           [
                                               'home'          => $contentData['home'],
                                               'header'        => $contentData['header'],
                                               'footer'        => $contentData['footer'],
                                               'boxes'         => $contentData['boxes'],
                                               'styleEdit'     => $contentData['styleEdit'],
                                               'others'        => $contentData['others'],
                                               'withdrawal'    => $contentData['withdrawal'],
                                               'contentStatus' => $contentData['contentStatus'],
                                           ]);
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $this->title,
                                   $this->_getTemplate('elements', 'overview'),
                                   $data,
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'elements'));
    }
    
    
    /**
     * Renders the creation form for content manager home element pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateHome()
    {
        $this->_setExpertMode();
        
        $title = new NonEmptyStringType($this->languageTextManager->get_text('NEW_CONTENT_TITLE'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $this->_getTemplate('elements', 'edit'),
                                   $this->_getCreationData('home'),
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'elements'));
    }
    
    
    /**
     * Renders the creation form for content manager header element pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateHeader()
    {
        $this->_setExpertMode();
        
        $title = new NonEmptyStringType($this->languageTextManager->get_text('NEW_CONTENT_TITLE'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $this->_getTemplate('elements', 'edit'),
                                   $this->_getCreationData('header'),
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'elements'));
    }
    
    
    /**
     * Renders the creation form for content manager footer element pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateFooter()
    {
        $this->_setExpertMode();
        
        $title = new NonEmptyStringType($this->languageTextManager->get_text('NEW_CONTENT_TITLE'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $this->_getTemplate('elements', 'edit'),
                                   $this->_getCreationData('footer'),
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'elements'));
    }
    
    
    /**
     * Renders the creation form for content manager boxes element pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateBoxes()
    {
        $this->_setExpertMode();
        
        $title = new NonEmptyStringType($this->languageTextManager->get_text('NEW_CONTENT_TITLE'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $this->_getTemplate('elements', 'edit'),
                                   $this->_getCreationData('boxes'),
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'elements'));
    }
    
    
    /**
     * Renders the creation form for content manager others element pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateOthers()
    {
        $this->_setExpertMode();
        
        $title = new NonEmptyStringType($this->languageTextManager->get_text('NEW_CONTENT_TITLE'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $this->_getTemplate('elements', 'edit'),
                                   $this->_getCreationData('others'),
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'elements'));
    }
    
    
    /**
     * Renders the creation form for content manager withdrawal element pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionCreateWithdrawal()
    {
        $this->_setExpertMode();
        
        $title = new NonEmptyStringType($this->languageTextManager->get_text('NEW_CONTENT_TITLE'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $this->_getTemplate('elements', 'edit'),
                                   $this->_getCreationData('withdrawal'),
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'elements'));
    }
    
    
    /**
     * Renders the edit form for content manager home element pages.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionEdit()
    {
        $this->_setExpertMode();
        
        $formData = $this->_getEditData();
        
        $languageCode = $this->languageProvider->getCodeById(new IdType($_SESSION['languages_id']));
        $contentData  = $formData->getValue('contentManager');
        
        $title = new NonEmptyStringType($this->languageTextManager->get_text('CONTENT_TITLE') . ': '
                                        . $contentData['content_title'][$languageCode->asString()]);
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $this->_getTemplate('elements', 'edit'),
                                   $formData,
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($this->languageTextManager, 'elements'));
    }
    
    
    /**
     * Stores content manager home element data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSaveHome()
    {
        $this->_setExpertMode();
        
        $data = $this->_preparePostData('start');
        
        return $this->_insertContentData($this->queryBuilder, $data['data'])
            ->_getUpdateResponse('ContentManagerElements',
                                 $data['contentGroupId']);
    }
    
    
    /**
     * Stores content manager header element data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSaveHeader()
    {
        $this->_setExpertMode();
        
        $data = $this->_preparePostData('header');
        
        return $this->_insertContentData($this->queryBuilder, $data['data'])
            ->_getUpdateResponse('ContentManagerElements',
                                 $data['contentGroupId']);
    }
    
    
    /**
     * Stores content manager footer element data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSaveFooter()
    {
        $this->_setExpertMode();
        
        $data = $this->_preparePostData('footer');
        
        return $this->_insertContentData($this->queryBuilder, $data['data'])
            ->_getUpdateResponse('ContentManagerElements',
                                 $data['contentGroupId']);
    }
    
    
    /**
     * Stores content manager boxes element data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSaveBoxes()
    {
        $this->_setExpertMode();
        
        $data = $this->_preparePostData('boxes');
        
        return $this->_insertContentData($this->queryBuilder, $data['data'])
            ->_getUpdateResponse('ContentManagerElements',
                                 $data['contentGroupId']);
    }
    
    
    /**
     * Stores content manager others element data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSaveOthers()
    {
        $this->_setExpertMode();
        
        $data = $this->_preparePostData('others');
        
        return $this->_insertContentData($this->queryBuilder, $data['data'])
            ->_getUpdateResponse('ContentManagerElements',
                                 $data['contentGroupId']);
    }
    
    
    /**
     * Stores content manager withdrawal element data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSaveWithdrawal()
    {
        $this->_setExpertMode();
        
        $data = $this->_preparePostData('withdrawal');
        
        return $this->_insertContentData($this->queryBuilder, $data['data'])
            ->_getUpdateResponse('ContentManagerElements',
                                 $data['contentGroupId']);
    }
    
    
    /**
     * Updates content manager elements data in the database and redirects to the overview.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionUpdate()
    {
        $this->_setExpertMode();
        
        $data = $this->_preparePostData($this->_getQueryParameter('type'));
        
        return $this->_updateContentData($this->queryBuilder, $data['data'], $data['contentGroupId'])
            ->_getUpdateResponse('ContentManagerElements', $data['contentGroupId']);
    }
    
    
    /**
     * Creates the template data for creation pages.
     * The action determines the target location after clicking the submit button.
     *
     * @param string $action Should be whether "home", "header", "footer" or "boxes".
     *
     * @return array|bool|\KeyValueCollection
     */
    protected function _getCreationData($action)
    {
        $ckIdentifier = [];
        $ckTypes      = [];
        foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
            $languageCode                = $languageCode->asString();
            $ckIdentifier[$languageCode] = 'content-manager-elements-new-content-' . $languageCode;
            $ckTypes[$languageCode]      = $this->userConfigurationService->getUserConfiguration(new IdType(0),
                                                                                                 $ckIdentifier[$languageCode]) ? : 'ckeditor';
        }
        
        $data = [
            'contentManager' => [
                'page_token'            => $_SESSION['coo_page_token']->generate_token(),
                'contentType'           => $this->_getQueryParameter('contentType'),
                'filelist'              => $this->_getScriptPageFiles(),
                'filemanager_available' => $this->_isFilemanagerAvailable(),
                'elements-content'      => [
                    'form_action' => 'admin.php?do=ContentManagerElements/save' . ucfirst($action),
                    'ckeditor'    => [
                        'identifier' => $ckIdentifier,
                        'type'       => $ckTypes,
                    ],
                ],
                'elements-script'       => [
                    'form_action' => 'admin.php?do=ContentManagerElements/save' . ucfirst($action),
                    'ckeditor'    => [
                        'identifier' => $ckIdentifier,
                        'type'       => $ckTypes,
                    ],
                ],
            ],
        ];
        
        return MainFactory::create('KeyValueCollection', $data);
    }
    
    
    /**
     * Prepares and returns the content manager data to be edited.
     *
     * @return \KeyValueCollection
     */
    protected function _getEditData()
    {
        $contentId          = $this->_getQueryParameter('id');
        $elementsData       = $this->queryBuilder->select()
            ->from('content_manager')
            ->where('content_group', $contentId)
            ->get()
            ->result_array();
        $contentManagerData = [];
        
        foreach ($elementsData as $key => $elementsDataDataSet) {
            $languageCode = $this->languageProvider->getCodeById(new IdType($elementsDataDataSet['languages_id']))
                ->asString();
            foreach ($elementsDataDataSet as $field => $value) {
                if (!empty($elementsDataDataSet['content_position'])) {
                    $contentType = !empty($elementsDataDataSet['content_position']) ? str_replace('elements_',
                        '',
                        $elementsDataDataSet['content_position']) : 'start';
                }
                $contentManagerData[$field][$languageCode] = $value;
            }
        }
        if(empty($contentType)) $contentType = "start";
        
        $contentManagerData['elements-content']['form_action'] = 'admin.php?do=ContentManagerElements/update&id='
                                                                 . $contentId . '&type=' . $contentType;
        $contentManagerData['elements-script']['form_action']  = 'admin.php?do=ContentManagerElements/update&id='
                                                                 . $contentId . '&type=' . $contentType;
        
        $ckIdentifier = [];
        $ckTypes      = [];
        foreach ($this->languageProvider->getCodes()->getArray() as $languageCode) {
            $languageCode                = $languageCode->asString();
            $ckIdentifier[$languageCode] = 'content-manager-elements-content-' . $contentId . '-' . $languageCode;
            $ckTypes[$languageCode]      = $this->userConfigurationService->getUserConfiguration(new IdType(0),
                                                                                                 $ckIdentifier[$languageCode]) ? : 'ckeditor';
        }
        $contentManagerData['elements']['ckeditor'] = [
            'identifier' => $ckIdentifier,
            'type'       => $ckTypes,
        ];
        
        if (isset($_SESSION['content_manager_selected_language'])) {
            $contentManagerData['elements']['selected_language'] = $_SESSION['content_manager_selected_language'];
            unset($_SESSION['content_manager_selected_language']);
        }
        
        foreach ($elementsData as $dataSet) {
            if ((int)$_SESSION['languages_id'] !== (int)$dataSet['languages_id']) {
                continue;
            }
            
            $groupIds = implode(',',
                                array_map(function ($element) {
                                    return str_replace(['_group', 'c_'], '', $element);
                                },
                                    array_filter(explode(',', $dataSet['group_ids']))));
            
            $contentManagerData['groupCheck'] = $groupIds;
            break;
        }
        $contentManagerData['filemanager_available'] = $this->_isFilemanagerAvailable();
        $contentManagerData['filelist']              = $this->_getScriptPageFiles();
        $contentManagerData['contentType']           = $this->_getContentType($elementsData);
	    $contentManagerData['page_token']            = $_SESSION['coo_page_token']->generate_token();
        
        return MainFactory::create('KeyValueCollection',
                                   [
                                       'contentManager' => $contentManagerData
                                   ]);
    }
    
    
    /**
     * Prepares the content manager elements post data.
     *
     * @param string $type Content manager type, whether "home", "header", "footer" or "boxes".
     *
     * @return array Prepared data array for inserting or updating in database.
     */
    protected function _preparePostData($type)
    {
	    $_SESSION['coo_page_token']->is_valid($_POST['page_token']);
    	
        $elementsData      = $this->_getPostData('content_manager');
        $contentGroupId    = $this->_getQueryParameter('id') ? : $this->_createNewContentGroupId($this->queryBuilder);
        $newContentGroupId = $this->_getPostData('content_manager')['content_group_id'];
        $data              = [];
        
        if ($this->_isFilemanagerAvailable() === false && $this->_getPostData('content_type') === 'file') {
            $elementsData['content_file'] = $this->_checkScriptpageFileUploads();
        }
        
        foreach ($this->fieldMap as $field) {
            if (array_key_exists($field, $elementsData)) {
                foreach ($elementsData[$field] as $languageCode => $value) {
                    $languageId      = $this->languageProvider->getIdByCode(new LanguageCode(new StringType($languageCode)));
                    $contentPosition = 'elements_' . $type;
                    
                    $data[$languageId]['languages_id']     = $languageId;
                    $data[$languageId]['content_group']    = $newContentGroupId ? : $contentGroupId;
                    $data[$languageId]['content_position'] = $contentPosition;
                    $data[$languageId]['group_ids']        = $this->_prepareContentManagerGroupCheckData();
                    $data[$languageId]['file_flag']        = array_flip($this->fileTypMap)[$this->contentTypeFileFlagMap[$contentPosition]];
                    
                    $data[$languageId][$field] = $value;
                    
                    if ($this->_getPostData('content_type') === 'content') {
                        $data[$languageId]['content_file'] = '';
                    }
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
        
        return [
            'data'           => $data,
            'contentGroupId' => $contentGroupId
        ];
    }
    
    
    /**
     * Detects file uploads for scriptpages and returns the array for new content_file post data.
     *
     * @return array
     */
    protected function _checkScriptpageFileUploads()
    {
        $return = $this->_getPostData('content_manager')['content_file'];
        if (count($_FILES['content_manager']['name']['content_file']) > 0) {
            foreach ($_FILES['content_manager']['name']['content_file'] as $key => $filename) {
                if (!empty($filename) && $_FILES['content_manager']['error']['content_file'][$key] === 0) {
                    // move uploaded file into media/content directory
                    $directory    = DIR_FS_CATALOG . 'media' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR;
                    $tempFilename = $_FILES['content_manager']['tmp_name']['content_file'][$key];
                    move_uploaded_file($tempFilename, $directory . $filename);
                    
                    // update post data for selected content_file. set this value to the uploaded file.
                    $return[$key] = $filename;
                }
            }
        }
        
        return $return;
    }
    
    
    /**
     * Fetches and returns the content data for the content manager elements.
     *
     * @return array
     */
    protected function _getContentData()
    {
        $query = $this->queryBuilder->select()
            ->from('content_manager')
            ->like('content_position', 'elements')
            ->order_by('content_title',
                       'ASC')
            ->get()
            ->result_array();
        
        $home          = [];
        $header        = [];
        $footer        = [];
        $boxes         = [];
        $others        = [];
        $withdrawal    = [];
        $contentStatus = [];
        $styleEdit     = [];
        
        foreach ($query as $result) {
            if ((int)$result['languages_id'] === (int)$_SESSION['languages_id']) {
                if ($result['content_position'] === 'elements_start') {
                    $home[] = [
                        'id'        => $result['content_group'],
                        'contentId' => $result['content_id'],
                        'name'      => $result['content_title'],
                        'type'      => $result['content_type'],
                        'deletable' => (int)$result['content_delete'] === 1
                    ];
                }
                if ($result['content_position'] === 'elements_header') {
                    $header[] = [
                        'id'        => $result['content_group'],
                        'contentId' => $result['content_id'],
                        'name'      => $result['content_title'],
                        'type'      => $result['content_type'],
                        'deletable' => (int)$result['content_delete'] === 1
                    ];
                }
                if ($result['content_position'] === 'elements_footer') {
                    $footer[] = [
                        'id'        => $result['content_group'],
                        'contentId' => $result['content_id'],
                        'name'      => $result['content_title'],
                        'type'      => $result['content_type'],
                        'deletable' => (int)$result['content_delete'] === 1
                    ];
                }
                if ($result['content_position'] === 'elements_boxes') {
                    $boxes[] = [
                        'id'        => $result['content_group'],
                        'contentId' => $result['content_id'],
                        'name'      => $result['content_title'],
                        'type'      => $result['content_type'],
                        'deletable' => (int)$result['content_delete'] === 1
                    ];
                }
                if ($result['content_position'] === 'elements_others') {
                    $others[] = [
                        'id'        => $result['content_group'],
                        'contentId' => $result['content_id'],
                        'name'      => $result['content_title'],
                        'type'      => $result['content_type'],
                        'deletable' => (int)$result['content_delete'] === 1,
                    ];
                }
                if ($result['content_position'] === 'elements_withdrawal') {
                    $withdrawal[] = [
                        'id'        => $result['content_group'],
                        'contentId' => $result['content_id'],
                        'name'      => $result['content_title'],
                        'type'      => $result['content_type'],
                        'deletable' => (int)$result['content_delete'] === 1,
                    ];
                }
                if ($result['content_position'] === 'elements_styleedit') {
                    $styleEdit[] = [
                        'id'        => $result['content_group'],
                        'contentId' => $result['content_id'],
                        'name'      => $result['content_title'],
                        'type'      => $result['content_type'],
                        'deletable' => (int)$result['content_delete'] === 1,
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
            'home'          => $home,
            'header'        => $header,
            'footer'        => $footer,
            'boxes'         => $boxes,
            'others'        => $others,
            'withdrawal'    => $withdrawal,
            'contentStatus' => $contentStatus,
            'styleEdit'     => $styleEdit
        ];
    }
}
