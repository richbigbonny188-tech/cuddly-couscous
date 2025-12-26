<?php
/* --------------------------------------------------------------
   FileManagerInterface.inc.php 2016-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2016 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Interface FileManagerInterface
 */
interface FileManagerInterface
{
	public function check_chmod($p_chmod_array = array());
	public function chmod_444($p_dir);
	public function chmod_777($p_dir, $p_chmod_array = array());
	public function connect();
	public function delete_file($p_dir, $p_file_path);
	public function delete_files($p_dir, $p_file_array = array());
	public function find_shop_dir($p_dir);
	public function get_directories($p_dir, $p_no_parent_dirs = false);
	public function is_shop($p_dir);
	public function move($p_dir, $p_file_array = array());
	public function put_file($p_dir, $p_source_file_path, $p_target_file_path);
	public function quit();
	public function recursive_check_chmod($p_dir, $p_exclude = array('.htaccess', '.', '..'));
	public function write_robots_file($p_dir, $p_relative_shop_path, $p_https_server = false);
}