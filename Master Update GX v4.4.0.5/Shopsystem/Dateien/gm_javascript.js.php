<?php
/* --------------------------------------------------------------
   gm_javascript.js.php 2018-06-15
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/
require_once 'includes/application_top.php';

if(isset($_SESSION['language_charset']))
{
	header('Content-Type: text/javascript; charset=' . $_SESSION['language_charset']);
}
else
{
	header('Content-Type: text/javascript; charset=utf-8');
}

$deactivatedGlobals = (!isset($_GET['globals']) || $_GET['globals'] !== 'off');

if($deactivatedGlobals)
{
	$httpCaching = MainFactory::create_object('HTTPCaching');
	$httpCaching->start_output_buffer();
	
	$page            = isset($_GET['page']) ? (string)$_GET['page'] : 'Global';
	$usermodJsMaster = MainFactory::create('UsermodJSMaster', $page);
	$files           = $usermodJsMaster->get_files();
	
	foreach($files as $file)
	{
		if(file_exists($file))
		{
			// print new line avoiding conflicts with comments
			echo "\n";
			include_once $file;
		}
	}
	
	$jsContent = $httpCaching->stop_output_buffer();
	
	$httpCaching->send_header($jsContent, false, false, 'public', time() + 60 * 60 * 24 * 31, '');
	$httpCaching->check_cache($jsContent);
	
	echo $jsContent;
	
	xtc_db_close();
	exit;
}

/////////////////// OLD SCRIPTS ///////////////////////

$_SESSION['lightbox']->set_actual('false');

$httpCaching = MainFactory::create_object('HTTPCaching');
$httpCaching->start_output_buffer();

if(!isset($_GET['globals']) || $_GET['globals'] !== 'off')
{

	$jsOptionsControl = MainFactory::create_object('JSOptionsControl');
	$jsOptions        = $jsOptionsControl->get_options_array($_GET);
	echo "\nvar js_options = " . json_encode($jsOptions) . "\n;var t_php_helper = \'\';\n";

	if(isset($GLOBALS['coo_debugger']) && is_object($GLOBALS['coo_debugger'])
	   && $GLOBALS['coo_debugger']->is_enabled('js') == true)
	{
		include_once DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/javascript/StopWatch.js';
	}

	//JQuery
	include_once get_usermod(DIR_FS_CATALOG . 'gm/javascript/jquery/jquery.min.js');
	//JQuery Migrate
	include_once get_usermod(DIR_FS_CATALOG . 'gm/javascript/jquery/jquery-migrate.min.js');
	/* BOF StyleEdit */
	if( StyleEditServiceFactory::service()->isEditing() || $_SESSION['style_edit_mode'] == 'sos')
	{
		include_once get_usermod(DIR_FS_CATALOG . 'gm/javascript/jquery/ui/jquery-ui.js');

		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/jquery/plugins/fancybox/jquery.mousewheel-3.0.2.pack.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/jquery/plugins/fancybox/jquery.fancybox-1.3.0.pack.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/jquery/plugins/ajaxupload.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/config_StyleEdit.js';

		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/GMColorizer.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/GMStyleMonitor.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/GMStyleEditToolBox.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/GMStyleEditHandler.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/GMStyleEditSelector.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/GMStyleEditControl.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/GMBoxesPageMenu.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/GMBoxesMaster.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/GMUploader.js';
		include_once DIR_FS_CATALOG . 'StyleEdit/javascript/style_edit.js.php';
	}
	/* EOF StyleEdit */

	$globalExtenderComponent = MainFactory::create_object('JSGlobalExtenderComponent');
	$globalExtenderComponent->set_data('GET', $_GET);
	$globalExtenderComponent->proceed();
}

$section = $_GET['section'];
if(preg_match('/[\W]+/', $section))
{
	trigger_error('gm_javascript: $_GET["section"] contains unexpected characters', E_USER_ERROR);
}

$c_page = '';
if(isset($_GET['page']) && is_string($_GET['page']))
{
	$c_page = trim((string)$_GET['page']);
}

if($c_page !== '')
{
	$classNameSuffix = 'ExtenderComponent';
	
	$requestRouter = MainFactory::create_object('RequestRouter', array($classNameSuffix));
	$requestRouter->set_data('GET', $_GET);
	$className     = 'JS' . $c_page;
	$proceedStatus = $requestRouter->proceed($className);
	if($proceedStatus !== true)
	{
		trigger_error('could not proceed module [' . htmlentities_wrapper($className) . ']', E_USER_ERROR);
	}
}

(is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"])) ? false : $___mysqli_res);

$jsContent = $httpCaching->stop_output_buffer();

$httpCaching->send_header($jsContent, false, false, 'private, must-revalidate', time() + (60 * 60 * 24 * 31), '');
$httpCaching->check_cache($jsContent);

echo $jsContent;
