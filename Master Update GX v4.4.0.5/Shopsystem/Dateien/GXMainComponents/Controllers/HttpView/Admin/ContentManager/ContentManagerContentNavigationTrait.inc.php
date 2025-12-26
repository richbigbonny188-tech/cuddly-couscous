<?php
/**
 * ContentManagerContentNavigationTrait.inc.php 2020-4-16
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

trait ContentManagerContentNavigationTrait
{
    /**
     * @var array
     */
    protected $contentTypeFileFlagMap = [
        'pages_main'          => 'topmenu',
        'pages_secondary'     => 'topmenu_corner',
        'pages_info'          => 'content',
        'pages_info_box'      => 'information',
        'pages_additional'    => 'additional',
        'elements_start'      => 'extraboxes',
        'elements_header'     => 'extraboxes',
        'elements_styleedit'  => 'information',
        'elements_footer'     => 'extraboxes',
        'elements_boxes'      => 'extraboxes',
        'elements_others'     => 'extraboxes',
        'elements_withdrawal' => 'withdrawal',
    ];
    
    /**
     * Order of this array is important as it
     * will update the file flag in the database on updating
     * @var array
     */
    protected $fileTypMap = [
        0 => 'information',
        1 => 'content',
        2 => 'topmenu_corner',
        3 => 'topmenu',
        4 => 'extraboxes',
        5 => 'withdrawal',
        6 => 'additional',
    ];
    
    /**
     * @var bool
     */
    protected $isExpertMode = false;
    
    
    /**
     * Creates the content navigation object for the content manager templates.
     *
     * @param \LanguageTextManager $languageTextManager Text manager instance to fetch texts.
     * @param string|null          $current             (Optional) Whether "pages", "elements" or "productContents" to
     *                                                  set nav item active.
     *
     * @return \ContentNavigationCollection
     */
    protected function _createContentNavigation(LanguageTextManager $languageTextManager, $current = null)
    {
        AdminMenuControl::connect_with_page('admin.php?do=ContentManagerPages');
        
        $pagesTitle = new StringType($languageTextManager->get_text('PAGE_TITLE_PAGES'));
        $pagesUrl   = new StringType('admin.php?do=ContentManagerPages');
        
        $elementsTitle = new StringType($languageTextManager->get_text('PAGE_TITLE_ELEMENTS'));
        $elementsUrl   = new StringType('admin.php?do=ContentManagerElements');
        
        $productContentsTitle = new StringType($languageTextManager->get_text('PAGE_TITLE_PRODUCT_CONTENTS'));
        $productContentsUrl   = new StringType('admin.php?do=ContentManagerProductContents');
        
        $contentNavigation = MainFactory::create('ContentNavigationCollection', []);
        
        $true  = new BoolType(true);
        $false = new BoolType(false);
        
        $contentNavigation->add($pagesTitle, $pagesUrl, $current === 'pages' ? $true : $false);
        $contentNavigation->add($elementsTitle, $elementsUrl, $current === 'elements' ? $true : $false);
        $contentNavigation->add($productContentsTitle,
                                $productContentsUrl,
                                $current === 'productContents' ? $true : $false);
        
        return $contentNavigation;
    }
    
    
    /**
     * Creates a new content group id.
     *
     * @param \CI_DB_query_builder $queryBuilder Query builder instance to access the database.
     *
     * @return int New content manager group id.
     */
    protected function _createNewContentGroupId(CI_DB_query_builder $queryBuilder)
    {
        return (int)$queryBuilder->select('content_group')
                        ->from('content_manager')
                        ->where('`content_group` < 3889891')
                        ->order_by('content_group', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row_array()['content_group'] + 1;
    }
    
    
    /**
     * Whether redirects to the last overview or update pages.
     *
     * @param string $contentManagerType Name of content manager controller class.
     * @param int    $contentGroupId     Content id of last edited content.
     *
     * @return \RedirectHttpControllerResponse
     */
    protected function _getUpdateResponse($contentManagerType, $contentGroupId, $editMethod = 'edit')
    {
        $expertModeQueryParameter = $this->isExpertMode ? '&expert' : '';
        
        if (isset($this->_getPostData('content_manager')['content_group_id'])
            && $this->_getPostData('content_manager')['content_group_id'] > 0) {
            $contentGroupId = $this->_getPostData('content_manager')['content_group_id'];
        }
        
        $update = (int)$this->_getQueryParameter('update') === 1 ? true : false;
        if ($update) {
            $selectedLanguage = $this->_getPostData('content_manager')['selected_language'];
            if (!empty($selectedLanguage)) {
                $_SESSION['content_manager_selected_language'] = $selectedLanguage;
            }
            
            return MainFactory::create('RedirectHttpControllerResponse',
                                       'admin.php?do=' . $contentManagerType . '/' . $editMethod . '&id='
                                       . $contentGroupId . $expertModeQueryParameter);
        }
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   'admin.php?do=' . $contentManagerType . $expertModeQueryParameter);
    }
    
    
    /**
     * Inserts the given content data in the database.
     *
     * @param \CI_DB_query_builder $queryBuilder Query builder instance to access the database.
     * @param array                $contentData  Content data array.
     *
     * @return $this|\ContentManagerPagesController Same instance for chained method calls.
     */
    protected function _insertContentData(CI_DB_query_builder $queryBuilder, array $contentData)
    {
        foreach ($contentData as $contentDataSet) {
            $queryBuilder->insert('content_manager', $contentDataSet);
            $queryBuilder->replace('content_manager_history', $contentDataSet);
        }
        
        return $this;
    }
    
    
    /**
     * Returns the assets for the content manager editing and creation pages.
     *
     * @return \AssetCollection
     */
    protected function _getAssets()
    {
        $assets = MainFactory::create('AssetCollection');
        $assets->add(MainFactory::create('Asset', 'admin_buttons.lang.inc.php'));
        $assets->add(MainFactory::create('Asset', 'content_manager.lang.inc.php'));
        $assets->add(MainFactory::create('Asset', 'shipping_and_payment_matrix.lang.inc.php'));
        $assets->add(MainFactory::create('Asset', 'includes/ckeditor/ckeditor.js'));
        
        return $assets;
    }
    
    
    /**
     * Returns an existing file object with the path to a content manager template file.
     * Take a look on the template files which are located in html/content/content_manager/$type directory
     * to know possible values for the $name argument.
     *
     * @param string $type Content manager type, whether "pages", "elements" or "product_contents".
     * @param string $name Name of template file.
     *
     * @return \ExistingFile
     */
    protected function _getTemplate($type, $name)
    {
        return new ExistingFile(new NonEmptyStringType(DIR_FS_ADMIN . '/html/content/content_manager/' . $type . '/'
                                                       . $name . '.html'));
    }
    
    
    /**
     * Updates the given content data in the database.
     *
     * @param \CI_DB_query_builder $queryBuilder   Query builder instance to access the database.
     * @param array                $contentData    Content data array.
     * @param int                  $contentGroupId Content group id.
     *
     * @return $this|\ContentManagerPagesController Same instance for chained method calls.
     */
    protected function _updateContentData(CI_DB_query_builder $queryBuilder, array $contentData, $contentGroupId)
    {
        foreach ($contentData as $contentDataSet) {
            $queryBuilder->update('content_manager',
                                  $contentDataSet,
                                  [
                                      'content_group' => $contentGroupId,
                                      'languages_id'  => $contentDataSet['languages_id']
                                  ]);
            $queryBuilder->replace('content_manager_history', $contentDataSet);
        }
        
        return $this;
    }
    
    
    /**
     * Returns an array with allowed script files for content data.
     *
     * @return array List with allowed script files.
     */
    protected function _getScriptPageFiles()
    {
        $contentFileDirectory = DIR_FS_CATALOG . 'media/content/';
        $scriptPageFiles      = [];
        $ignoredScripts       = ['.', '..', 'index.html'];
        
        $iterator = new IteratorIterator(new DirectoryIterator($contentFileDirectory));
        
        $scriptPageFiles[''] = $this->languageTextManager->get_text('TEXT_NO_SELECTION', 'admin_general');
        foreach ($iterator as $scriptFile) {
            /** @var \DirectoryIterator $scriptFile */
            if (!in_array($scriptFile->getFilename(), $ignoredScripts)) {
                $scriptPageFiles[$scriptFile->getFilename()] = $scriptFile->getFilename();
            }
        }
        
        return $scriptPageFiles;
    }
    
    
    /**
     * Returns an array with allowed script files for content data.
     *
     * @return array List with allowed script files.
     */
    protected function _getProductsContentFiles()
    {
        $contentFileDirectory = DIR_FS_CATALOG . 'media/products/';
        $scriptPageFiles      = [];
        $ignoredScripts       = ['.', '..', 'index.html'];
        
        $iterator = new IteratorIterator(new DirectoryIterator($contentFileDirectory));
        
        $scriptPageFiles[''] = $this->languageTextManager->get_text('TEXT_SELECT', 'admin_general');
        foreach ($iterator as $scriptFile) {
            /** @var \DirectoryIterator $scriptFile */
            if (!in_array($scriptFile->getFilename(), $ignoredScripts)) {
                $scriptPageFiles[$scriptFile->getFilename()] = $scriptFile->getFilename();
            }
        }
        
        return $scriptPageFiles;
    }
    
    
    /**
     * Returns true if the "Responsive File Manager" is installed an false otherwise.
     *
     * @return bool
     */
    protected function _isFilemanagerAvailable()
    {
        $fileManagerConfiguration = MainFactory::create('ResponsiveFileManagerConfigurationStorage');
        
        return $fileManagerConfiguration->isInstalled()
               && $fileManagerConfiguration->get('use_in_content_manager_pages');
    }
    
    
    /**
     * Sets the expert mode, if the query parameter has been passed.
     */
    protected function _setExpertMode()
    {
        $this->isExpertMode = $this->_getQueryParameter('expert') !== null;
    }
    
    
    /**
     * Prepares $_POST data for the content_manager's 'group_ids' column.
     *
     * @return string
     */
    protected function _prepareContentManagerGroupCheckData()
    {
        $groupCheckData = $this->_getPostData('content_manager')['group_check'];
        
        return $groupCheckData ? implode(',',
                                         array_map(function ($element) {
                                             return 'c_' . $element . '_group';
                                         },
                                             $groupCheckData)) . ',' : '';
    }
    
    
    /**
     * Returns the content type of the given query result.
     *
     * @param array $queryResult Data sets of query for content_manager table.
     *
     * @return string Whether "content", "file" or "link".
     */
    protected function _getContentType(array $queryResult)
    {
        foreach ($queryResult as $result) {
            return $result['content_type'];
        }
        
        return 'content';
    }
}