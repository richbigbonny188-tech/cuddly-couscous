<?php
/* --------------------------------------------------------------
   GambioUpdateControl.inc.php 2020-12-04
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

require_once(DIR_FS_CATALOG . 'gambio_updater/classes/GambioUpdateModel.inc.php');
require_once(DIR_FS_CATALOG . 'gambio_updater/classes/FileManagerInterface.inc.php');
require_once(DIR_FS_CATALOG . 'gambio_updater/classes/FTPManager.inc.php');
require_once(DIR_FS_CATALOG . 'gambio_updater/classes/SFTPManager.inc.php');
require_once(DIR_FS_CATALOG . 'system/core/caching/CacheControl.inc.php');
require_once(DIR_FS_CATALOG . 'system/classes/security/SecurityCheck.inc.php');
if (file_exists(DIR_FS_CATALOG . 'gambio_updater/classes/AutoUpdateChecker.inc.php')) {
    require_once(DIR_FS_CATALOG . 'gambio_updater/classes/AutoUpdateChecker.inc.php');
}
require_once(DIR_FS_CATALOG . 'gambio_updater/classes/GambioUpdateSerializer.inc.php');

class GambioUpdateControl
{
    public $current_db_version;
    public $gambio_update_array            = array();
    public $section_file_delete_info_array = array();

    protected $customer_id = 0;
    protected $db_host;
    protected $db_user;
    protected $db_password;
    protected $db_name;
    protected $db_persistent;
    protected $rerun_step;

    protected $deleteOperationsSuccess = 1;

	/**
	 * @var \GambioUpdateSerializer 
	 */
    protected $gambioUpdateSerializer;
	
	/**
	 * @var \DatabaseModel
	 */
    protected $db;


    /**
     * Creates a new GambioUpdateControl instance and loads and sorts all available updates
     *
     * @param string $p_db_host       The host for the DB connection
     * @param string $p_db_user       The user for the DB connection
     * @param string $p_db_password   The password for the DB connection
     * @param string $p_db_name       The selected DB name
     * @param bool   $p_db_persistent Persistent DB connection?
     */
    public function __construct($p_db_host = '',
                                $p_db_user = '',
                                $p_db_password = '',
                                $p_db_name = '',
                                $p_db_persistent = null)
    {
        $this->db_host            = $p_db_host;
        $this->db_user            = $p_db_user;
        $this->db_password        = $p_db_password;
        $this->db_name            = $p_db_name;
        $this->db_persistent      = $p_db_persistent;
	
	    $this->gambioUpdateSerializer = new GambioUpdateSerializer();
	    $this->db                     = new DatabaseModel($this->db_host, $this->db_user, $this->db_password,
	                                                      $this->db_name, $this->db_persistent);
        
        $this->current_db_version = $this->get_current_db_version();
        $this->rerun_step         = false;

        $updateArrayResult = $this->db->queryGmConfiguration('UPDATE_ARRAY');
        $update_array_from_database = $this->gambioUpdateSerializer->deserialize($this->db->getConfigurationValueOrFallback($updateArrayResult, '[]'));
	    
	    if(empty($update_array_from_database) === false)
	    {
            $this->gambio_update_array = $update_array_from_database;
		
            if(is_array($this->gambio_update_array))
            {
	            /**
	             * @var \GambioUpdateModel $update
	             */
	            foreach($this->gambio_update_array as $update)
	            {
		            $update->reset_mysqli($this->db->get_coo_mysqli());
	            }
            }
            else
            {
        $this->load_updates();
        $this->sort_updates();
	            $this->insert_update_array_into_gm_configuration_db();
            }
        } 
        else
        {
	        $this->load_updates();
	        $this->sort_updates();
	        $this->insert_update_array_into_gm_configuration_db();
        }
    }

    private function insert_update_array_into_gm_configuration_db()
    {
        if ($this->db->table_exists('gm_configuration')) {
            $this->db->query('ALTER TABLE `gm_configuration` MODIFY COLUMN `gm_value` mediumtext NOT NULL');
        }
    	
    	$this->save_gm_configuration('UPDATE_ARRAY', $this->gambioUpdateSerializer->serialize($this->gambio_update_array));
    }


    private function get_current_db_version()
    {
        $t_sql          = "SELECT * FROM `version_history` WHERE `type` IN ('master_update', 'service_pack') ORDER BY `installation_date` DESC, `history_id` DESC LIMIT 1";
        $t_version_data = $this->db->query($t_sql);

        if(count($t_version_data) > 0)
        {
            return $t_version_data[0]['version'];
        }
        else
        {
            return false;
        }
    }


    /**
     * Returns an array of versions for the "force shop version dropdown"
     *
     * @return array
     */
    public function get_versions()
    {
        if(file_exists(DIR_FS_CATALOG . 'gambio_updater/updates/versions.ini'))
        {
            $versions = parse_ini_file('updates/versions.ini');
        }
        else
        {
            $versions    = array();
            $update_dirs = glob(DIR_FS_CATALOG . 'gambio_updater/updates/v*', GLOB_ONLYDIR);

            foreach($update_dirs as $update_dir)
            {
                $version = substr(basename($update_dir), 1);

                if(version_compare($version, '2.1.3.0', '<'))
                {
                    continue;
                }

                $versions[$version] = 'v' . $version;
            }
        }
	
	    arsort($versions, SORT_NATURAL);
	    
	    return $versions;
    }


    /**
     * Reads all available updates from the 'update'-directory, that match the requirements
     *
     * @return bool Indicates if all updates have been successfully loaded
     */
    public function load_updates($custom_current_db_version = false)
    {
        $t_success    = true;
        $t_dir_handle = opendir(DIR_FS_CATALOG . 'gambio_updater/updates');
        while($t_update_path = readdir($t_dir_handle))
        {
            if(is_dir(DIR_FS_CATALOG . 'gambio_updater/updates/' . $t_update_path)
               && $t_update_path != '.'
               && $t_update_path != '..'
               && file_exists(DIR_FS_CATALOG . 'gambio_updater/updates/' . $t_update_path . '/configuration.ini')
            )
            {
                $coo_update = new GambioUpdateModel($t_update_path,
                                                    $this->db_host,
                                                    $this->db_user,
                                                    $this->db_password,
                                                    $this->db_name,
                                                    $this->db_persistent,
                                                    $this->customer_id,
                                                    $custom_current_db_version);
                if($this->current_db_version === false || is_null($this->current_db_version))
                {
                    $this->current_db_version = $coo_update->get_shop_db_version();
                }
                $t_matches_requirements = $coo_update->check_environment_requirements();
                if($t_matches_requirements)
                {
                    $this->gambio_update_array[] = $coo_update;
                }
                $t_success &= $t_matches_requirements;
            }
        }
        closedir($t_dir_handle);

        return $t_success;
    }


    public function sort_updates()
    {
        $this->sort_update_versions();
        $previous_update_list = array();
        while($previous_update_list != $this->gambio_update_array)
        {
            $previous_update_list = $this->gambio_update_array;
            $this->sort_out_old_updates();
            $this->sort_out_disconnected_updates();
            $this->sort_out_implicit_updates();
            $this->sort_out_installed_updates();
        }
    }


    public function insert_version_history_entry($p_db_version = false)
    {
        $t_db_version = $this->current_db_version;
        if($p_db_version !== false)
        {
            $t_db_version = $p_db_version;
        }

        $t_php_version = PHP_VERSION;

        $t_sql = "	INSERT INTO
						`version_history` (`version`, `name`, `type`, `revision`, `is_full_version`, `installation_date`, `php_version`, `mysql_version`, `installed`)
					VALUES
						('" . $this->db->real_escape_string($t_db_version) . "', 
						'" . $this->db->real_escape_string($t_db_version) . "', 
						'service_pack', 
						0, 
						1, 
						NOW(), 
						'" . $this->db->real_escape_string($t_php_version) . "', 
						VERSION(),
						0)";
        $this->db->query($t_sql);
    }


    public function get_chmod_array()
    {
        $t_chmod_array = SecurityCheck::getWrongPermittedUpdaterFiles();

        return $t_chmod_array;
    }


    public function get_move_array()
    {
        $t_move_array = array();
        foreach($this->gambio_update_array as $coo_update)
        {
            $coo_update->set_charset($coo_update->get_charset());
            $coo_update->load_move_array();
            $t_move_array = array_replace_recursive($t_move_array, $coo_update->get_move_array());
        }

        return $t_move_array;
    }


    protected function chmod_array_unique($p_chmod_array = array())
    {
        $t_chmod_array_flat = array();
        $t_chmod_array      = array();

        foreach($p_chmod_array as $t_chmod_data)
        {
            $t_chmod_array_flat[$t_chmod_data['PATH']] = $t_chmod_data['IS_DIR'];
        }

        foreach($t_chmod_array_flat as $t_path => $t_is_dir)
        {
            $t_chmod_array[] = array('PATH' => $t_path, 'IS_DIR' => $t_is_dir);
        }

        return $t_chmod_array;
    }


    protected function get_save_filelist_array($p_action, $p_ftp_sleeptime = 1000000)
    {
        for($i = 0; $i < 5; $i++)
        {
            switch($p_action)
            {
                case 'delete':
                    $t_ftp_action_array = $this->get_delete_list();
                    break;
                case 'move':
                    $t_ftp_action_array = $this->get_move_array();
                    break;
                case 'chmod':
                    $t_ftp_action_array = $this->get_chmod_array();
                    break;
            }

            if(empty($t_ftp_action_array))
            {
                break;
            }
            usleep($p_ftp_sleeptime);
        }

        if($p_action !== 'move')
        {
            sort($t_ftp_action_array);
        }

        return $t_ftp_action_array;
    }


    public function get_delete_form($p_second_try = false)
    {

        $t_delete_array = $this->get_save_filelist_array('delete');
	
	    $t_html = '';
	
	    /**
	     * Creates an HTML form showing wrong file or directory permissions
	     */
	    if(empty($t_delete_array) === false)
	    {
		    $t_html .= $this->get_ftp_form('delete', $t_delete_array, $p_second_try);
	    }
	    else
	    {
		    if(isset($_POST['dir']) && empty($_POST['dir']) == false)
		    {
			    $t_dir_confirmed = 'true';
		    }
		    else
		    {
			    $t_dir_confirmed = 'false';
		    }
		
		    $t_html .= '<div>' . TEXT_DELETE_FILES_OK . '</div>'
		               . '<br/><br/>'
		               . '<input type="hidden" name="protocol" value="' . str_replace('"', '&quot;', $_POST['protocol']) . '" />'
		               . '<input type="hidden" name="FTP_HOST" value="' . str_replace('"', '&quot;', $_POST['FTP_HOST']) . '" />'
		               . '<input type="hidden" name="FTP_USER" value="' . str_replace('"', '&quot;', $_POST['FTP_USER']) . '" />'
		               . '<input type="hidden" name="FTP_PASSWORD" value="' . str_replace('"', '&quot;', $_POST['FTP_PASSWORD']) . '" />'
		               . '<input type="hidden" name="FTP_PORT" value="' . str_replace('"', '&quot;', $_POST['FTP_PORT']) . '" />'
		               . '<input type="hidden" name="FTP_PASV" value="' . str_replace('"', '&quot;', $_POST['FTP_PASV']) . '" />'
		               . '<input type="hidden" name="dir" value="' . str_replace('"', '&quot;', $_POST['dir']) . '" />'
		               . '<input type="hidden" name="dir_confirmed" value="' . $t_dir_confirmed . '" />'
		               . '<input type="submit" name="go" value="' . BUTTON_CONTINUE . '" class="btn btn-primary btn-lg" />';
	    }
	
	    return $t_html;
    }
	
	public function get_move_form($p_second_try = false, $p_ftp_sleeptime = 1000000)
	{
		$t_move_array = $this->get_save_filelist_array('move');
		
		$t_html = '';
		
		/**
		 * Creates an HTML form
		 */
		if(empty($t_move_array) === false)
		{
			$t_html .= $this->get_ftp_form('move', $t_move_array, $p_second_try);
		}
		else
		{
			if(isset($_POST['dir']) && empty($_POST['dir']) == false)
			{
				$t_dir_confirmed = 'true';
			}
			else
			{
				$t_dir_confirmed = 'false';
			}
			
			$t_html .= '<div>' . TEXT_MOVE_OK . '</div>'
			           . '<br/><br/>'
			           . '<input type="hidden" name="protocol" value="' . str_replace('"', '&quot;', $_POST['protocol']) . '" />'
			           . '<input type="hidden" name="FTP_HOST" value="' . str_replace('"', '&quot;', $_POST['FTP_HOST']) . '" />'
			           . '<input type="hidden" name="FTP_USER" value="' . str_replace('"', '&quot;', $_POST['FTP_USER']) . '" />'
			           . '<input type="hidden" name="FTP_PASSWORD" value="' . str_replace('"', '&quot;', $_POST['FTP_PASSWORD']) . '" />'
			           . '<input type="hidden" name="FTP_PORT" value="' . str_replace('"', '&quot;', $_POST['FTP_PORT']) . '" />'
			           . '<input type="hidden" name="FTP_PASV" value="' . str_replace('"', '&quot;', $_POST['FTP_PASV']) . '" />'
			           . '<input type="hidden" name="dir" value="' . str_replace('"', '&quot;', $_POST['dir']) . '" />'
			           . '<input type="hidden" name="dir_confirmed" value="' . $t_dir_confirmed . '" />'
			           . '<input type="submit" name="go" value="' . BUTTON_CONTINUE . '" class="btn btn-primary btn-lg" />';
		}
		
		return $t_html;
	}
	
	public function get_ftp_html($p_coo_ftp_manager, $p_dir)
	{
		$t_html = '';
		
		if(is_object($p_coo_ftp_manager))
		{
			if($p_coo_ftp_manager->error != '')
			{
				$t_html .= '<div class="error">' . $p_coo_ftp_manager->error . '</div>';
			}
			else
			{
                $t_html .= $this->get_folders_html($p_coo_ftp_manager, $p_dir);
            }
        }

        return $t_html;
    }


    public function get_ftp_form($p_content, $p_files_array, $p_second_try)
    {
        $t_html = '';

        switch($p_content)
        {
            case 'move':
                $t_files_array = array();

                $moveFilesSuccess = $this->_moveFiles($p_files_array);

                foreach($p_files_array as $t_data_array)
                {
                    if(dirname($t_data_array['old']) !== dirname($t_data_array['new']))
                    {
                        $t_files_to_move_array[] = $t_data_array['old'] . ' => ' . $t_data_array['new'];
                    }
                    else
                    {
                        $t_files_to_rename_array[] = $t_data_array['old'] . ' => ' . $t_data_array['new'];
                    }
                }

                if($moveFilesSuccess)
                {
                    $t_html .= '<div id="move_errors_report">';

                    if(empty($t_files_to_move_array) === false)
                    {
                        $t_html .= '<p><h2>' . HEADING_MOVED . '</h2></p>
								<div class="notice_field">' . implode("<br />\n", $t_files_to_move_array) . '</div>';
                    }

                    if(empty($t_files_to_rename_array) === false)
                    {
                        if(empty($t_files_to_move_array) === false)
                        {
                            $t_html .= '<br />';
                        }

                        $t_html .= '<p><h2>' . HEADING_RENAMED . '</h2></p>
								<div class="notice_field">' . implode("<br />\n", $t_files_to_rename_array) . '</div>';
                    }

                    $t_html .= '<br /><br />
								<a class="button_reload btn btn-primary btn-lg" href="#">' . BUTTON_CONTINUE . '</a>
							</div>';

                    return $t_html;
                }

                $t_html .= '<div id="move_errors_report">';

                if(empty($t_files_to_move_array) === false)
                {
                    $t_html .= '<p><h2>' . HEADING_MOVE . '</h2></p>
								<div class="error_field">' . implode("<br />\n", $t_files_to_move_array) . '</div>';
                }

                if(empty($t_files_to_rename_array) === false)
                {
                    if(empty($t_files_to_move_array) === false)
                    {
                        $t_html .= '<br />';
                    }

                    $t_html .= '<p><h2>' . HEADING_RENAME . '</h2></p>
								<div class="error_field">' . implode("<br />\n", $t_files_to_rename_array) . '</div>';
                }

                $t_html .= '<p>' . TEXT_MOVE . '</p>
								<a class="button_reload btn btn-default btn-lg" href="#">' . BUTTON_CHECK_MOVE . '</a>
								<br /><br />';

                break;
            case 'delete':

                $t_files_array = $p_files_array;
                foreach($t_files_array as $t_key => $t_path)
                {
                    $t_path = trim($t_path);
                    if(strlen($t_path) > 2 && substr($t_path, -2) == '/*')
                    {
                        $t_files_array[$t_key] = substr($t_path, 0, -2);
                    }
                }

                // Create Backup START *********************************************
                require_once 'classes/zip_creator/ZipCreator.inc.php';

                $fileListToZip = ZipCreator::prepareFileListFromShop($t_files_array);

                $zipDirFromShopRoot = 'export/';
                $zipFileName        = null;
                $zipCreator         = @new ZipCreator(DIR_FS_CATALOG, $zipDirFromShopRoot, $zipFileName);
                @$zipCreator->createZip($fileListToZip, DIR_FS_CATALOG);

                $t_files_json = urlencode(json_encode($t_files_array));

                $t_html_deletepart = '<div id="delete_errors_report">
							<p><h2>' . HEADING_NEED_TO_DELETE . '</h2></p>
							<div class="error_field">' . implode("<br />\n", $t_files_array) . '</div>
							<p>' . TEXT_DELETE_FILES . '</p>
							<a class="button_reload btn btn-default btn-lg" data-filelist_to_delete="' . $t_files_json
                                     . '" href="#">' . BUTTON_DOWNLOAD_FILELIST_TO_DELETE . '</a>
							<a class="button_reload btn btn-default btn-lg" href="#">' . BUTTON_CHECK_DELETE_FILES . '</a>
							###downloadbutton###
							<br /><br />
							<br /><br />';

                $t_buttonTemplate = '';

                $zipFileShowName = @$zipCreator->getZipFileName();

                $zipFilePath = DIR_FS_CATALOG . $zipDirFromShopRoot . $zipFileShowName;

                if($zipFileShowName && file_exists($zipFilePath))
                {
                    $t_buttonTemplate = '<a class="button_create_backup btn btn-default btn-lg" href="../export/'
                                        . $zipFileShowName . '" target="_blank">' . BUTTON_CREATE_BACKUP . '</a>';

                    $this->_deleteFiles($t_files_array);

                    if($this->deleteOperationsSuccess)
                    {
                        $t_html_deletepart = '<div id="delete_errors_report">
							<p><h2>' . HEADING_DELETED_FILES . '</h2></p>
							<div class="error_field">' . implode("<br />\n", $t_files_array) . '</div>
							<p>' . TEXT_DELETED_FILES . '</p>
							<a class="button_reload btn btn-default btn-lg" data-filelist_to_delete="' . $t_files_json
                                             . '" href="#">' . BUTTON_DOWNLOAD_FILELIST_TO_DELETE . '</a>
							###downloadbutton###
							<br /><br /><br />
							<a class="button_reload btn btn-primary btn-lg" href="#">' . BUTTON_CONTINUE . '</a>
						</div>';

                        $t_html_deletepart = str_replace('###downloadbutton###', $t_buttonTemplate, $t_html_deletepart);
                        $t_html .= $t_html_deletepart;

                        return $t_html;
                    }
                }

                $t_html_deletepart = str_replace('###downloadbutton###', $t_buttonTemplate, $t_html_deletepart);
                $t_html .= $t_html_deletepart;

                // Create Backup END *****************************************************
                break;
            case 'chmod':
                $t_files_array = array();

                $chmodSuccess = $this->_chmod($p_files_array);

                foreach($p_files_array as &$t_data_array)
                {
                    $t_data_array['PATH'] = str_replace(DIR_FS_CATALOG, '', $t_data_array['PATH']);
                    $t_files_array[]      = $t_data_array['PATH'];
                }

                if($chmodSuccess)
                {
                    $t_html .= '<div id="chmod_errors_report">
								<p><h2>' . HEADING_PERMISSIONS_SET . '</h2></p>
								<div class="error_field">' . implode("<br />\n", $t_files_array) . '</div>
								<p>' . TEXT_PERMISSIONS_SET . '</p>
								<a class="button_reload btn btn-primary btn-lg" href="#">' . BUTTON_CONTINUE . '</a>
							</div>';

                    return $t_html;
                }

                $t_html .= '<div id="chmod_errors_report">
								<p><h2>' . HEADING_WRONG_PERMISSIONS . '</h2></p>
								<div class="error_field">' . implode("<br />\n", $t_files_array) . '</div>
								<p>' . TEXT_SET_PERMISSIONS . '</p>
								<a class="button_reload btn btn-default btn-lg" href="#">' . BUTTON_CHECK_PERMISSIONS . '</a>
								<br /><br />
								<br /><br />';

                break;
        }

        if(isset($_POST['FTP_PASV']) == false || empty($_POST['FTP_PASV']) || $_POST['FTP_PASV'] == true)
        {
            $t_is_passive_html = ' checked="checked"';
        }
        else
        {
            $t_is_passive_html = '';
        }

		$t_ftp_checked = ' checked';
		$t_sftp_checked = '';
		if(isset($_POST['protocol']) && $_POST['protocol'] === 'sftp')
		{
			$t_ftp_checked = '';
			$t_sftp_checked = ' checked';
		}
		
		$t_html .= '<fieldset class="block_head">
                        <legend><i class="fa fa-upload"></i> ' . HEADING_FTP_DATA . '</legend>
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <input id="ftp" type="radio" name="protocol" value="ftp"' . $t_ftp_checked . '>
								<label for="ftp" style="margin-right: 30px;">' . LABEL_FTP . '</label>
							
                                    <input id="sftp" type="radio" name="protocol" value="sftp"' . $t_sftp_checked . '>
								<label for="sftp">' . LABEL_SFTP . '</label>
                                </div>
                                
                                <div class="form-group">
                                    <input type="text" 
                                           class="input_field form-control input-lg"
                                           placeholder="' . LABEL_FTP_SERVER . '"
                                           name="FTP_HOST" 
                                           size="35" 
                                           value="' . str_replace('"', '&quot;', $_POST['FTP_HOST']) . '" />
                                </div>
                                
                                <div class="form-group">
                                    <input type="text" 
                                           class="input_field form-control input-lg"
                                           placeholder="' . LABEL_FTP_USER . '"
                                           name="FTP_USER" 
                                           size="35" 
                                           value="' . str_replace('"', '&quot;', $_POST['FTP_USER']) . '" />
                                </div>
                                
                                <div class="form-group">
                                    <input type="password" 
                                           class="input_field form-control input-lg"
                                           placeholder="' . LABEL_FTP_PASSWORD . '"
                                           name="FTP_PASSWORD" 
                                           size="35" 
                                           value="' . str_replace('"', '&quot;', $_POST['FTP_PASSWORD']) . '" />
                                </div>
                                
                                <div class="form-group ftp-port">
                                    <input type="text" 
                                           class="input_field form-control input-lg"
                                           placeholder="' . LABEL_FTP_PORT . '"
                                           name="FTP_PORT" 
                                           size="35" 
                                           value="' . str_replace('"', '&quot;', (!empty($_POST['FTP_PORT']) ? $_POST['FTP_PORT'] : 22)) . '" />
                                </div>
                                
                                <div class="form-group ftp-pasv">
                                    <label for="pasv">' . LABEL_FTP_PASV . '</label>
                                    <input type="checkbox" 
                                           id="pasv" 
                                           name="FTP_PASV" 
                                           value="true" ' . $t_is_passive_html . ' />
                                </div>
                            </div>
                        </div>
                    </fieldset>';
        if(isset($_POST['FTP_HOST']) == false
           || ($_POST['FTP_HOST'] === '' && $_POST['FTP_USER'] === ''
               && $_POST['FTP_PASSWORD'] === '')
        )
        {
            $t_html .= '<br /><input type="hidden" name="dir" value="/" />
						<input type="submit" name="go" value="' . BUTTON_CONNECT
                       . '" class="btn btn-primary btn-lg" /><br/><br/>';
        }
        else
        {
            $t_html .= '<br />
						<fieldset>
							<legend>' . HEADING_REMOTE_CONSOLE . '</legend>
							<div id="ftp_content">';
			
			if($_POST['protocol'] === 'ftp')
			{
				$coo_ftp_manager = FTPManager::get_instance(true, $_POST['FTP_HOST'], $_POST['FTP_USER'], $_POST['FTP_PASSWORD'], $_POST['FTP_PASV']);
			}
			else
			{
				$coo_ftp_manager = SFTPManager::get_instance(true, $_POST['FTP_HOST'], $_POST['FTP_USER'], $_POST['FTP_PASSWORD'], $_POST['FTP_PORT']);
			}
			
			if( (is_object($coo_ftp_manager)) && $coo_ftp_manager->error != '')
			{
				$t_html .= $coo_ftp_manager->error;
				
				$t_html .= '</div>
						</fieldset>
						<br />
						<input type="submit" name="reconnect" value="' . BUTTON_CONNECT_NEW . '" class="btn btn-default btn-lg" />
					</div>';

                return $t_html;
            }

            if(isset($_POST['dir']) && $_POST['dir'] !== '/')
            {
                $t_dir = $_POST['dir'];
            }
            else
            {
                $t_dir = $coo_ftp_manager->find_shop_dir('/');
            }

            if(isset($_POST['move'])
               && empty($_POST['move']) === false
               && is_object($coo_ftp_manager)
               && $coo_ftp_manager->is_shop($t_dir)
               && $p_second_try === false
            )
            {
                $coo_ftp_manager->move($t_dir, $p_files_array);

                return $this->get_move_form(true);
            }

            if(isset($_POST['delete_files'])
               && empty($_POST['delete_files']) === false
               && is_object($coo_ftp_manager)
               && $coo_ftp_manager->is_shop($t_dir)
               && $p_second_try === false
            )
            {
                $coo_ftp_manager->delete_files($t_dir, $p_files_array);

                return $this->get_delete_form(true);
            }

            if(isset($_POST['chmod_777'])
               && empty($_POST['chmod_777']) === false
               && is_object($coo_ftp_manager)
               && $coo_ftp_manager->is_shop($t_dir)
               && $p_second_try === false
            )
            {
                $coo_ftp_manager->chmod_777($t_dir, $p_files_array);

                return $this->get_chmod_form(true);
            }

            $t_html .= $this->get_ftp_html($coo_ftp_manager, $t_dir);

            $t_html .= '</div></fieldset>';

            if($coo_ftp_manager->is_shop($t_dir))
            {
                $t_is_correct_shop = $this->check_correct_shop($t_dir);
        
                if($t_is_correct_shop)
                {
                    $t_html .= '<input type="hidden" name="dir" value="' . $t_dir . '" />';
            
                    switch($p_content)
                    {
                        case 'move':
                            $t_html .= '<input type="submit" name="move" value="' . BUTTON_MOVE
                                       . '" class="btn btn-primary btn-lg" />';
                    
                            break;
                        case 'delete':
                            $t_html .= '<input type="submit" name="delete_files" value="' . BUTTON_DELETE_FILES
                                       . '" class="btn btn-danger btn-lg" />';
                    
                            break;
                        case 'chmod':
                            $t_html .= '<input type="submit" name="chmod_777" value="' . BUTTON_SET_PERMISSIONS
                                       . '" class="btn btn-primary btn-lg" />';
                    
                            break;
                    }
                }
            }

			if($coo_ftp_manager->error != '' || (is_object($coo_ftp_manager)))
			{
				$t_html .= '&nbsp; <input type="submit" name="reconnect" value="' . BUTTON_CONNECT_NEW . '" class="btn btn-default btn-lg" />';
			}
		}

        if($p_content === 'chmod')
        {
            $t_html .= '<div class="alert alert-warning">
							' . TEXT_SKIP . '
						</div>
						<br>
						<a class="button_skip btn btn-default btn-lg" href="#">' . BUTTON_SKIP . '</a>';
        }

        $t_html .= '</div>';

        return $t_html;
    }


    public function get_chmod_form($p_second_try = false, $p_ftp_sleeptime = 1000000)
    {

        $t_chmod_array = $this->get_save_filelist_array('chmod');

        $t_html = '';

        /**
         * Creates an HTML form showing wrong file or directory permissions
         */
        if(empty($t_chmod_array) == false)
        {
            $t_html .= $this->get_ftp_form('chmod', $t_chmod_array, $p_second_try);
        }
        else
        {
            $t_html .= '<div>' . TEXT_PERMISSIONS_OK . '</div>' . '<br/><br/>'
                       . '<input type="submit" name="go" value="' . BUTTON_CONTINUE
                       . '" class="btn btn-default btn-lg" />';
        }

        return $t_html;
    }


    public function get_folders_html($p_coo_ftp_manager, $p_dir)
    {
        $t_html = TEXT_CURRENT_DIR . $p_dir . '<br /><br />';

        if(strrpos($p_dir, '/') !== false && $p_dir != '/')
        {
            if(strrpos($p_dir, '/') === 0)
            {
                $t_absolute_dir = '/';
            }
            else
            {
                $t_absolute_dir = substr($p_dir, 0, strrpos($p_dir, '/'));
            }

            $t_html .= '<div class="folder" title="' . LABEL_DIR_UP . '">
							<i class="fa fa-folder-open"></i> ..		
							<span class="absolute_dir">' . $t_absolute_dir . '</span>
						</div>';
        }

        $t_list_array = $p_coo_ftp_manager->get_directories($p_dir);

        for($i = 0; $i < count($t_list_array); $i++)
        {
            $t_html .= '<div class="folder">
							<i class="fa fa-folder-open"></i>' . basename($t_list_array[$i]) . ' 
							<span class="absolute_dir">' . $t_list_array[$i] . '</span>
						</div>';
        }

        return $t_html;
    }


    /**
     * Returns an array of all update forms
     *
     * @return array An array of all update forms
     */
    public function get_update_forms()
    {
        $t_forms_array     = array();
        $t_conflicts_array = array();
        $t_html            = '';

        foreach($this->gambio_update_array as $coo_update)
        {
            $coo_update->set_charset($coo_update->get_charset());
            $t_forms_array[]   = $coo_update->get_update_form();
            $t_conflicts_array = array_merge_recursive($t_conflicts_array, $coo_update->get_section_conflicts());
        }

        /**
         * Creates an HTML form to ask the user which text changes should be applied
         */
        if(!empty($t_conflicts_array))
        {
            $t_html .= '<fieldset id="conflict_fieldset">
                            <legend>' . TEXTCONFLICTS_LABEL . '</legend>
                            <p>' . TEXTCONFLICTS_TEXT . '</p>
            <table id="section_conflicts" class="table table-striped">';

            foreach($t_conflicts_array as $t_language_name => $t_section_data)
            {
                $t_html .= '<thead>
								<tr class="section_conflicts_language_row">
									<th colspan="4">
										<b>' . ucfirst($t_language_name) . '</b>
									</th>
								</tr>
							</thead>';
                foreach($t_section_data as $t_section_name => $t_phrase_data)
                {
                    $t_section_path = $t_section_name;
                    if(strpos(trim($t_section_name), 'lang__') === 0)
                    {
                        $t_section_path = str_replace('__', '/', $t_section_path);
                        $t_section_path = str_replace('___', '.', $t_section_path);
                    }
                    else
                    {
                        $t_section_path = 'lang/' . $t_language_name . '/sections/' . $t_section_name . '.lang.inc.php';
                    }

                    $t_alt_counter = 0;
                    $t_html .= '<tbody id="' . $t_section_path . '" class="section_body">
									<tr class="section_conflicts_section_row">
										<td colspan="4">
											<i>' . $t_section_path . '</i>
											<input type="hidden" name="keep_list[' . $t_language_name . '_'
                               . $t_section_name . ']" value=""/>
										</td>
									</tr>
									<tr>
										<th colspan="2" class="section_conflicts_left_column conflict_header">'
                               . TEXTCONFLICTS_OLD . '</th>
										<th colspan="2" class="conflict_header">' . TEXTCONFLICTS_NEW . '</th>
									</tr>';
                    foreach($t_phrase_data as $t_phrase_name => $t_phrase_text_data)
                    {
                        $t_alt_class = '';
                        if($t_alt_counter % 2 == 1)
                        {
                            $t_alt_class = '_alt';
                        }
                        if(is_array($t_phrase_text_data['old']))
                        {
                            $t_phrase_text_data['old'] = end($t_phrase_text_data['old']);
                        }
                        if(is_array($t_phrase_text_data['new']))
                        {
                            $t_phrase_text_data['new'] = end($t_phrase_text_data['new']);
                        }
                        if(is_array($t_phrase_text_data['from_file']))
                        {
                            $t_phrase_text_data['from_file'] = end($t_phrase_text_data['from_file']);
                        }

                        $t_begin_file_span = '';
                        $t_end_file_span   = '';
                        $t_bound_radio     = '';
                        if($t_phrase_text_data['from_file'] == 1)
                        {
                            $t_begin_file_span = '<span class="from_file">';
                            $t_end_file_span   = '</span>';
                            $t_bound_radio     = 'bound_radio ';
                        }

                        $t_html .= '<tr class="section_conflicts_phrase_row' . $t_alt_class . '">
										<td class="section_conflict section_conflict_radio">
											<input id="' . $t_language_name . '_' . $t_section_name . '_'
                                   . $t_phrase_name . '_old" class="' . $t_bound_radio . 'section_' . $t_language_name
                                   . '_' . $t_section_name . '" type="radio" name="section_phrase[' . $t_language_name
                                   . '][' . $t_section_name . '][' . $t_phrase_name . '][refuse]" value="1" /> 
										</td>
										<td class="section_conflicts_left_column section_conflict">
											<label for="' . $t_language_name . '_' . $t_section_name . '_'
                                   . $t_phrase_name . '_old">' . $t_begin_file_span . '&quot;'
                                   . $this->htmlentities($t_phrase_text_data['old']) . '&quot;' . $t_end_file_span . '</label>
										</td>
										<td class="section_conflict section_conflict_radio">
											<input id="' . $t_language_name . '_' . $t_section_name . '_'
                                   . $t_phrase_name . '_new" class="' . $t_bound_radio . 'section_' . $t_language_name
                                   . '_' . $t_section_name . '" type="radio" name="section_phrase[' . $t_language_name
                                   . '][' . $t_section_name . '][' . $t_phrase_name . '][refuse]" value="0" checked="checked" /> 
										</td>
										<td class="section_conflict">
											<label for="' . $t_language_name . '_' . $t_section_name . '_'
                                   . $t_phrase_name . '_new">' . $t_begin_file_span . '&quot;'
                                   . $this->htmlentities($t_phrase_text_data['new']) . '&quot;' . $t_end_file_span . '</label>
											<input type="hidden" name="section_phrase[' . $t_language_name . ']['
                                   . $t_section_name . '][' . $t_phrase_name . '][from_file]" value="'
                                   . $t_phrase_text_data['from_file'] . '" />
										</td>
									</tr>';
                        $t_alt_counter++;
                    }
                }
                $t_html .= ' </tbody>';
            }

            $t_html .= '</table></fieldset>';
        }
        elseif(empty($t_html))
        {
            $t_empty = true;
            foreach($t_forms_array as $t_content)
            {
                if($t_content != '')
                {
                    $t_empty = false;
                    break;
                }
            }

            if($t_empty)
            {
                $t_html .= '<p>' . TEXT_NO_CONFIGURATION . '</p>';
            }
        }

        $t_forms_array[] = $t_html;

        return $t_forms_array;
    }


    protected function htmlentities($p_string)
    {
        $t_string = $p_string;
        if(preg_match('/(?:[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})+/xs',
                      $t_string) === 0
        )
        {
            return htmlentities($t_string, ENT_COMPAT, 'ISO-8859-15');
        }

        return htmlentities($t_string, ENT_COMPAT, 'UTF-8');
    }


    /**
     * Gathers all delete lists of all updates and combines intersections
     *
     * @return An array of all files that need to be deleted
     */
    public function get_delete_list()
    {
        clearstatcache();

        $t_delete_list = array();

        foreach($this->gambio_update_array as $coo_update)
        {
            $t_delete_list = array_merge($t_delete_list, $coo_update->get_delete_list());
        }

        if(file_exists(DIR_FS_CATALOG . 'cache/additional_delete_list.pdc'))
        {
            $t_additional_delete_list = unserialize(file_get_contents(DIR_FS_CATALOG
                                                                      . 'cache/additional_delete_list.pdc'));
            foreach($t_additional_delete_list as $t_delete_file)
            {
                $t_delete_list[] = $t_delete_file;
            }
        }

        $t_delete_list = $this->filter_delete_list(array_unique($t_delete_list));
        rsort($t_delete_list);

        return $t_delete_list;
    }
	
	
	/**
	 * Removes entries from a delete list and returns the remaining delete list
	 * 
	 * @param array $deleteList
	 *
	 * @return array The remaing delete list
	 */
	public function filter_delete_list(array $deleteList)
	{
		foreach($deleteList as $key => $filePath)
		{
			$filePath = trim($filePath);
			
			if($filePath === '')
			{
				unset($deleteList[$key]);
				continue;
			}
			
			if(strlen($filePath) > 2 && substr($filePath, -2) == '/*')
			{
				$filePath = substr($filePath, 0, -2);
			}
			elseif(is_dir(DIR_FS_CATALOG . $filePath) && glob(DIR_FS_CATALOG . $filePath . '/*') !== false
			       && $this->dir_has_files_in_list($deleteList, $filePath) == false
			)
			{
				unset($deleteList[$key]);
			}
			
			if(!file_exists(DIR_FS_CATALOG . $filePath))
			{
				unset($deleteList[$key]);
			}
			else // Windows cannot differentiate between lower- und uppercase -> double check
			{
				$realPath = realpath(DIR_FS_CATALOG . $filePath);
				$realPath = str_replace('\\', '/', $realPath);
				
				if(strpos($realPath, $filePath) === false)
				{
					unset($deleteList[$key]);
				}
			}
		}
		
		return $deleteList;
	}


    protected function dir_has_files_in_list(array $p_list, $p_dir)
    {
        foreach($p_list as $t_entry)
        {
            if(strpos(trim($t_entry), $p_dir . '/') !== false && $t_entry != $p_dir)
            {
                return true;
            }
        }

        return false;
    }


    /**
     * Executes all updates from gambio_update_array
     *
     * @param string $p_current_update_name         Name of update to install
     * @param array  $p_refusion_array              Refused phrases by the user
     * @param bool   $p_execute_independent_queries Indicates if independent updates should be executed
     * @param bool   $p_execute_dependent_queries   Indicates if dependent updates should be executed
     * @param bool   $p_update_css                  Indicates if CSS should be updated
     * @param bool   $p_update_sections             Indicates if sections should be updated
     *
     * @return bool Indicates if all updates were executed successfully
     */
    public function update($p_current_update_name,
                           $p_refusion_array = array(),
                           $p_execute_dependent_queries = true,
                           $p_execute_independent_queries = true,
                           $p_update_css = true,
                           $p_update_sections = true,
                           $p_update_version_history = true)
    {
        $t_success = true;

        foreach($this->gambio_update_array as $coo_update)
        {
            if($coo_update->get_update_name() == $p_current_update_name)
            {
                $coo_update->set_charset($coo_update->get_charset());
                if($p_execute_dependent_queries)
                {
                    $t_success &= $coo_update->update_dependent_data();
                }
                if($p_execute_independent_queries)
                {
                    $t_success &= $coo_update->update_independent_data();
                }
                if($p_update_sections)
                {
                    $check = $coo_update->query('SHOW TABLES LIKE "language_sections"', true);
                    if($check->num_rows === 1)
                    {
                        $t_section_file_delete_info_array = $coo_update->update_sections($p_refusion_array);
                        if($t_section_file_delete_info_array === false)
                        {
                            $t_success = false;
                        }
                        else
                        {
                            $this->section_file_delete_info_array = $t_section_file_delete_info_array;
                        }
                    }
                }
                if($p_update_css)
                {
                    $t_success &= $coo_update->update_css();
                }
                if($p_update_version_history)
                {
                    $t_success &= $coo_update->update_version_history();
                }

                $this->rerun_step = $coo_update->get_rerun_step();
                break;
            }
        }

        return $t_success;
    }


    /**
     * Sorts all GambioUpdate instances in the corect installation order
     */
    protected function sort_update_versions()
    {
        $t_sorted_updates = array();
        foreach($this->gambio_update_array as $coo_update)
        {
            if(empty($t_sorted_updates))
            {
                $t_sorted_updates[] = $coo_update;
            }
            else
            {
                $t_actual_sort_value = $coo_update->convert_version($coo_update->get_version_sort_value());
                $t_previous_size     = count($t_sorted_updates);
                for($i = 0; $i < count($t_sorted_updates); $i++)
                {
                    $t_ref_sort_value = $t_sorted_updates[$i]->convert_version($t_sorted_updates[$i]->get_version_sort_value());
                    if(version_compare($t_actual_sort_value, $t_ref_sort_value, '<'))
                    {
                        $t_sorted_updates = array_merge(array_slice($t_sorted_updates, 0, $i), array($coo_update),
                                                        array_slice($t_sorted_updates, $i));
                        break;
                    }
                }

                if($t_previous_size == count($t_sorted_updates))
                {
                    $t_sorted_updates[] = $coo_update;
                }
            }
        }
        $this->gambio_update_array = $t_sorted_updates;
    }


    /**
     * Sorts out updates that are lower than the actual version
     */
    protected function sort_out_old_updates()
    {
        $t_sorted_updates = array();
        foreach($this->gambio_update_array as $coo_update)
        {
            if(!$coo_update->is_lower_than_installed())
            {
                $t_sorted_updates[] = $coo_update;
            }
        }
        $this->gambio_update_array = $t_sorted_updates;
    }


    /**
     * Sorts out updates that can't be installed because none of the required shopversions can be reached through any
     * other updates
     */
    protected function sort_out_disconnected_updates()
    {
        $t_sorted_updates = array();
        if(count($this->gambio_update_array) > 0 && $this->gambio_update_array[0]->is_appliable())
        {
            $t_sorted_updates[] = $this->gambio_update_array[0];
            for($i = 0; $i < count($this->gambio_update_array) - 1; $i++)
            {
                if($this->gambio_update_array[$i + 1]->is_compatible_to($this->get_predicted_shop_version($i)))
                {
                    $t_sorted_updates[] = $this->gambio_update_array[$i + 1];
                }
            }
        }
        elseif(count($this->gambio_update_array) > 0)
        {
            array_shift($this->gambio_update_array);
            $this->sort_out_disconnected_updates();

            return;
        }
        $this->gambio_update_array = $t_sorted_updates;
    }


    protected function get_predicted_shop_version($p_index)
    {
        if(!isset($this->gambio_update_array[$p_index]))
        {
            return $this->current_db_version;
        }
        if($this->gambio_update_array[$p_index]->get_update_type() == 'update')
        {
            return $this->get_predicted_shop_version($p_index - 1);
        }

        return $this->gambio_update_array[$p_index]->get_update_version();
    }


    /**
     * Sorts out updates that are included within another update
     */
    protected function sort_out_implicit_updates()
    {
        $t_sorted_updates = array();
        foreach($this->gambio_update_array as $coo_update)
        {
            $t_update_is_implicit = false;
            foreach($this->gambio_update_array as $coo_implying_update)
            {
                if($coo_implying_update->implies_update($coo_update->get_update_key()))
                {
                    $t_update_is_implicit = true;
                    break;
                }
            }
            if(!$t_update_is_implicit)
            {
                $t_sorted_updates[] = $coo_update;
            }
        }
        $this->gambio_update_array = $t_sorted_updates;
    }


    protected function sort_out_installed_updates()
    {
        $t_sorted_updates = array();
        foreach($this->gambio_update_array as $coo_update)
        {
            if($coo_update->get_update_type() == 'update')
            {
                $t_query  = 'SELECT
								`history_id`
							FROM
								`version_history`
							WHERE
								`name` = "' . $coo_update->get_name() . '"
								AND `version` >= "' . $coo_update->get_update_version() . '"';
                $t_result = $this->db->query($t_query);
                if(empty($t_result))
                {
                    $t_sorted_updates[] = $coo_update;
                }
            }
            else
            {
                $t_sorted_updates[] = $coo_update;
            }
        }
        $this->gambio_update_array = $t_sorted_updates;
    }


    /**
     * Returns SQL-errors-array of all updates
     * @return array
     */
    public function get_sql_errors_array()
    {
        $t_sql_errors_array = array();
        foreach($this->gambio_update_array AS $coo_update)
        {
            $t_sql_errors_array = array_merge($t_sql_errors_array, $coo_update->get_sql_errors());
        }

        return $t_sql_errors_array;
    }


    public function login($p_email, $p_password, $p_check_latin1 = true)
    {
        $c_email    = $this->db->real_escape_string(trim(stripslashes($p_email)));
        $c_password = md5(trim(stripslashes($p_password)));

        $t_sql    = "SELECT `customers_id`, `customers_password` FROM `customers` 
					WHERE 
						`customers_email_address` = '" . $c_email . "' AND 
						`customers_status` = 0";
        $t_result = $this->db->query($t_sql, true);
	
	    if($t_result->num_rows === 1)
	    {
		    $t_result_array = $t_result->fetch_assoc();
		
		    if(function_exists('password_verify'))
		    {
			    $success = password_verify($p_password, $t_result_array['customers_password'])
			               || $c_password === $t_result_array['customers_password'];
		    }
		    else
		    {
			    $success = $c_password === $t_result_array['customers_password'];
		    }
		
		    if($success)
		    {
			    $this->customer_id = (int)$t_result_array['customers_id'];
			
			    foreach($this->gambio_update_array as $coo_update)
			    {
				    $coo_update->set_customer_id($this->customer_id);
			    }
			
			    return true;
		    }
	    }
        
        if($p_check_latin1 && $this->login($p_email, utf8_decode($p_password), false))
        {
	        $t_sql    = "UPDATE `customers` 
					        SET `customers_password` = '" . $c_password . "' 
							WHERE 
								`customers_email_address` = '" . $c_email . "' AND 
								`customers_status` = 0";
	        $this->db->query($t_sql);
        	
        	return true;
        }

        return false;
    }


    public function clear_cache()
    {
	    include_once(DIR_FS_INC . 'strtoupper_wrapper.inc.php');
        include_once(DIR_FS_INC . 'xtc_db_connect.inc.php');
        include_once(DIR_FS_INC . 'xtc_db_query.inc.php');
        include_once(DIR_FS_INC . 'xtc_db_input.inc.php');
        include_once(DIR_FS_INC . 'xtc_db_fetch_array.inc.php');
        include_once(DIR_FS_INC . 'xtc_db_num_rows.inc.php');
        include_once(DIR_FS_CATALOG . 'gm/inc/check_data_type.inc.php');
        include_once(DIR_FS_CATALOG . 'system/gngp_layer_init.inc.php');
        include_once(DIR_FS_CATALOG . 'system/core/caching/CacheControl.inc.php');
        include_once(DIR_FS_CATALOG . 'GXMainComponents/Loaders/GXCoreLoader/StaticGXCoreLoader.inc.php');
        require_once __DIR__ . '/../../admin/includes/classes/SecurityCheckCache.php';
        
        $t_db_link = xtc_db_connect();
    
        $coo_cache_control = new CacheControl();
        $coo_cache_control->clear_data_cache();
        
        $coo_phrase_cache_builder = MainFactory::create_object('PhraseCacheBuilder');
        $coo_phrase_cache_builder->build();
        
        $coo_cache_control->clear_text_cache();
    
        $coo_mail_templates_cache_builder = MainFactory::create_object('MailTemplatesCacheBuilder');
        $coo_mail_templates_cache_builder->build();

        $coo_cache_control->clear_menu_cache();
        $coo_cache_control->clear_content_view_cache();
    //  This class handles the cache file for the security check of the the Gambio Admin
        (new SecurityCheckCache($_SERVER['HTTP_USER_AGENT']))->deleteCacheFile();
    
        $currentTheme =  $this->db->getConfiguration('CURRENT_THEME');
    
        if ($currentTheme !== '') {
            require_once DIR_FS_CATALOG . 'GXMainComponents/ApplicationCss.inc.php';

            $application = new Gambio\GX\ApplicationCss();
            $application->runWithoutHeader();
        
            $themeId              = StaticGXCoreLoader::getThemeControl()->getCurrentTheme();
            $themeSourcePath      = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemesPath();
            $themeDestinationPath = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemePath();
            $themeSettings        = ThemeSettings::create(ThemeDirectoryRoot::create(new ExistingDirectory($themeSourcePath)),
                ThemeDirectoryRoot::create(new ExistingDirectory($themeDestinationPath)));
        
            /** @var ThemeService $themeService */
            $themeService = StaticGXCoreLoader::getService('Theme');
            $themeService->buildTemporaryTheme(ThemeId::create($themeId), $themeSettings);
        }
    
        $coo_cache_control->clear_templates_c();
        $coo_cache_control->clear_template_cache();
        $coo_cache_control->clear_google_font_cache();
        $coo_cache_control->clear_css_cache();
        $coo_cache_control->clear_shop_offline_page_cache();

        $this->set_no_error_output(false);

        ((is_null($___mysqli_res = mysqli_close($t_db_link))) ? false : $___mysqli_res);

        debug_notice('Cache cleared.');
    }


    public function rebuild_cache()
    {
        $coo_cache_control = new CacheControl();
        $coo_cache_control->rebuild_feature_index();
        $coo_cache_control->rebuild_products_categories_index();
        $coo_cache_control->rebuild_products_properties_index();

        debug_notice('Cache rebuilt.');
    }


    public function get_current_shop_version()
    {
        return $this->db->getGmConfiguration('CURRENT_SHOP_VERSION');
    }


    public function set_current_shop_version()
    {
        $this->save_gm_configuration('CURRENT_SHOP_VERSION', $this->current_db_version);
    }


    public function reset_current_shop_version()
    {
        $this->save_gm_configuration('CURRENT_SHOP_VERSION', '0');
    }


    public function set_installed_version()
    {
        include(DIR_FS_CATALOG . 'release_info.php');
        
        $this->save_gm_configuration('INSTALLED_VERSION', $gx_version);
        $this->reset_update_array();
    }

    public function reset_update_array()
    {
	    $this->save_gm_configuration('UPDATE_ARRAY', serialize([]));
    }


    public function is_update_mandatory()
    {
        include(DIR_FS_CATALOG . 'release_info.php');
		
		return $gx_version !== $this->db->getGmConfiguration('INSTALLED_VERSION');
	}
	
	protected function check_correct_shop($p_dir)
	{
		$t_source_file = 'cache/source.test';
		$t_target_file = 'cache/target.test';
		
		$t_write_success = @file_put_contents(DIR_FS_CATALOG . $t_source_file, '');
		if ($t_write_success === false)
		{
			return false;
		}
		
		if($_POST['protocol'] === 'ftp')
		{
			$coo_ftp_manager = FTPManager::get_instance(true, $_POST['FTP_HOST'], $_POST['FTP_USER'], $_POST['FTP_PASSWORD'], $_POST['FTP_PASV']);
		}
		else
		{
			$coo_ftp_manager = SFTPManager::get_instance(true, $_POST['FTP_HOST'], $_POST['FTP_USER'], $_POST['FTP_PASSWORD'], $_POST['FTP_PORT']);
		}
			
		$t_success = $coo_ftp_manager->put_file($p_dir, DIR_FS_CATALOG . $t_source_file, $t_target_file);
		if ($t_success == false)
		{
			unlink(DIR_FS_CATALOG . $t_source_file);
			return false;
		}

        $t_success = file_exists(DIR_FS_CATALOG . $t_target_file);

        unlink(DIR_FS_CATALOG . $t_source_file);
        $coo_ftp_manager->delete_file($p_dir, $t_target_file);

        return $t_success;
    }


    public function rebuild_gambio_update_array(array $p_update_dir_array)
    {
        $this->gambio_update_array = array();
        foreach($p_update_dir_array as $t_update_path)
        {
            $coo_update = new GambioUpdateModel($t_update_path, $this->db_host, $this->db_user, $this->db_password,
                                                $this->db_name, $this->db_persistent, $this->customer_id);

            if($coo_update->check_environment_requirements())
            {
                $this->gambio_update_array[] = $coo_update;
            }
        }
    }


    public function get_rerun_step()
    {
        return $this->rerun_step;
    }


    /**
     * Try to delete files/directories via PHP
     *
     * @param array $files
     */
    protected function _deleteFiles(array $files)
    {
        foreach($files as $file)
        {
            $file         = trim($file);
            $fileOriginal = $file;
            $file         = DIR_FS_CATALOG . $file;

            if($file !== DIR_FS_CATALOG
               && substr($file, -1) !== '.'
               && substr($file, -2) !== './'
               && strpos($file, '..') === false
            )
            {
                $fileComparePath = realpath(DIR_FS_CATALOG) . '/' . $fileOriginal;
                $fileComparePath = str_replace('\\', '/', $fileComparePath);
    
                if($file && file_exists($file) && $fileComparePath === str_replace('\\', '/', realpath($file)))
                {
                    if(is_dir($file))
                    {
                        $this->_deleteDir($file);
                    }
                    else
                    {
                        $this->deleteOperationsSuccess &= @unlink($file);
                    }
                }
            }
        }
    }


    /**
     * @param string $p_dir
     */
    protected function _deleteDir($p_dir)
    {
        $dirContent = @scandir($p_dir);

        if(is_array($dirContent))
        {
            $files = array_diff($dirContent, array('.', '..'));

            foreach($files as $file)
            {
                if(is_dir("$p_dir/$file"))
                {
                    $this->_deleteDir("$p_dir/$file");
                }
                else
                {
                    $this->deleteOperationsSuccess &= @unlink("$p_dir/$file");
                }
            }
        }
    
        $this->deleteOperationsSuccess &= @rmdir($p_dir);
    }


    /**
     * @param array $files
     *
     * @return int success 1 failure 0
     */
    protected function _moveFiles(array $files)
    {
        $success = 1;

        foreach($files as $move)
        {
            $old = DIR_FS_CATALOG . $move['old'];
            $new = DIR_FS_CATALOG . $move['new'];

            if(file_exists($old) && !file_exists($new))
            {
                $success &= @rename($old, $new);
            }
            elseif(file_exists($old) && file_exists($new))
            {
                $success &= $this->_replace($old, $new);
            }
            else
            {
                $success = 0;
            }
        }

        return $success;
    }


	/**
	 * Replaces the destination with the source. Before the replacement, the destination will be backuped.
	 *
	 * Backup format:
	 * file:      sample.php -> sample.php.20181006150400.bak
	 * directory: dir[/subdir/sample.php] -> dir-20181006150400-bak[/subdir/sample.php.bak]
	 *
	 * @param string $sourceFilePath
	 * @param string $destinationFilePath
	 * @param int    $success
	 * @param array  $renameHistory
	 *
	 * @return bool true on success; false on failure
	 */
	protected function _replace($sourceFilePath, $destinationFilePath, $success = null, $renameHistory = array())
	{
		if(!file_exists($destinationFilePath))
		{
			return false;
		}
		
		$mainCall = $success === null;
		$success  = $success === null ? 1 : $success;
		
		$backupFileName = $destinationFilePath . '.' . date('YmdHis') . '.bak';
		
		if(is_dir($destinationFilePath) && $success)
		{
			$dirContent = @scandir($destinationFilePath);
			
			if(is_array($dirContent))
			{
				$files = array_diff($dirContent, array('.', '..'));
				
				foreach($files as $file)
				{
					if(!$success)
					{
						break;
					}
					
					if(is_dir("$destinationFilePath/$file"))
					{
						$success &= $this->_replace("$sourceFilePath/$file", "$destinationFilePath/$file", $success,
						                            $renameHistory);
					}
					elseif(strlen($file) >= 4 && substr($file, -4) !== '.bak')
					{
						$backupFileName = $destinationFilePath . '/' . $file . '.bak';
						
						$rename = @rename("$destinationFilePath/$file", $backupFileName);
						
						if(!$rename)
						{
							@chmod($destinationFilePath, 0777);
							$success &= @rename("$destinationFilePath/$file", $backupFileName);
						}
						
						if($success)
						{
							$renameHistory[] = ['old' => "$destinationFilePath/$file", 'new' => $backupFileName];
						}
						else
						{
							foreach($renameHistory as $log)
							{
								@rename($log['new'], $log['old']);
							}
							
							break;
						}
					}
				}
			}
			
			$backupFileName = $destinationFilePath . '-' . date('YmdHis') . '-bak';
		}
		
		if(!$mainCall || !$success)
		{
			return $success;
		}
		
		$success &= @rename($destinationFilePath, $backupFileName);
		if($success)
		{
			$success &= @rename($sourceFilePath, $destinationFilePath);
		}
		
		return $success;
	}


    /**
     * @param array $files
     *
     * @return int success 1 failure 0
     */
    protected function _chmod(array $files)
    {
        $success = 1;

        foreach($files as $file)
        {
            $success &= @chmod($file['PATH'], 0777);
        }

        return $success;
    }


    public function isUpdateAvailable()
    {
        if (!class_exists('AutoUpdateChecker')) {
            return false;
        }
        $autoUpdateChecker = new AutoUpdateChecker($this->db);
	    
	    return $autoUpdateChecker->isUpdateAvailable();
    }


    public function set_no_error_output($p_noErrorOutput)
    {
        if($p_noErrorOutput)
        {
            file_put_contents(DIR_FS_CATALOG . 'cache/no_error_output.php', 'no_error_output');
        }
        elseif(file_exists(DIR_FS_CATALOG . 'cache/no_error_output.php'))
        {
            unlink(DIR_FS_CATALOG . 'cache/no_error_output.php');
        }
    }

    public function configureErrorReporting($isActive) {
	    $configPath = DIR_FS_CATALOG . 'cache/error_reporting_configuration.json';
	    $config     = [
		    'active' => $isActive,
		    'dsn'    => 'https://22a09c39ec104b93a0624e18f61055fe@telemetry.gambio-server.net/6'
	    ];
	
	    file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	
	    $this->save_gm_configuration('MODULE_CENTER_ERRORREPORTING_INSTALLED', $isActive ? '1' : '0');
    }
	
	
	public function configureShopInformationDataProcessing($accepted)
	{
		$this->save_gm_configuration('ADMIN_FEED_ACCEPTED_SHOP_INFORMATION_DATA_PROCESSING', $accepted ? "true" : "false");
	}
	
	
	protected function save_gm_configuration($key, $value, $type = '')
	{
	    $this->db->replaceGmConfiguration($key, $value, $type);
	}
}
