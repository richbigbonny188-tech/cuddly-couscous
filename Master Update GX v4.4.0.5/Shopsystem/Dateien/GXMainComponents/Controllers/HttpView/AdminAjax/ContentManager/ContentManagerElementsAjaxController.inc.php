<?php

/* --------------------------------------------------------------
   ContentManagerElementsAjaxController.inc.php 2020-12-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class ContentManagerElementsAjaxController
 *
 * Ajax controller for the content manager elements
 *
 * @category   System
 * @package    AdminHttpViewControllers
 * @extends    AdminHttpViewController
 */
class ContentManagerElementsAjaxController extends AdminHttpViewController
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
     * Deletes an content manager element from the db
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
        // cleaning up content_manager_aliases table
        $this->db->query('DELETE FROM `content_manager_aliases` WHERE `content_group` NOT IN (SELECT `content_group` from `content_manager`)');
        
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
}