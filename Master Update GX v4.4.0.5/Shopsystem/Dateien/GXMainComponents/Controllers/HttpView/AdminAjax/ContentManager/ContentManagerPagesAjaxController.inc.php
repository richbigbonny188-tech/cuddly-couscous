<?php
/* --------------------------------------------------------------
   ContentManagerPagesAjaxController.inc.php 2020-10-20
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class ContentManagerPagesAjaxController
 *
 * Ajax controller for the content manager pages
 *
 * @category   System
 * @package    AdminHttpViewControllers
 * @extends    AdminHttpViewController
 */
class ContentManagerPagesAjaxController extends AdminHttpViewController
{
    /**
     * Database connection.
     *
     * @var CI_DB_query_builder
     */
    protected $db;
    
    
    /**
     * Init
     */
    public function init()
    {
        $this->db = StaticGXCoreLoader::getDatabaseQueryBuilder();
    }
    
    
    /**
     * Saves the sorting of content manager pages in the db
     *
     * @return bool|\JsonHttpControllerResponse
     * @throws \AuthenticationException
     */
    public function actionSavePagesSorting()
    {
        if (!$this->_isAdmin()) {
            throw new AuthenticationException('No admin privileges. Please contact the administrator.');
        }
        
        $pages           = $this->_getPostData('pages');
        $contentPosition = $this->_getPostData('position');
        
        if (!isset($pages) || !is_array($pages)) {
            return MainFactory::create('JsonHttpControllerResponse', ['success']);
        }
        
        foreach ($pages as $sortOrder => $contentGroupId) {
            $data = [
                'sort_order'       => (int)$sortOrder,
                'content_position' => $contentPosition,
                'file_flag'        => $this->_getFileFlagId($contentGroupId, $contentPosition)
            ];
            
            $this->db->set($data)->where('content_group', (int)$contentGroupId)->update('content_manager');
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['success']);
    }
    
    
    /**
     * Deletes an content manager page from the db
     *
     * @return bool|\JsonHttpControllerResponse
     * @throws \AuthenticationException
     */
    public function actionDelete()
    {
        if (!$this->_isAdmin()) {
            throw new AuthenticationException('No admin privileges. Please contact the administrator.');
        }
        
        $id = $this->_getPostData('id');
        
        if (!isset($id) || !is_numeric($id)) {
            return MainFactory::create('JsonHttpControllerResponse', ['Invalid ID']);
        }
        
        $this->db->where('content_group', (int)$id)->delete('content_manager');
        
        $urlRewriteStorage = MainFactory::create('UrlRewriteStorage',
                                                 new NonEmptyStringType('content'),
                                                 $this->db,
                                                 MainFactory::create('LanguageProvider', $this->db));
        $urlRewriteStorage->delete(new IdType((int)$id));
        
        /** @var \SliderWriteServiceInterface $sliderWriteService */
        $sliderWriteService = StaticGXCoreLoader::getService('SliderWrite');
        $sliderWriteService->deleteSliderAssignmentByContentId(new IdType((int)$id));
        
        return MainFactory::create('JsonHttpControllerResponse', ['success']);
    }
    
    
    /**
     * Check if the customer is the admin.
     *
     * @return bool Is the customer the admin?
     */
    protected function _isAdmin()
    {
        try {
            $this->validateCurrentAdminStatus();
            
            return true;
        } catch (LogicException $exception) {
            return false;
        }
    }
    
    
    /**
     * Get file flag ID by content group ID and content position.
     *
     * @param int    $contentGroupId
     * @param string $contentPosition
     *
     * @return int file flag ID
     */
    protected function _getFileFlagId($contentGroupId, $contentPosition)
    {
        $fileFlagIds = [
            'information'      => 0,
            'content'          => 1,
            'topmenu_corner'   => 2,
            'topmenu'          => 3,
            'extraboxes'       => 4,
            'withdrawal'       => 5
        ];
        
        $fileFlagIds['pages_additional'] = (int)$this->db->query('SELECT `file_flag` FROM `cm_file_flags` ORDER BY `file_flag` DESC LIMIT 1')->row_array(1)['file_flag'];
        
        // withdrawal 1 to 4
        if (in_array((int)$contentGroupId, [3889896, 3889897, 3889898, 3889899], true)) {
            return $fileFlagIds['withdrawal'];
        }
        
        switch ($contentPosition) {
            case 'pages_info_box':
                $fileFlagId = $fileFlagIds['information'];
                break;
            case 'pages_main':
                $fileFlagId = $fileFlagIds['topmenu'];
                break;
            case 'pages_secondary':
                $fileFlagId = $fileFlagIds['topmenu_corner'];
                break;
            case 'pages_info':
                $fileFlagId = $fileFlagIds['content'];
                break;
            case 'elements_start':
            case 'elements_header':
            case 'elements_footer':
            case 'elements_boxes':
            case 'elements_others':
                $fileFlagId = $fileFlagIds['extraboxes'];
                break;
            case 'pages_additional':
                $fileFlagId = $fileFlagIds['pages_additional'];
                break;
            default:
                $fileFlagId = $fileFlagIds['content'];
        }
        
        return $fileFlagId;
    }
}
