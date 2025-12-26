<?php
/* --------------------------------------------------------------
   SFTPManager.inc.php 2018-10-05
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class SFTPManager implements FileManagerInterface
{
	static protected $instance = null;
	
	public $error = '';
	public $data_array = array();
	public $wrong_chmod_array = array();
	
	protected $host;
	protected $user;
	protected $password;
	protected $port;
	protected $sftp;
	protected $isDirCache = array();
	
	static public function get_instance($p_connect = false, $p_host = '', $p_user = '', $p_password = '', $p_port = 22)
	{
		$t_is_same_connection = isset(self::$instance->host)
		                        && isset(self::$instance->user)
		                        && isset(self::$instance->password)
		                        && isset(self::$instance->port)
		                        && $p_host == self::$instance->host
		                        && $p_user == self::$instance->user
		                        && $p_password == self::$instance->password
		                        && $p_port == self::$instance->port;
		
		if(self::$instance === null || $t_is_same_connection == false)
		{
			self::$instance = new self($p_connect, $p_host, $p_user, $p_password, $p_port);
		}
		
		return self::$instance;
	}
	
	
	protected function __construct($p_connect, $p_host, $p_user, $p_password, $p_port)
	{
		$this->host = $p_host;
		$this->user = $p_user;
		$this->password = $p_password;
		$this->port = $p_port;
		
		if($p_connect === true)
		{
			$this->connect();
		}
	}


	public function connect()
	{
		$this->sftp = new Net_SFTP($this->host, $this->port);
		
		if(@!$this->sftp->login($this->user, $this->password))
	    {
		    debug_notice(ERROR_SFTP_CONNECTION);
		    $this->error .= ERROR_SFTP_CONNECTION;
	    }
	    else
	    {
		    debug_notice('FTP connection successful');
		}
	}
	
	
	protected function _isdir($p_dir)
	{
		if(isset($this->isDirCache[$p_dir]))
		{
			return $this->isDirCache[$p_dir];
		}
		$this->isDirCache[$p_dir] = false;
		
		if($this->sftp->chdir($p_dir))
		{
			$this->sftp->chdir('..');
			$this->isDirCache[$p_dir] = true;
		}
		
		return $this->isDirCache[$p_dir];
	}
	
	
	public function get_directories($p_dir, $p_no_parent_dirs = false)
	{
		$t_parent = '';
		if($p_dir !== '/')
		{
			$t_parent = $p_dir;
		}
		elseif(array_key_exists('dir', $_POST))
		{
			$t_parent = $_POST['dir'];
		}
		$t_parent .= '/';
		
		$t_list_array = $this->_get_dir_content($p_dir);
		
		$t_final_list = array();
		
		$list_count = count($t_list_array);
		for($i = 0; $i < $list_count; $i++)
		{
			if(substr($t_list_array[$i], -3) === '/.')
			{
				continue;
			}
			
			if(strpos($t_list_array[$i], '/') !== 0)
			{
				$t_list_array[$i] = $t_parent . $t_list_array[$i];
			}
			
			if(substr($t_list_array[$i], -3) !== '/..' && substr($t_list_array[$i], -2) !== '/.' && $this->_isdir($t_list_array[$i]))
			{
				if($p_no_parent_dirs)
				{
					$t_dir = substr(strrchr($t_list_array[$i], '/'), 1);
				}
				else
				{
					$t_dir = $t_list_array[$i];
				}
				
				if(strlen($t_dir) > 1 && substr($t_dir, 0, 2) === '//')
				{
					$t_dir = substr($t_dir, 1);
				}
				
				$t_final_list[] = $t_dir;
			}
		}
		
		sort($t_final_list);
		
		return $t_final_list;
	}
	
	
	protected function _get_dir_content($p_dir)
	{
		$dirContent = $this->sftp->nlist($p_dir);
		
		if($dirContent === false)
		{
			$dirContent = array();
		}
		
		return $dirContent;
	}
	
	
	public function quit()
	{
		if($this->sftp !== null)
		{
			$this->sftp->disconnect();
		}
	}


	public function is_shop($p_dir)
	{
		$t_found_shop = false;
		
		if($this->sftp !== null)
		{
			$t_includes_dir_content_array = $this->_get_dir_content($p_dir . '/includes');
			$file_count = count($t_includes_dir_content_array);
			for($i = 0; $i < $file_count; $i++)
			{
				if(strpos($t_includes_dir_content_array[$i], 'application_top_main.php') !== false)
				{
					$t_found_shop = true;
					break;
				}
			}
		}
		
		return $t_found_shop;
	}
	
	
	public function chmod_777($p_dir, $p_chmod_array = array())
	{
		foreach($p_chmod_array as $t_line) {
			$t_line['PATH'] = trim($t_line['PATH']);
			if(strlen($t_line['PATH']) > 0)
			{
				$t_line['PATH'] = str_replace('\\', '/', $t_line['PATH']);
				if(substr($t_line['PATH'], 0, 1) != '/')
				{
					$t_line['PATH'] = '/' . $t_line['PATH'];
				}
				
				if(substr($p_dir, -1) == '/')
				{
					$t_line['PATH'] = substr($p_dir, 0, -1) . $t_line['PATH'];
				}
				else
				{
					$t_line['PATH'] = $p_dir . $t_line['PATH'];
				}
				
				$this->sftp->chmod(0777, trim($t_line['PATH']));
			}
		}
	}
	
	
	public function put_file($p_dir, $p_source_file_path , $p_target_file_path)
	{
		$t_success = false;
		$t_target_file_path = trim($p_target_file_path);
		if(strlen($t_target_file_path) > 0)
		{
			$t_target_file_path = str_replace('\\', '/', $t_target_file_path);
			if(substr($t_target_file_path, 0, 1) != '/')
			{
				$t_target_file_path = '/' . $t_target_file_path;
			}
			if(substr($p_dir, -1) == '/')
			{
				$t_target_file_path = substr($p_dir, 0, -1) . $t_target_file_path;
			}
			else
			{
				$t_target_file_path = $p_dir . $t_target_file_path;
			}
			
			$t_success = $this->sftp->put($t_target_file_path, $p_source_file_path, NET_SFTP_LOCAL_FILE);
		}
		
		return $t_success;
	}
	
	
	public function delete_file($p_dir, $p_file_path)
	{
		$t_success = false;
		$t_file = trim($p_file_path);
		if(strlen($t_file) > 0)
		{
			$t_file = str_replace('\\', '/', $t_file);
			if(substr($t_file, 0, 1) != '/')
			{
				$t_file = '/' . $t_file;
			}
			if(substr($p_dir, -1) == '/')
			{
				$t_file = substr($p_dir, 0, -1) . $t_file;
			}
			else
			{
				$t_file = $p_dir . $t_file;
			}
			$t_success = $this->sftp->delete($t_file);
		}
		
		return $t_success;
	}
	
	
	public function delete_files($p_dir, $p_file_array = array())
	{
		$t_dirs_to_delete = array();
		
		foreach($p_file_array as $t_file)
		{
			if(is_string($t_file))
			{
				$t_file = trim($t_file);
				if(strlen($t_file) > 0)
				{
					$t_file = str_replace('\\', '/', $t_file);
					if(substr($t_file, 0, 1) != '/')
					{
						$t_file = '/' . $t_file;
					}
					
					$t_recursive = false;
					if(substr($t_file, -2) == '/*')
					{
						$t_recursive = true;
						$t_file = substr($t_file, 0, -2);
					}
					
					if(substr($p_dir, -1) == '/')
					{
						$t_file = substr($p_dir, 0, -1) . $t_file;
					}
					else
					{
						$t_file = $p_dir . $t_file;
					}
					
					if($t_recursive && $this->_isdir($t_file))
					{
						$this->sftp->chdir($t_file);
						
						$this->data_array = array();
						$this->_recursive_ftpn_list($t_file);
						sort($this->data_array);
						$this->data_array = array_reverse($this->data_array);
						$t_mode = true;
						
						foreach($this->data_array as $t_child_file)
						{
							if($this->_isdir($t_child_file))
							{
								$this->sftp->chdir('..');
								
								$t_mode = $this->sftp->rmdir($t_child_file);
							}
							else
							{
								$t_mode &= $this->sftp->delete(trim($t_child_file));
							}
						}
						
						if($t_mode)
						{
							$t_mode = $this->sftp->rmdir($t_file);
						}
						
						$this->sftp->chdir('..');
					}
					elseif($this->_isdir($t_file))
					{
						$this->sftp->chdir($t_file);
						$t_delete_success = $this->sftp->rmdir($t_file);
						
						if($t_delete_success === false)
						{
							$t_dirs_to_delete[] = $t_file;
						}
						
						$this->sftp->chdir('..');
					}
					else
					{
						$this->sftp->delete($t_file);
					}
				}
			}
		}
		
		rsort($t_dirs_to_delete);
		
		foreach($t_dirs_to_delete as $t_dir)
		{
			$this->sftp->rmdir($t_dir);
		}
	}
	
	
	protected function _recursive_ftpn_list($p_dir, $p_directories = true, $p_files = true, $p_exclude = array('.', '..'))
	{
		$t_parent = '';
		if(isset($_POST['dir']))
		{
			$t_parent = $_POST['dir'];
		}
		$t_parent .= '/';
		
		if(strpos($p_dir, '/') !== 0)
		{
			$p_dir = $t_parent . $p_dir;
		}
		
		$t_list_array = $this->_get_dir_content($p_dir);
		foreach($t_list_array as $t_file)
		{
			if(strpos($t_file, '/') !== 0)
			{
				$t_file = $p_dir . '/' . $t_file;
			}
			
			if(strrchr($t_file, '/') !== false)
			{
				$t_name = substr(strrchr($t_file, '/'), 1);
			}
			else
			{
				$t_name = $t_file;
			}
			if(!in_array($t_name, $p_exclude))
			{
				if($this->_isdir($t_file) && $p_directories)
				{
					$this->data_array[] = $t_file;
					$this->_recursive_ftpn_list($t_file, $p_directories, $p_files, $p_exclude);
				}
				
				if(!$this->_isdir($t_file) && $p_files)
				{
					$this->data_array[] = $t_file;
				}
			}
		}
		
		return $this->data_array;
	}
	
	
	public function chmod_444($p_dir)
	{
		@chmod(DIR_FS_CATALOG . 'admin/includes/configure.org.php', 0444);
		@chmod(DIR_FS_CATALOG . 'admin/includes/configure.php', 0444);
		@chmod(DIR_FS_CATALOG . 'includes/configure.org.php', 0444);
		@chmod(DIR_FS_CATALOG . 'includes/configure.php', 0444);
		
		$this->sftp->chmod(0444, $p_dir . '/admin/includes/configure.org.php');
		$this->sftp->chmod(0444, $p_dir . '/admin/includes/configure.php');
		$this->sftp->chmod(0444, $p_dir . '/includes/configure.org.php');
		$this->sftp->chmod(0444, $p_dir . '/includes/configure.php');
	}
	
	
	public function check_chmod($p_chmod_array = array())
	{
		$this->wrong_chmod_array = array();
		
		foreach($p_chmod_array as $t_line) {
			$t_line = trim($t_line);
			if(strlen($t_line) > 0)
			{
				$t_line = str_replace('\\', '/', $t_line);
				if(substr($t_line, 0, 1) == '/')
				{
					$t_line = substr($t_line, 1);
				}
				
				if(@file_exists(DIR_FS_CATALOG . $t_line) && @!is_writable(DIR_FS_CATALOG . $t_line))
				{
					// set 777 rights only if path is not writable to prevent problems with servers which only need 755 rights to run properly
					@chmod(DIR_FS_CATALOG . $t_line, 0777);
					
					if(@!is_writable(DIR_FS_CATALOG . $t_line))
					{
						$this->wrong_chmod_array[] = DIR_FS_CATALOG . $t_line;
					}
				}
			}
		}
		
		return $this->wrong_chmod_array;
	}
	
	
	public function recursive_check_chmod($p_dir, $p_exclude = array('.htaccess', '.', '..'))
	{
		if(substr($p_dir, -1) != '/')
		{
			$p_dir .= '/';
		}
		
		if(is_dir($p_dir))
		{
			if($t_dh = opendir($p_dir))
			{
				while(($t_file = readdir($t_dh)) !== false)
				{
					if(in_array($t_file, $p_exclude) === false)
					{
						if(is_writable($p_dir . $t_file) === false)
						{
							// set 777 rights only if path is not writable to prevent problems with servers which only need 755 rights to run properly
							@chmod($p_dir . $t_file, 0777);
							
							if(is_writable($p_dir . $t_file) === false)
							{
								$this->wrong_chmod_array[] = $p_dir . $t_file;
							}
						}
						
						if(is_dir($p_dir . $t_file))
						{
							$this->recursive_check_chmod($p_dir . $t_file, $p_exclude);
						}
					}
				}
				closedir($t_dh);
			}
		}
		
		return $this->wrong_chmod_array;
	}
	
	
	public function write_robots_file($p_dir, $p_relative_shop_path, $p_https_server = false)
	{
		$t_file_exists = @file_exists(str_replace($p_relative_shop_path, '/', DIR_FS_CATALOG) . 'robots.txt');
		
		if($t_file_exists === false)
		{
			$t_file = DIR_FS_CATALOG . 'export/robots.txt.tpl';
			
			$t_lines = file($t_file);
			
			$t_robots_content = '';
			foreach($t_lines AS $t_line)
			{
				$t_robots_content .= str_replace('{PATH}', $p_relative_shop_path, $t_line);
			}
			
			// check SSL
			if($p_https_server)
			{
				// check if ssl is in a subdirectory
				$t_http_parsed = parse_url($p_https_server);
				if(isset($t_http_parsed['path']))
				{
					$t_robots_content .= "\t\n\t\n";
					$t_path = substr($t_http_parsed['path'], 1);
					if(substr($t_path, -1, 1) != '/')
					{
						$t_path = $t_path.'/';
					}
					// again for ssl
					foreach($t_lines AS $t_line)
					{
						$t_robots_content .= str_replace('{PATH}', $p_shop_path.$t_path, $t_line);
					}
				}
			}
		}
		
		$t_handle = fopen(DIR_FS_CATALOG . 'cache/temp_robots.txt', 'w+');
		fwrite($t_handle, $t_robots_content);
		rewind($t_handle);
		
		if($p_relative_shop_path != '/' && strpos($p_dir, substr($p_relative_shop_path, 0, -1)) !== false 
		   && substr($p_dir, strlen(substr($p_relative_shop_path, 0, -1)) * -1) === substr($p_relative_shop_path, 0, -1))
		{
			$t_dir =  substr($p_dir, 0, strlen(substr($p_relative_shop_path, 0, -1)) * -1);
			
			$t_filesize = $this->sftp->size($t_dir . '/robots.txt');
			if($t_filesize !== false)
			{
				$this->sftp->delete($t_dir . '/robots.txt');
				$t_success = $this->sftp->put($t_dir . '/robots.txt', DIR_FS_CATALOG . 'cache/temp_robots.txt', 
				                              NET_SFTP_LOCAL_FILE);
				$this->sftp->chmod(0644, $t_dir . '/robots.txt');
			}
			
			fclose($t_handle);
			
			if($t_success)
			{
				@unlink(DIR_FS_CATALOG . 'cache/temp_robots.txt');
				return true;
			}
		}
		
		return false;
	}
	
	
	public function move($p_dir, $p_file_array = array())
	{
		$t_success = true;
		
		foreach($p_file_array as $t_path_array)
		{
			if(isset($t_path_array['old']) && $t_path_array['new'])
			{
				$t_path_array['old'] = trim($t_path_array['old']);
				$t_path_array['new'] = trim($t_path_array['new']);
				
				if(strlen($t_path_array['old']) > 0 && strlen($t_path_array['new']))
				{
					$t_path_array['old'] = str_replace('\\', '/', $t_path_array['old']);
					if(substr($t_path_array['old'], 0, 1) != '/')
					{
						$t_path_array['old'] = '/' . $t_path_array['old'];
					}
					
					if(substr($p_dir, -1) == '/')
					{
						$t_path_array['old'] = substr($p_dir, 0, -1) . $t_path_array['old'];
					}
					else
					{
						$t_path_array['old'] = $p_dir . $t_path_array['old'];
					}
					
					$t_path_array['new'] = str_replace('\\', '/', $t_path_array['new']);
					if(substr($t_path_array['new'], 0, 1) != '/')
					{
						$t_path_array['new'] = '/' . $t_path_array['new'];
					}
					
					if(substr($p_dir, -1) == '/')
					{
						$t_path_array['new'] = substr($p_dir, 0, -1) . $t_path_array['new'];
					}
					else
					{
						$t_path_array['new'] = $p_dir . $t_path_array['new'];
					}
					
					// Windows file system cannot differentiate between upper- und lowercase -> do intermediate step
					if($t_path_array['old'] === $t_path_array['new'])
					{
						$t_temp_target_path = $t_path_array['new'] . '_temp';
						$this->_ftp_move($p_dir, $t_path_array['old'], $t_temp_target_path);
						$t_path_array['old'] = $t_temp_target_path;
					}
					
					$t_success = $this->_ftp_move($p_dir, $t_path_array['old'], $t_path_array['new']);
				}
			}
		}
		clearstatcache();
		$this->sftp->clearStatCache();
		return $t_success;
	}
	
	
	protected function _file_exists($p_path)
	{
		if($this->_isdir($p_path))
		{
			return true;
		}
		
		$t_path = $p_path;
		
		if(substr($p_path, -1) == '/')
		{
			$t_path = substr($t_path, 0, -1);
		}
		
		$t_dir = dirname($t_path);
		
		if(empty($t_dir) === false)
		{
			$t_list_array = $this->_get_dir_content($t_dir);
			
			foreach($t_list_array as $t_file)
			{
				if(basename($t_file) === basename($p_path))
				{
					return true;
				}
			}
		}
		
		return false;
	}
	
	
	protected function _create_directories($p_dir, $p_path)
	{
		$t_success = true;
		
		$this->sftp->chdir($p_dir);
		
		$t_path = $p_path;
		if(strpos($p_path, $p_dir) === 0)
		{
			$t_path = substr($p_path, strlen($p_dir));
		}
		
		$t_dirs_array = explode('/', $t_path);
		
		foreach($t_dirs_array as $t_folder)
		{
			if($t_folder !== '')
			{
				$t_new_dir_path = isset($t_new_dir_path) ? $t_new_dir_path . '/' . $t_folder : $p_dir . '/' . $t_folder;
				
				if($this->_isdir($t_new_dir_path) === false)
				{
					$t_new_dir = $this->sftp->mkdir($t_folder);
					
					if($t_new_dir !== false)
					{
						$this->isDirCache[$t_new_dir_path] = true;
						$this->sftp->chdir($t_new_dir_path);
						$this->sftp->chmod(0755, $t_new_dir_path);
						$t_success &= true;
					}
					else
					{
						$t_success = false;
					}
				}
			}
		}
		
		$this->sftp->chdir($p_dir);
		
		return $t_success;
	}
	
	
	// Moving files or folders if target does not exist. Otherwise source will be kept.
	protected function _ftp_move($p_dir, $p_source_path, $p_target_path)
	{
		$t_success = true;
		
		$this->sftp->chdir($p_dir);
		
		if(!$this->_file_exists($p_target_path))
		{
			$t_rename_success = $this->sftp->rename($p_source_path, $p_target_path);
		}
		else
		{
			$t_rename_success = $this->_replace($p_source_path, $p_target_path);
		}
		
		if($t_rename_success === false)
		{
			if($this->_file_exists(dirname($p_target_path)) === false)
			{
				$t_success &= $this->_create_directories($p_dir, dirname($p_target_path));
				
				if($t_success)
				{
					$t_success = $this->sftp->rename($p_source_path, $p_target_path);
				}
			}
			elseif($this->_isdir($p_source_path))
			{
				$t_list_array = $this->_get_dir_content($p_source_path);
				$t_delete_source = true;
				
				foreach($t_list_array as $t_file)
				{
					$t_file = basename($t_file);
					
					if($t_file != '.' && $t_file != '..')
					{
						if($this->_isdir($p_target_path . '/' . $t_file))
						{
							$t_success &= $this->_ftp_move($p_dir, $p_source_path . '/' . $t_file, $p_target_path . '/' . $t_file);
						}
						elseif($this->_file_exists($p_target_path . '/' . $t_file) == false)
						{
							$t_success &= $this->sftp->rename($p_source_path . '/' . $t_file, $p_target_path . '/' . $t_file);
						}
						else
						{
							$t_delete_source = false;
						}
					}
				}
				
				if($t_success && $t_delete_source)
				{
					$this->sftp->rmdir($p_source_path);
				}
			}
		}
		clearstatcache();
		$this->sftp->clearStatCache();
		return $t_success;
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
		if(!$this->_file_exists($destinationFilePath))
		{
			return false;
		}
		
		$mainCall = $success === null;
		$success  = $success === null ? 1 : $success;
		
		$backupFileName = $destinationFilePath . '.' . date('YmdHis') . '.bak';
		
		if($this->_isdir($destinationFilePath) && $success)
		{
			$dirContent = $this->_get_dir_content($destinationFilePath);
			$files      = array_diff($dirContent, array('.', '..'));
			
			foreach($files as $file)
			{
				if(!$success)
				{
					break;
				}
				
				if($this->_isdir("$destinationFilePath/$file"))
				{
					$success &= $this->_replace("$sourceFilePath/$file", "$destinationFilePath/$file", $success,
					                            $renameHistory);
				}
				elseif(strlen($file) >= 4 && substr($file, -4) !== '.bak')
				{
					$backupFileName = $destinationFilePath . '/' . $file . '.bak';
					
					$rename = $this->sftp->rename("$destinationFilePath/$file", $backupFileName);
					
					if(!$rename)
					{
						$this->sftp->chmod(0777, $destinationFilePath);
						$success &= $this->sftp->rename("$destinationFilePath/$file", $backupFileName);
					}
					
					if($success)
					{
						$renameHistory[] = ['old' => "$destinationFilePath/$file", 'new' => $backupFileName];
					}
					else
					{
						foreach($renameHistory as $log)
						{
							$this->sftp->rename($log['new'], $log['old']);
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
		
		$success &= $this->sftp->rename($destinationFilePath, $backupFileName);
		if($success)
		{
			$success &= $this->sftp->rename($sourceFilePath, $destinationFilePath);
		}
		
		return $success;
	}
	
	
	/**
	 * returns path to shop if found else returns $p_dir
	 *
	 * @param string $p_dir
	 *
	 * @return string
	 */
	public function find_shop_dir($p_dir)
	{
		if($this->is_shop($p_dir))
		{
			return $p_dir;
		}
		
		$dirContent = $this->get_directories($p_dir, true);
		
		$pathArray = preg_split('![/\\\]!', substr(DIR_FS_CATALOG, 0, -1));
		$pathArray = array_reverse($pathArray);
		
		$shopPath = '';
		
		foreach($pathArray as $pathDir)
		{
			$shopPath = $pathDir . '/' . $shopPath;
			
			foreach($dirContent as $dir)
			{
				if($pathDir === $dir)
				{
					$shopDir = '/' . substr($shopPath, 0, -1);
					if($this->is_shop($shopDir))
					{
						return $shopDir;
					}
				}
				else
				{
					$this->sftp->chdir($t_new_dir_path);
				}
			}
		}
		
		return $p_dir;
	}
}