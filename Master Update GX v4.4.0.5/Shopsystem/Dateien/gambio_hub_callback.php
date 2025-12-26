<?php
/* --------------------------------------------------------------
   gambio_hub_callback.php 2018-05-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_CORE_ERROR & ~E_CORE_WARNING);

@date_default_timezone_set('Europe/Berlin');

require_once __DIR__ .'/vendor/autoload.php';

require_once __DIR__ . '/GXMainComponents/Shared/LegacyDependencyContainer.php';

if(file_exists('includes/local/configure.php'))
{
	require_once 'includes/local/configure.php';
}
else
{
	require_once 'includes/configure.php';
}

require_once DIR_FS_CATALOG . 'vendor/autoload.php';

if(!class_exists('\HubPublic\ValueObjects\HubClientKey'))
{
	spl_autoload_register(function ($class)
	{
		// project-specific namespace prefix
		$prefix = 'HubPublic\\';
		
		// base directory for the namespace prefix
		$base_dir = DIR_FS_CATALOG . 'vendor/gambio-hub/hubpublic/src/';
		
		// does the class use the namespace prefix?
		$len = strlen($prefix);
		if(strncmp($prefix, $class, $len) !== 0)
		{
			// no, move to the next registered autoloader
			return;
		}
		
		// get the relative class name
		$relative_class = substr($class, $len);
		
		// replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php
		$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
		
		// if the file exists, require it
		if(file_exists($file))
		{
			require $file;
		}
	});
}

require_once DIR_FS_CATALOG . 'GXModules/Gambio/Hub/Shop/Classes/Extensions/HubCallback.inc.php';

use \HubPublic\Http\CurlRequest;

$callback = new HubCallback(new CurlRequest());
$callback->proceed();
