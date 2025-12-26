<?php
/* --------------------------------------------------------------
   UpdaterLogin.inc.php 2016-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2016 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class UpdaterLogin
 */
class UpdaterLogin
{
	/**
	 * Checks if file gambio_updater/sectoken-$token exists and deletes it
	 * 
	 * @param string $token
	 * 
	 * @return bool
	 */
	static public function auth($token)
	{
		$tokenPath = dirname(__DIR__) . '/sectoken-' . basename((string)$token);
		
		// check if token is valid
		$auth = file_exists($tokenPath);
		
		if($auth)
		{
			CLIHelper::doSystem('rm -f ' . $tokenPath);
		}
		
		return $auth;
	}
}