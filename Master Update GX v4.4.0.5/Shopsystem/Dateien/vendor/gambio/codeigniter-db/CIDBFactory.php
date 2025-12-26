<?php

/* --------------------------------------------------------------
   CIDBFactory.php 2016-10-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2016 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

namespace CIDB;

require_once __DIR__ . '/CIDB.php';

/**
 * Class CIDBFactory
 * 
 * Use this class if you need to mock the creation of the database objects or if you need a PSR4 compatible class. 
 * 
 * Example: 
 * 
 * $cidbFactory = new \CIDB\Factory; 
 * 
 * 
 * @package CIDB
 */
class CIDBFactory
{
	/**
	 * Create Query Builder Class
	 *
	 * e.g. 'mysqli://user:password@localhost/dbname?socket=/tmp/mysql.sock'
	 *
	 * The socket parameter is optional.
	 *
	 * @param string $connectionString Provide a CodeIgniter DB compatible connection string.
	 *
	 * @return \CI_DB_query_builder
	 */
	public function createQueryBuilder($connectionString)
	{
		$this->_validateConnectionString($connectionString);
		
		return CIDB($connectionString);
	}
	
	
	/**
	 * Create Utilities Class
	 *
	 *  e.g. 'mysqli://user:password@localhost/dbname?socket=/tmp/mysql.sock'
	 *
	 * The socket parameter is optional.
	 *
	 * @param string $connectionString Provide a CodeIgniter DB compatible connection string.
	 *
	 * @return \CI_DB_utility
	 */
	public function createUtils($connectionString)
	{
		$this->_validateConnectionString($connectionString);
		
		return CIDBUtils($connectionString);
	}
	
	
	/**
	 * Create Forge Class
	 *
	 *  e.g. 'mysqli://user:password@localhost/dbname?socket=/tmp/mysql.sock'
	 *
	 * The socket parameter is optional.
	 *
	 * @param string $connectionString Provide a CodeIgniter DB compatible connection string.
	 *
	 * @return \CI_DB_mysqli_forge
	 */
	public function createForge($connectionString)
	{
		$this->_validateConnectionString($connectionString);
		
		return CIDBForge($connectionString);
	}
	
	
	/**
	 * Validate the provided connection string.
	 *
	 * @param string $connectionString
	 */
	protected function _validateConnectionString($connectionString)
	{
		if(empty($connectionString) || !is_string($connectionString))
		{
			throw new \InvalidArgumentException('Invalid connection string provided!');
		}
	}
}