<?php
/* --------------------------------------------------------------
   DatabaseModel.inc.php 2020-09-21
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class DatabaseModel
{
	protected static $mysqli;
	
	protected $coo_mysqli;
	protected $sql_errors;

	/**
	 * Creates a new DatabaseModel and establishes a DB connection
	 *
	 * @param string p_db_host The host for the DB connection
	 * @param string p_db_user The user for the DB connection
	 * @param string p_db_password The password for the DB connection
	 * @param string p_db_name The selected DB name
	 * @param bool p_db_persistent Persistent DB connection?
	 */
	public function __construct($p_db_host = '', $p_db_user = '', $p_db_password = '', $p_db_name = '', $p_db_persistent = null)
	{
		if(self::$mysqli === null)
		{
			$t_db_host = empty($p_db_host) ? DB_SERVER : $p_db_host;
			$t_db_user = empty($p_db_user) ? DB_SERVER_USERNAME : $p_db_user;
			$t_db_password = empty($p_db_password) ? DB_SERVER_PASSWORD : $p_db_password;
			$t_db_name = empty($p_db_name) ? DB_DATABASE : $p_db_name;
			//$t_db_persistent = $p_db_persistent == null ? USE_PCONNECT : $p_db_persistent;
			$t_db_port = ini_get("mysqli.default_port");
			$t_db_socket = ini_get("mysqli.default_socket");

			if(strstr($t_db_host,':'))
			{
				$t_db_host = explode(':', $t_db_host);
				if(is_numeric($t_db_host[1]))
				{
					$t_db_port = $t_db_host[1];
				}
				else
				{
					$t_db_socket = $t_db_host[1];
				}
				$t_db_host = $t_db_host[0];
			}

			// Port and Socket variables must not be an empty string (refs #41773). 
			if($t_db_port == '')
			{
				$t_db_port = null;
			}

			if($t_db_socket == '')
			{
				$t_db_socket = null;
			}
			
			$this->coo_mysqli = new mysqli($t_db_host, $t_db_user, $t_db_password, $t_db_name,$t_db_port,$t_db_socket);
			$this->sql_errors = array();

			if (version_compare($this->coo_mysqli->server_info, '5', '>=')) $this->query("SET SESSION sql_mode=''");

			$this->query("SET SQL_BIG_SELECTS=1");
			$this->set_charset('utf8');

			self::$mysqli = $this->coo_mysqli;
		}
		else
		{
			$this->reset_mysqli(self::$mysqli);
		}
	}


	/**
	 * Returns all logged SQL errors
	 *
	 * @return array An Array of all logged SQL errors
	 */
	public function get_sql_errors( )
	{
		return $this->sql_errors;
	}


	/**
	 * Executes query, returning a result depending on the type of query
	 *
	 * @param string p_sql
	 * @param bool p_force_result_object
	 * @return mixed
	 */
	public function query($p_sql, $p_force_result_object = false)
	{
		if ($coo_result = $this->coo_mysqli->query($p_sql))
		{
			if ($p_force_result_object)
			{
				return $coo_result;
			}
			if (strpos(strtolower(trim($p_sql)), 'select') === 0 || strpos(strtolower(trim($p_sql)), 'show') === 0)
			{
				$t_result_array = array();
				while ($t_row = $coo_result->fetch_assoc())
				{
					$t_result_array[] = $t_row;
				}
				return $t_result_array;
			}
			else if (strpos(strtolower(trim($p_sql)), 'insert') === 0)
			{
				return $this->coo_mysqli->insert_id;
			}
			else if (strpos(strtolower(trim($p_sql)), 'update') === 0 || strpos(strtolower(trim($p_sql)), 'delete') === 0)
			{
				return $this->coo_mysqli->affected_rows;
			}
			return true;
		}
		else
		{
			$this->sql_errors[] = array('query' => $p_sql, 'error' => $this->coo_mysqli->error);

			if(function_exists('debug_notice'))
			{
				debug_notice("MySQL-Error: " . $this->coo_mysqli->error . "\nQuery: " . $p_sql);
			}

			return false;
		}
	}


	/**
	 * Gets the last insert_id
	 *
	 * @return int
	 */
	public function get_insert_id()
	{
		return $this->coo_mysqli->insert_id;
	}


	public function set_charset($p_charset = 'utf8')
	{
		switch ($p_charset)
		{
			case 'big5':
			case 'dec8':
			case 'cp850':
			case 'hp8':
			case 'koi8r':
			case 'latin1':
			case 'latin2':
			case 'swe7':
			case 'ascii':
			case 'ujis':
			case 'sjis':
			case 'hebrew':
			case 'tis620':
			case 'euckr':
			case 'koi8u':
			case 'gb2312':
			case 'greek':
			case 'cp1250':
			case 'gbk':
			case 'latin5':
			case 'armscii8':
			case 'utf8':
			case 'ucs2':
			case 'cp866':
			case 'keybcs2':
			case 'macce':
			case 'macroman':
			case 'cp852':
			case 'latin7':
			case 'utf8mb4':
			case 'cp1251':
			case 'utf16':
			case 'utf16le':
			case 'cp1256':
			case 'cp1257':
			case 'utf32':
			case 'binary':
			case 'geostd8':
			case 'cp932':
			case 'eucjpms':
				if (version_compare(PHP_VERSION, '5.2.3', '>=')) {
					$this->coo_mysqli->set_charset($p_charset);
				} else {
					$this->query("SET NAMES " . $p_charset);
				}
				return true;
			default:
				return false;
		}
	}

	/* Clean duplicate entries in given table
	 *
	 * @param string $p_table
	 * @param array $p_columns_array
	 * @return boolean Returns true, if re-inserting of unique values was successful
	 */
	public function clean_table($p_table, $p_columns_array)
	{
		$t_success = true;

		$t_check = 'SELECT
						*
					FROM
						`' . $p_table . '`
					GROUP BY
						`' . implode('`,`', $p_columns_array) . '`
					HAVING
						COUNT(*) > 1
		';
		$t_result = $this->query($t_check);

		if(is_array($t_result) && count($t_result))
		{
			foreach($t_result as $t_line)
			{
				$t_delete = 'DELETE FROM `' . $p_table . '` WHERE ';
				$t_insert = 'INSERT INTO `' . $p_table . '` VALUES (';
				$t_where_part_array = array();
				$t_insert_values_array = array();

				foreach($t_line as $t_key => $t_value)
				{
                    if ($t_value === null)
                    {
                        if(in_array($t_key, $p_columns_array))
                        {
                            $t_where_part_array[] = '`' . $t_key . '` IS NULL';
                        }
                        $t_insert_values_array[] =  'NULL';
                        
                    }
                    else
                    {
                        if(in_array($t_key, $p_columns_array))
                        {
                            $t_where_part_array[] = '`' . $t_key . '`=' . '\'' . $this->coo_mysqli->real_escape_string($t_value) . '\'';
                        }
                        $t_insert_values_array[] =  '\'' . $this->coo_mysqli->real_escape_string($t_value) . '\'';
                    }
				}

				$t_delete .= implode(' AND ', $t_where_part_array);
				$t_insert .= implode(', ', $t_insert_values_array) . ')';

				$t_success &= $this->query($t_delete, true);
				$t_success &= $this->query($t_insert, true);
			}
		}

		return $t_success;
	}


	/**
	 * Set index for table, checking existence if index name is known
	 *
	 * @param type $p_table
	 * @param type $p_index_type = PRIMARY KEY, INDEX, UNIQUE or FULLTEXT
	 * @param type $p_columns_array
	 * @param type $p_index_name
	 * @return boolean Returns true, if index is successfully created or does already exist
	 */
	public function set_index($p_table, $p_index_type, $p_columns_array, $p_index_name = null)
	{
		$c_table = $this->coo_mysqli->real_escape_string($p_table);
		
		$t_non_unique = ($p_index_type == 'INDEX' || $p_index_type == 'FULLTEXT') ? 1 : 0;
		$t_get_indexes_query = $this->query('SHOW INDEX FROM `' . $c_table . '` WHERE `Non_unique` = ' . $t_non_unique);

		foreach($t_get_indexes_query as $value)
		{
			$t_column_array = array();
			$t_get_key_name_columns = $this->query('SHOW INDEX FROM `' . $c_table . '` WHERE `Key_name` = "' . $value['Key_name'] . '"');
			foreach($t_get_key_name_columns as $t_field)
			{
				$t_column_array[] = $t_field['Column_name'];
			}
			if($t_column_array === $p_columns_array)
			{
				return true;
			}
		}
		
		if($p_index_type === 'UNIQUE' || $p_index_type === 'PRIMARY KEY')
		{
			$this->clean_table($c_table, $p_columns_array);
		}
		$t_index_type = ($p_index_type == 'FULLTEXT') ? 'FULLTEXT' : 'BTREE';
		
		$t_index_names_array = $this->getIndicesNames($c_table, $t_non_unique, $t_index_type);
		$t_timestamp = time();
		
		if(sizeof($p_columns_array) <= 0)
		{
			return false;
		}
		
		// drop other indices with same name 
		$t_check = $this->query('SHOW INDEX FROM `' . $c_table . '` WHERE `Key_name` = "' . $this->coo_mysqli->real_escape_string($p_index_name) . '"', true);
		if($t_check->num_rows)
		{
			$this->query('DROP INDEX `' . $this->coo_mysqli->real_escape_string($p_index_name) . '` ON ' . $c_table);
		}
		
		// set name of index
		if(!empty($p_index_name)){
			// check 1
			if(in_array($p_index_name, $t_index_names_array))
			{
				$p_index_name = $p_index_name . '_' . $t_timestamp;
			}
			// rename with timestamp and check 2
			if(in_array($p_index_name, $t_index_names_array))
			{
				// if index exists, cancel process to add index
				return false;
			}
		}
		else
		{
			// no index to check
			return false;
		}
		
		$t_index_name_sql_part = ' `' . $this->coo_mysqli->real_escape_string($p_index_name) . '` ';
		
		// check, if column exists
		$t_columns_exists = $this->check_col_exists($c_table, $p_columns_array);
		
		// 1 column or more dosn't exist
		if($t_columns_exists !== $p_columns_array)
		{
			return false;
		}

		// add index
		if($p_index_type == 'PRIMARY KEY')
		{
			$t_sql = 'SHOW INDEX FROM ' . $c_table . ' WHERE Key_name = "PRIMARY"';
			$coo_result = $this->query($t_sql, true);
			if($coo_result->num_rows > 0)
			{
				$this->query('DROP INDEX `PRIMARY` ON ' . $c_table);
			}
			$t_index_name_sql_part = '';
		}
		
		if(!$this->indexExists($c_table, $p_index_type, $p_columns_array))
		{
			$t_sql = "ALTER TABLE `" . $c_table . "`
				ADD " . $this->coo_mysqli->real_escape_string($p_index_type) . " " . $t_index_name_sql_part . "(`" . implode('`,`', $p_columns_array) . "`)";

			return $this->query($t_sql, true);
		}
		
		return true;
	}

	/**
	 * selects all indices which exists in table
	 *
	 * @param type $p_table
	 * @param type $t_non_unique
	 * @param type $t_index_type
	 * @return array Returns indices which exists in table
	 */
	protected function getIndicesNames($p_table, $t_non_unique, $t_index_type)
	{
		$t_indices_names = array();
		$t_sql = "SHOW INDEX
						FROM `" . $this->coo_mysqli->real_escape_string($p_table) . "`
						WHERE
							Non_unique = '" . $t_non_unique . "' AND
							Index_type = '" . $t_index_type . "'";
		$t_result_array = $this->query($t_sql);
		foreach($t_result_array AS $t_key => $t_data_array)
		{
			$t_indices_names[$t_data_array['Key_name']] = $t_data_array['Key_name'];
		}
		return $t_indices_names;
	}


	/**
	 * Returns an array of all indices of a table
	 * 
	 * @param string $p_table
	 *
	 * @return array
	 */
	protected function getIndices($p_table)
	{
		$indicesGroupedByColums = array();
		
		$indexRows = $this->query('SHOW INDEX FROM `' . $this->coo_mysqli->real_escape_string($p_table) . '`');

		$indices = array();

		foreach($indexRows as $key => $row)
		{
			if(!isset($indices[$row['Key_name']]))
			{
				$indices[$row['Key_name']] = array('type' => $this->getIndexType($row), 'cols' => $row['Column_name']);
			}
			else
			{
				$indices[$row['Key_name']]['cols'] .= ',' . $row['Column_name'];
			}
		}

		foreach($indices as $keyName => $indexInfoArray)
		{
			if(!isset($indicesGroupedByColums[$indexInfoArray['cols']]))
			{
				$indicesGroupedByColums[$indexInfoArray['cols']] = array($indexInfoArray['type'] => array($keyName));
			}
			elseif(!isset($indicesGroupedByColums[$indexInfoArray['cols']][$indexInfoArray['type']]))
			{
				$indicesGroupedByColums[$indexInfoArray['cols']][$indexInfoArray['type']] = array($keyName);
			}
			else
			{
				$indicesGroupedByColums[$indexInfoArray['cols']][$indexInfoArray['type']][] = $keyName;
			}
		}
		
		return $indicesGroupedByColums;
	}


	/**
	 * get index type by SHOW INDEX record
	 * 
	 * @param array $row
	 *
	 * @return string
	 */
	protected function getIndexType(array $row)
	{
		if($row['Key_name'] === 'PRIMARY')
		{
			return 'PRIMARY KEY';
		}

		if($row['Index_type'] === 'FULLTEXT')
		{
			return 'FULLTEXT';
		}

		if($row['Non_unique'] === '0')
		{
			return 'UNIQUE';
		}

		return 'INDEX';
	}


	/**
	 * check if index already exists
	 *
	 * @param string $p_table
	 * @param string $p_indexType
	 * @param array  $columns
	 *
	 * @return bool
	 */
	protected function indexExists($p_table, $p_indexType, array $columns)
	{
		$indicesGroupedByColums = $this->getIndices($p_table);
		
		$columnsString = implode(',', $columns);
		
		switch($p_indexType)
		{
			case 'FULLTEXT':
				if($indicesGroupedByColums[$columnsString]['FULLTEXT'])
				{
					return true;
				}
			case 'INDEX':
				if($indicesGroupedByColums[$columnsString]['INDEX'])
				{
					return true;
				}
			case 'UNIQUE':
				if($indicesGroupedByColums[$columnsString]['UNIQUE'])
				{
					return true;
				}
			case 'PRIMARY KEY':
				if($indicesGroupedByColums[$columnsString]['PRIMARY KEY'])
				{
					return true;
				}
		}

		return false;
	}


	/**
	 * Checks if a column exists
	 *
	 * @param string PHPDoc for non-existing argument
	 * @param string PHPDoc for non-existing argument
	 *
	 * @return bool
	 */
	protected function columnExists($table, $column)
	{
		$query = "SHOW COLUMNS FROM `" . $this->coo_mysqli->real_escape_string($table) . "`;";
		$tableColumns = $this->query($query);
		
		foreach($tableColumns as $tableColumn)
		{
			if($column === $tableColumn['Field'])
			{
				return true;
			}
		}
		
		return false;
	}
	

	/**
	 * check if col of index-params exists in table
	 *
	 * @param type $p_table
	 * @param type $p_columns_array
	 * @return array Returns columns which exists
	 */
	protected function check_col_exists($p_table, $p_columns_array)
	{
		$t_columns_exists = $t_columns_index = array();
		
		$t_sql = "SHOW COLUMNS FROM `" . $this->coo_mysqli->real_escape_string($p_table) . "`;";
		$t_result_array = $this->query($t_sql);
		foreach($t_result_array AS $t_key => $t_data_array)
		{
			$t_columns_exists[] = $t_data_array['Field'];
		}
		
		foreach($p_columns_array AS $k_col => $v_col)
		{
			if(in_array($v_col, $t_columns_exists ))
			{
				$t_columns_index[] = $v_col;
			}
		}
		return $t_columns_index;
	}

	/**
	 * handle errors of function set_index
	 *
	 * @param type $p_error_number
	 * @param type $p_table
	 * @param type $p_index_type
	 * @param type $p_columns_array
	 * @param type $p_index_name
	 * @return String Returns error-text
	 */
	protected function set_index_error($p_error_number, $p_table, $p_index_type, $p_columns_array, $p_index_name)
	{
		$t_error_text = 'Unbekannter Fehler';
		switch($p_error_number)
		{
			case 1 :
				$t_error_text = 'Indexname nicht bekannt<br/>' . 'Tabelle: ' . $p_table;
				break;
			case 2 :
				$t_error_text = 'Indexname existiert bereits<br/>' . 'Tabelle: ' . $p_table . '<br/>Index: ' . $p_index_name;
				break;
			case 3 :
				$t_error_text = 'Mindestens eine Spalte existiert nicht<br/>' . 'Tabelle: ' . $p_table . '<br/>Spalten: ' . implode($p_columns_array, ', ');
				break;
			case 4 :
				$t_error_text = 'Keine Spalten für den Index angegeben<br/>' . 'Tabelle: ' . $p_table . '<br/>Index: ' . $p_index_name;
				break;
		}
		$t_error_text = 'Error in set_index_error:<br/>' . $t_error_text . '<br/><br/>';
		return $t_error_text;
	}

	/**
	 * Drop index if exists in table
	 *
	 * @param type $p_table
	 * @param type $p_index_name
	 * @return boolean Returns true, if index is successfully deleted
	 */
	protected function drop_index($p_table, $p_index_name)
	{
		$t_success = true;

		$t_sql = 'SHOW INDEX FROM `' . $this->coo_mysqli->real_escape_string($p_table) . '` WHERE Key_name = "' .  $this->coo_mysqli->real_escape_string($p_index_name) . '"';

		$t_get_columns = $this->query($t_sql, true);
		if($t_get_columns->num_rows > 0)
		{
			if($p_index_name !== 'PRIMARY')
			{
				$t_query = 'ALTER TABLE `' . $this->coo_mysqli->real_escape_string($p_table) . '` DROP INDEX `' . $this->coo_mysqli->real_escape_string($p_index_name) . '`';
			}
			else
			{
				$t_query = 'ALTER TABLE `' . $this->coo_mysqli->real_escape_string($p_table) . '` DROP PRIMARY KEY';
			}

			$t_success = $this->query($t_query);
		}

		return $t_success;
	}

	/**
	 * Check if column exists in table
	 *
	 * @param type $p_table
	 * @param type $p_column
	 * @return Returns true, if column already exist
	 */
	protected function table_column_exists($p_table, $p_column)
	{
		if($this->table_exists($p_table) === false)
		{
			return false;
		}
		
		$t_return = false;
		$t_check = $this->query("DESCRIBE `" . $p_table . "` '" . $p_column . "'", true);
		if($t_check->num_rows != 0)
		{
			$t_return = true;
		}
		return $t_return;
	}

	/**
	 * Check if table exists in database
	 *
	 * @param string $p_table
	 * @return bool Returns true, if table already exist
	 */
	public function table_exists($p_table)
	{
		$t_return = false;
		$t_check = $this->query("SHOW TABLES LIKE '" . $p_table . "'", true);
		if($t_check->num_rows != 0)
		{
			$t_return = true;
		}
		return $t_return;
	}

	/**
	 * mysqli::real_escape_string
	 * @param string $p_string
	 * @return string Returns an escaped string.
	 */
	public function real_escape_string($p_string)
	{
		return $this->coo_mysqli->real_escape_string($p_string);
	}


	/**
	 * Method to delete duplicate entries for a unique column that is missing a UNIQUE KEY constraint.
	 *
	 * @param string $p_table The table featuring the unique column
	 * @param string $p_unique_key The unique column (to be)
	 * @param string $p_primary_key The primary key to differ the rows that bare the same unique key
	 * @return boolean Success
	 */
	public function delete_duplicate_entries($p_table, $p_unique_key, $p_primary_key)
	{
		$t_sql = '	DELETE FROM
						' . $p_table . '
					USING
						' . $p_table . ',
						' . $p_table . ' AS tmp_table
					WHERE
						' . $p_table . '.' . $p_unique_key . ' = tmp_table.' . $p_unique_key . ' AND
						' . $p_table . '.' . $p_primary_key . ' < tmp_table.' . $p_primary_key;

		$t_success = $this->query($t_sql, true);

		return $t_success;
	}
	
	public function get_coo_mysqli()
	{
		return $this->coo_mysqli;
	}
    
    
    /**
     * Adds a new Admin Access Group into the database.
     *
     * @param array $names        Array which contains the names for this group. Keys must be the language id.
     * @param array $descriptions Array which contains the descriptions for this group. Keys must be the language id.
     * @param int   $sortOrder    Sort order.
     * @param int   $parentId     Parent group id.
     *
     * @return int|bool Returns the group id on success or false on failure.
     */
    public function addAdminAccessGroup($names, $descriptions = [], $sortOrder = 0, $parentId = 0, $protected = true)
    {
        $query   = sprintf('INSERT INTO `admin_access_groups` (`parent_id`, `sort_order`, `protected`) VALUES (%d, %d, %d);',
                           (int)$parentId,
                           (int)$sortOrder,
                           (int)$protected);
        $groupId = $this->query($query);
        if ($groupId === false) {
            return false;
        }
        
        $query = 'INSERT INTO `admin_access_group_descriptions` (`admin_access_group_id`, `language_id`, `name`, `description`) VALUES ';
        foreach ($names as $languageId => $name) {
            $query .= sprintf('(%d, %d, "%s", "%s"), ',
                              (int)$groupId,
                              (int)$languageId,
                              $this->real_escape_string($name),
                (isset($descriptions[$languageId]) ? $this->real_escape_string($descriptions[$languageId]) : ''));
        }
        
        $result = $this->query(substr($query, 0, -2) . ';');
        if ($result !== false && $result !== null) {
            return $groupId;
        }
        
        return false;
    }
    
    
    /**
     * Adds a Group Item to an existing Admin Access Group.
     *
     * @param int          $groupId     Group ID.
     * @param string|array $types       Type of this item. Should be "CONTROLLER", "PAGE" or "AJAX_HANDLER".
     * @param string|array $identifiers Identifier of this item. Must be the name of the controller or page.
     *
     * @return bool Return true on success and false on failure.
     */
    public function addAdminAccessGroupItem($groupId, $types, $identifiers)
    {
        $query = 'REPLACE INTO `admin_access_group_items` (`admin_access_group_id`, `identifier`, `type`) VALUES ';
        
        if (is_array($identifiers)) {
            foreach ($identifiers as $index => $identifier) {
                $query .= sprintf('(%d, "%s", "%s"), ',
                                  (int)$groupId,
                                  $this->real_escape_string($identifier),
                                  $this->real_escape_string(is_array($types) ? $types[$index] : $types));
            }
        } else {
            $query .= sprintf('(%d, "%s", "%s"), ',
                              (int)$groupId,
                              $this->real_escape_string($identifiers),
                              $this->real_escape_string($types));
        }
        
        $result = $this->query(substr($query, 0, -2) . ';');
        
        return $result !== null && $result !== false;
    }
    
    
    /**
     * Adds a new Admin Access Role into the database.
     *
     * @param array $names        Array which contains the names for this role. Keys must be the language id.
     * @param array $descriptions Array which contains the descriptions for this role. Keys must be the language id.
     * @param int   $sortOrder    Sort order.
     *
     * @return int|bool Returns the role id on success or false on failure.
     */
    public function addAdminAccessRole($names, $descriptions = [], $sortOrder = 0, $protected = true)
    {
        $query  = sprintf('INSERT INTO `admin_access_roles` (`sort_order`, `protected`) VALUES (%d, %d);',
                          (int)$sortOrder,
                          (int)$protected);
        $roleId = $this->query($query);
        if ($roleId === null || $roleId === false) {
            return false;
        }
        
        $query = 'INSERT INTO `admin_access_role_descriptions` (`admin_access_role_id`, `language_id`, `name`, `description`) VALUES ';
        foreach ($names as $languageId => $name) {
            $query .= sprintf('(%d, %d, "%s", "%s"), ',
                              (int)$roleId,
                              (int)$languageId,
                              $this->real_escape_string($name),
                (isset($descriptions[$languageId]) ? $this->real_escape_string($descriptions[$languageId]) : ''));
        }
        $result = $this->query(substr($query, 0, -2) . ';');
        if ($result !== null && $result !== false) {
            return $roleId;
        }
        
        return false;
    }
    
    
    /**
     * Grants permission for an existing Admin Access Permission.
     * The permission will be identified by the ID of the Admin Access Role and Group.
     *
     * @param int  $roleId        Role id.
     * @param int  $groupId       Group id.
     * @param bool $grantReading  True if reading permission should be granted, otherwise false.
     * @param bool $grantWriting  True if writing permission should be granted, otherwise false.
     * @param bool $grantDeleting True if deleting permission should be granted, otherwise false.
     *
     * @return bool Return true on success and false on failure.
     */
    public function grantAdminAccessPermission(
        $roleId,
        $groupId,
        $grantReading = true,
        $grantWriting = true,
        $grantDeleting = true
    ) {
        $query  = sprintf('REPLACE INTO `admin_access_permissions` (`admin_access_role_id`, `admin_access_group_id`, `reading_granted`, `writing_granted`, `deleting_granted`) VALUES (%d, %d, %d, %d, %d);',
                          (int)$roleId,
                          (int)$groupId,
                          (int)$grantReading,
                          (int)$grantWriting,
                          (int)$grantDeleting);
        $result = $this->query($query, true);
        $error  = $result !== null && $result !== false;
        
        $parentGroupSql   = sprintf('SELECT * FROM `admin_access_groups` WHERE `admin_access_group_id` = (SELECT `parent_id` FROM `admin_access_groups` WHERE `admin_access_group_id` = %d);',
                                    (int)$groupId);
        $parentGroupQuery = $this->query($parentGroupSql, true);
        if ($parentGroupSql !== null && $parentGroupQuery->num_rows > 0) {
            $parentGroup = $parentGroupQuery->fetch_assoc();
            
            if ((int)($parentGroup['admin_access_group_id']) > 0) {
                $error &= $this->grantAdminAccessPermission($roleId,
                                                            (int)$parentGroup['admin_access_group_id'],
                                                            $grantReading ? : $parentGroup['reading_granted'] === '1',
                                                            $grantWriting ? : $parentGroup['writing_granted'] === '1',
                                                            $grantDeleting ? : $parentGroup['deleting_granted']
                                                                               === '1');
            }
        }
        
        return $error;
    }
    
    
    /**
     * Adds an existing Admin Access Role to an existing Customer/Admin.
     *
     * @param int $roleId     Role id.
     * @param int $customerId Customer id.
     *
     * @return bool Return true on success and false on failure.
     */
    public function addAdminAccessRoleToUserByCustomerId($roleId, $customerId)
    {
        $query  = sprintf('REPLACE INTO `admin_access_users` (`customer_id`, `admin_access_role_id`) VALUES (%d, %d);',
                          (int)$customerId,
                          (int)$roleId);
        $result = $this->query($query, true);
        
        return $result !== null && $result !== false;
    }
    
    
    /**
     * Removes an existing Admin Access Role to an existing Customer/Admin.
     *
     * @param int $roleId     Role id.
     * @param int $customerId Customer id.
     *
     * @return bool Return true on success and false on failure.
     */
    public function removeAdminAccessRoleFromUserByCustomerId($roleId, $customerId)
    {
        $query  = sprintf('DELETE FROM `admin_access_users` WHERE `customer_id` = %d AND `admin_access_role_id` = %d;',
                          (int)$customerId,
                          (int)$roleId);
        $result = $this->query($query, true);
        
        return $result !== null && $result !== false;
    }
    
    
    /**
     * Returns the id of an existing Admin Access Group identified by a type and the identifier.
     *
     * @param string $type       Type of this identifier. Should be "PAGE", "CONTROLLER" or "AJAX_HANDLER".
     * @param string $identifier Identifier you are looking for. Should be the name of a controller or page.
     *
     * @return bool|int Return id on success and false on failure.
     */
    public function getAdminAccessGroupIdByIdentifier($type, $identifier)
    {
        $query = sprintf('SELECT * FROM  `admin_access_group_items` WHERE `type` = "%s" AND `identifier` = "%s" LIMIT 1;',
                         $this->real_escape_string($type),
                         $this->real_escape_string($identifier));
        
        $group = $this->query($query, true);
        if ($group !== null && $group->num_rows > 0) {
            $group = $group->fetch_assoc();
            
            return (int)$group['admin_access_group_id'];
        }
        
        return false;
    }
    
    
    /**
     * Returns the id of an existing Admin Access Group identified by a type and the identifier.
     *
     * @param string $type       Type of this identifier. Should be "PAGE", "CONTROLLER" or "AJAX_HANDLER".
     * @param string $identifier Identifier you are looking for. Should be the name of a controller or page.
     *
     * @return bool Return true on success and false on failure.
     */
    public function getAdminAccessGroupIdByName($name, $languageId = 2)
    {
        $query = sprintf('SELECT * FROM  `admin_access_group_descriptions` WHERE `name` = "%s" AND `language_id` = %s LIMIT 1;',
                         $this->real_escape_string($name),
                         (int)$languageId);
        
        $group = $this->query($query, true);
        if ($group !== null && $group->num_rows > 0) {
            $group = $group->fetch_assoc();
            
            return $group['admin_access_group_id'];
        }
        
        return false;
    }
    
    
    /**
     * Checks the deleting permission for a controller.
     *
     * @param string $identifier The name of a controller to identify an admin access group.
     * @param int    $customerId ID of a customer to check the permission for.
     *
     * @return bool True if customer has a deleting permission for the controller, false otherwise.
     */
    public function checkAdminAccessDeletingPermissionForController($identifier, $customerId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_group_items` `items` LEFT JOIN `admin_access_permissions` `permissions` ON `admin_access_group_id` = `permissions`.`admin_access_group_id` LEFT JOIN `admin_access_users` `users` ON `permissions`.`admin_access_role_id` = `users`.`admin_access_role_id` WHERE `items`.`identifier` = "%s" AND `items`.`type` = "%s" AND `users`.`customer_id` = %d;',
                         $this->real_escape_string($identifier),
                         'CONTROLLER',
                         (int)$customerId);
        
        $permission = $this->query($query, true);
        if ($permission !== null && $permission->num_rows > 0) {
            $permission = $permission->fetch_assoc();
            
            return (int)$permission['deleting_granted'] === 1;
        }
        
        return false;
    }
    
    
    /**
     * Checks the deleting permission for a page.
     *
     * @param string $identifier The name of a page to identify an admin access group.
     * @param int    $customerId ID of a customer to check permission for.
     *
     * @return bool True if customer has a deleting permission for the page, false otherwise.
     */
    public function checkAdminAccessDeletingPermissionForPage($identifier, $customerId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_group_items` `items` LEFT JOIN `admin_access_permissions` `permissions` ON `admin_access_group_id` = `permissions`.`admin_access_group_id` LEFT JOIN `admin_access_users` `users` ON `permissions`.`admin_access_role_id` = `users`.`admin_access_role_id` WHERE `items`.`identifier` = "%s" AND `items`.`type` = "%s" AND `users`.`customer_id` = %d;',
                         $this->real_escape_string($identifier),
                         'PAGE',
                         (int)$customerId);
        
        $permission = $this->query($query, true);
        if ($permission !== null && $permission->num_rows > 0) {
            $permission = $permission->fetch_assoc();
            
            return (int)$permission['deleting_granted'] === 1;
        }
        
        return false;
    }
    
    
    /**
     * Checks the deleting permission for an ajax handler.
     *
     * @param string $identifier The name of an ajax handler to identify an admin access group.
     * @param int    $customerId ID of a customer to check permission for.
     *
     * @return bool True if customer has a deleting permission for the ajax handler, false otherwise.
     */
    public function checkAdminAccessDeletingPermissionForAjaxHandler($identifier, $customerId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_group_items` `items` LEFT JOIN `admin_access_permissions` `permissions` ON `admin_access_group_id` = `permissions`.`admin_access_group_id` LEFT JOIN `admin_access_users` `users` ON `permissions`.`admin_access_role_id` = `users`.`admin_access_role_id` WHERE `items`.`identifier` = "%s" AND `items`.`type` = "%s" AND `users`.`customer_id` = %d;',
                         $this->real_escape_string($identifier),
                         'AJAX_HANDLER',
                         (int)$customerId);
        
        $permission = $this->query($query, true);
        if ($permission !== null && $permission->num_rows > 0) {
            $permission = $permission->fetch_assoc();
            
            return (int)$permission['deleting_granted'] === 1;
        }
        
        return false;
    }
    
    
    /**
     * Checks the reading permission for a controller.
     *
     * @param string $identifier The name of a controller to identify an admin access group.
     * @param int    $customerId ID of a customer to check the permission for.
     *
     * @return bool True if customer has a reading permission for the controller, false otherwise.
     */
    public function checkAdminAccessReadingPermissionForController($identifier, $customerId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_group_items` `items` LEFT JOIN `admin_access_permissions` `permissions` ON `admin_access_group_id` = `permissions`.`admin_access_group_id` LEFT JOIN `admin_access_users` `users` ON `permissions`.`admin_access_role_id` = `users`.`admin_access_role_id` WHERE `items`.`identifier` = "%s" AND `items`.`type` = "%s" AND `users`.`customer_id` = %d;',
                         $this->real_escape_string($identifier),
                         'CONTROLLER',
                         (int)$customerId);
        
        $permission = $this->query($query, true);
        if ($permission !== null && $permission->num_rows > 0) {
            $permission = $permission->fetch_assoc();
            
            return (int)$permission['reading_granted'] === 1;
        }
        
        return false;
    }
    
    
    /**
     * Checks the reading permission for a page.
     *
     * @param string $identifier The name of a page to identify an admin access group.
     * @param int    $customerId ID of a customer to check permission for.
     *
     * @return bool True if customer has a reading permission for the page, false otherwise.
     */
    public function checkAdminAccessReadingPermissionForPage($identifier, $customerId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_group_items` `items` LEFT JOIN `admin_access_permissions` `permissions` ON `admin_access_group_id` = `permissions`.`admin_access_group_id` LEFT JOIN `admin_access_users` `users` ON `permissions`.`admin_access_role_id` = `users`.`admin_access_role_id` WHERE `items`.`identifier` = "%s" AND `items`.`type` = "%s" AND `users`.`customer_id` = %d;',
                         $this->real_escape_string($identifier),
                         'PAGE',
                         (int)$customerId);
        
        $permission = $this->query($query, true);
        if ($permission !== null && $permission->num_rows > 0) {
            $permission = $permission->fetch_assoc();
            
            return (int)$permission['reading_granted'] === 1;
        }
        
        return false;
    }
    
    
    /**
     * Checks the reading permission for an ajax handler.
     *
     * @param string $identifier The name of an ajax handler to identify an admin access group.
     * @param int    $customerId ID of a customer to check permission for.
     *
     * @return bool True if customer has a reading permission for the ajax handler, false otherwise.
     */
    public function checkAdminAccessReadingPermissionForAjaxHandler($identifier, $customerId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_group_items` `items` LEFT JOIN `admin_access_permissions` `permissions` ON `admin_access_group_id` = `permissions`.`admin_access_group_id` LEFT JOIN `admin_access_users` `users` ON `permissions`.`admin_access_role_id` = `users`.`admin_access_role_id` WHERE `items`.`identifier` = "%s" AND `items`.`type` = "%s" AND `users`.`customer_id` = %d;',
                         $this->real_escape_string($identifier),
                         'AJAX_HANDLER',
                         (int)$customerId);
        
        $permission = $this->query($query, true);
        if ($permission !== null && $permission->num_rows > 0) {
            $permission = $permission->fetch_assoc();
            
            return (int)$permission['reading_granted'] === 1;
        }
        
        return false;
    }
    
    
    /**
     * Checks the writing permission for a controller.
     *
     * @param string $identifier The name of a controller to identify an admin access group.
     * @param int    $customerId ID of a customer to check the permission for.
     *
     * @return bool True if customer has a writing permission for the controller, false otherwise.
     */
    public function checkAdminAccessWritingPermissionForController($identifier, $customerId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_group_items` `items` LEFT JOIN `admin_access_permissions` `permissions` ON `admin_access_group_id` = `permissions`.`admin_access_group_id` LEFT JOIN `admin_access_users` `users` ON `permissions`.`admin_access_role_id` = `users`.`admin_access_role_id` WHERE `items`.`identifier` = "%s" AND `items`.`type` = "%s" AND `users`.`customer_id` = %d;',
                         $this->real_escape_string($identifier),
                         'CONTROLLER',
                         (int)$customerId);
        
        $permission = $this->query($query, true);
        if ($permission !== null && $permission->num_rows > 0) {
            $permission = $permission->fetch_assoc();
            
            return (int)$permission['writing_granted'] === 1;
        }
        
        return false;
    }
    
    
    /**
     * Checks the writing permission for a page.
     *
     * @param string $identifier The name of a page to identify an admin access group.
     * @param int    $customerId ID of a customer to check permission for.
     *
     * @return bool True if customer has a writing permission for the page, false otherwise.
     */
    public function checkAdminAccessWritingPermissionForPage($identifier, $customerId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_group_items` `items` LEFT JOIN `admin_access_permissions` `permissions` ON `admin_access_group_id` = `permissions`.`admin_access_group_id` LEFT JOIN `admin_access_users` `users` ON `permissions`.`admin_access_role_id` = `users`.`admin_access_role_id` WHERE `items`.`identifier` = "%s" AND `items`.`type` = "%s" AND `users`.`customer_id` = %d;',
                         $this->real_escape_string($identifier),
                         'PAGE',
                         (int)$customerId);
        
        $permission = $this->query($query, true);
        if ($permission !== null && $permission->num_rows > 0) {
            $permission = $permission->fetch_assoc();
            
            return (int)$permission['writing_granted'] === 1;
        }
        
        return false;
    }
    
    
    /**
     * Checks the writing permission for an ajax handler.
     *
     * @param string $identifier The name of an ajax handler to identify an admin access group.
     * @param int    $customerId ID of a customer to check permission for.
     *
     * @return bool True if customer has a writing permission for the ajax handler, false otherwise.
     */
    public function checkAdminAccessWritingPermissionForAjaxHandler($identifier, $customerId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_group_items` `items` LEFT JOIN `admin_access_permissions` `permissions` ON `admin_access_group_id` = `permissions`.`admin_access_group_id` LEFT JOIN `admin_access_users` `users` ON `permissions`.`admin_access_role_id` = `users`.`admin_access_role_id` WHERE `items`.`identifier` = "%s" AND `items`.`type` = "%s" AND `users`.`customer_id` = %d;',
                         $this->real_escape_string($identifier),
                         'AJAX_HANDLER',
                         (int)$customerId);
        
        $permission = $this->query($query, true);
        if ($permission !== null && $permission->num_rows > 0) {
            $permission = $permission->fetch_assoc();
            
            return (int)$permission['writing_granted'] === 1;
        }
        
        return false;
    }
    
    
    /**
     * Returns a collection of all permissions by a given role ID.
     *
     * @param int $roleId Role ID.
     *
     * @return mysqli_result
     */
    public function getAdminAccessPermissionsByRoleId($roleId)
    {
        $query = sprintf('SELECT `permissions`.* FROM `admin_access_permissions` `permissions` WHERE `permissions`.`admin_access_role_id` = %d;',
                         (int)$roleId);
        
        return $this->query($query, true);
    }
    
    
    /**
     * Returns all roles of certain user by a given user ID.
     *
     * @param int $id User ID.
     *
     * @return mysqli_result
     */
    public function getAdminAccessRolesByCustomerId($customerId)
    {
        $query = sprintf('SELECT `roles`.* FROM `admin_access_users` `users` LEFT JOIN `admin_access_roles` `roles` ON `users`.`admin_access_role_id` = `roles`.`admin_access_role_id` WHERE `users`.`customer_id` = %d;',
                         (int)$customerId);
        
        return $this->query($query, true);
    }
    
    
    /**
     * Grants deleting permission to a role for a given group id.
     *
     * @param int $groupId Group ID to grant permission for.
     * @param int $roleId  Role ID to grant permission for.
     *
     * @return bool Return true on success and false on failure.
     */
    public function grantAdminAccessDeletingPermissionToRole($groupId, $roleId)
    {
        $query  = sprintf('UPDATE `admin_access_permissions` SET `deleting_granted` = "1" WHERE `admin_access_role_id` = %d AND `admin_access_group_id` = %d;',
                          (int)$roleId,
                          (int)$groupId);
        $result = $this->query($query, true);
        $error  = $result !== null && $result !== false;
        
        $parentGroupSql   = sprintf('SELECT * FROM `admin_access_groups` WHERE `admin_access_group_id` = (SELECT `parent_id` FROM `admin_access_groups` WHERE `admin_access_group_id` = %d);',
                                    (int)$groupId);
        $parentGroupQuery = $this->query($parentGroupSql, true);
        if ($parentGroupQuery !== null && $parentGroupQuery->num_rows > 0) {
            $parentGroup = $parentGroupQuery->fetch_assoc();
            if ((int)$parentGroup['admin_access_group_id'] > 0) {
                $error &= $this->grantAdminAccessDeletingPermissionToRole((int)$parentGroup['admin_access_group_id'],
                                                                          $roleId);
            }
        }
        
        return $error;
    }
    
    
    /**
     * Removes deleting permission from role for a given group id.
     *
     * @param int $groupId Group ID to remove permission for.
     * @param int $roleId  Role ID to remove permission from.
     *
     * @return bool Return true on success and false on failure.
     */
    public function removeAdminAccessDeletingPermissionFromRole($groupId, $roleId)
    {
        $query  = sprintf('UPDATE `admin_access_permissions` SET `deleting_granted` = "0" WHERE `admin_access_role_id` = %d AND `admin_access_group_id` = %d;',
                          (int)$roleId,
                          (int)$groupId);
        $result = $this->query($query, true);
        
        return $result !== null && $result !== false;
    }
    
    
    /**
     * Grants reading permission to a role for a given group id.
     *
     * @param int $groupId Group ID to grant permission for.
     * @param int $roleId  Role ID to grant permission for.
     *
     * @return bool Return true on success and false on failure.
     */
    public function grantAdminAccessReadingPermissionToRole($groupId, $roleId)
    {
        $query  = sprintf('UPDATE `admin_access_permissions` SET `reading_granted` = "1" WHERE `admin_access_role_id` = %d AND `admin_access_group_id` = %d;',
                          (int)$roleId,
                          (int)$groupId);
        $result = $this->query($query, true);
        $error  = $result !== null && $result !== false;
        
        $parentGroupSql   = sprintf('SELECT * FROM `admin_access_groups` WHERE `admin_access_group_id` = (SELECT `parent_id` FROM `admin_access_groups` WHERE `admin_access_group_id` = %d);',
                                    (int)$groupId);
        $parentGroupQuery = $this->query($parentGroupSql, true);
        if ($parentGroupQuery !== null && $parentGroupQuery->num_rows > 0) {
            $parentGroup = $parentGroupQuery->fetch_assoc();
            if ((int)$parentGroup['admin_access_group_id'] > 0) {
                $error &= $this->grantAdminAccessReadingPermissionToRole((int)$parentGroup['admin_access_group_id'],
                                                                         $roleId);
            }
        }
        
        return $error;
    }
    
    
    /**
     * Removes reading permission from role for a given group id.
     *
     * @param int $groupId Group ID to remove permission for.
     * @param int $roleId  Role ID to remove permission from.
     *
     * @return bool Return true on success and false on failure.
     */
    public function removeAdminAccessReadingPermissionFromRole($groupId, $roleId)
    {
        $query  = sprintf('UPDATE `admin_access_permissions` SET `reading_granted` = "0" WHERE `admin_access_role_id` = %d AND `admin_access_group_id` = %d;',
                          (int)$roleId,
                          (int)$groupId);
        $result = $this->query($query, true);
        
        return $result !== null && $result !== false;
    }
    
    
    /**
     * Grants writing permission to a role for a given group id.
     *
     * @param int $groupId Group ID to grant permission for.
     * @param int $roleId  Role ID to grant permission for.
     *
     * @return bool Return true on success and false on failure.
     */
    public function grantAdminAccessWritingPermissionToRole($groupId, $roleId)
    {
        $query  = sprintf('UPDATE `admin_access_permissions` SET `writing_granted` = "1" WHERE `admin_access_role_id` = %d AND `admin_access_group_id` = %d;',
                          (int)$roleId,
                          (int)$groupId);
        $result = $this->query($query, true);
        $error  = $result !== null && $result !== false;
        
        $parentGroupSql   = sprintf('SELECT * FROM `admin_access_groups` WHERE `admin_access_group_id` = (SELECT `parent_id` FROM `admin_access_groups` WHERE `admin_access_group_id` = %d);',
                                    (int)$groupId);
        $parentGroupQuery = $this->query($parentGroupSql, true);
        if ($parentGroupQuery !== null && $parentGroupQuery->num_rows > 0) {
            $parentGroup = $parentGroupQuery->fetch_assoc();
            if ((int)$parentGroup['admin_access_group_id'] > 0) {
                $error &= $this->grantAdminAccessWritingPermissionToRole((int)$parentGroup['admin_access_group_id'],
                                                                         $roleId);
            }
        }
        
        return $error;
    }
    
    
    /**
     * Removes writing permission from role for a given group id.
     *
     * @param int $groupId Group ID to remove permission for.
     * @param int $roleId  Role ID to remove permission from.
     *
     * @return bool Return true on success and false on failure.
     */
    public function removeAdminAccessWritingPermissionFromRole($groupId, $roleId)
    {
        $query  = sprintf('UPDATE `admin_access_permissions` SET `writing_granted` = "0" WHERE `admin_access_role_id` = %d AND `admin_access_group_id` = %d;',
                          (int)$roleId,
                          (int)$groupId);
        $result = $this->query($query, true);
        
        return $result !== null && $result !== false;
    }
    
    
    /**
     * Deletes role by a given role ID.
     *
     * @param int $roleId ID of the role that should be deleted.
     *
     * @return bool Return true on success and false on failure.
     */
    public function deleteAdminAccessRoleById($roleId)
    {
        $query  = sprintf('DELETE FROM `admin_access_roles` WHERE `admin_access_role_id` = %d;', (int)$roleId);
        $result = $this->query($query, true);
        $error  = $result !== null && $result !== false;
        
        $query  = sprintf('DELETE FROM `admin_access_role_descriptions` WHERE `admin_access_role_id` = %d;',
                          (int)$roleId);
        $result = $this->query($query, true);
        $error  &= $result !== null && $result !== false;
        
        $query  = sprintf('DELETE FROM `admin_access_users` WHERE `admin_access_role_id` = %d;', (int)$roleId);
        $result = $this->query($query, true);
        $error  &= $result !== null && $result !== false;
        
        $query  = sprintf('DELETE FROM `admin_access_permissions` WHERE `admin_access_role_id` = %d;', (int)$roleId);
        $result = $this->query($query, true);
        $error  &= $result !== null && $result !== false;
        
        return $error;
    }
    
    
    /**
     * Returns a collection of all roles.
     *
     * @return mysqli_result
     */
    public function getAllAdminAccessRoles()
    {
        return $this->query('SELECT `roles`.* FROM `admin_access_roles` `roles`;', true);
    }
    
    
    /**
     * Deletes an admin access user by a given customer ID.
     *
     * @param int $customerId ID of the user that should be deleted.
     *
     * @return bool Return true on success and false on failure.
     */
    public function deleteAdminAccessUserByCustomerId($customerId)
    {
        $query  = sprintf('DELETE FROM `admin_access_users` WHERE `customer_id` = %d;', (int)$customerId);
        $result = $this->query($query, true);
        
        return $result !== null && $result !== false;
    }
    
    
    /**
     * Returns a role by a given role ID.
     *
     * @param int $roleId ID of the requested role.
     *
     * @return mysqli_result
     */
    public function getAdminAccessRoleById($roleId)
    {
        $query = sprintf('SELECT `roles`.* FROM `admin_access_roles` `roles` WHERE `roles`.`admin_access_role_id` = %d;',
                         (int)$roleId);
        
        return $this->query($query, true);
    }
    
    
    /**
     * Returns a collection of all groups.
     *
     * @return mysqli_result
     */
    public function getAllAdminAccessGroups()
    {
        return $this->query('SELECT `groups`.* FROM `admin_access_groups` `groups`;', true);
    }
    
    
    /**
     * Returns a group by a given group id.
     *
     * @param int $id Group id.
     *
     * @return mysqli_result
     */
    public function getAdminAccessGroupById($groupId)
    {
        $query = sprintf('SELECT `groups`.* FROM `admin_access_groups` `groups` WHERE `groups`.`admin_access_group_id` = %d;',
                         (int)$groupId);
        
        return $this->query($query, true);
    }
    
    
    /**
     * Deletes a group by a given group ID.
     *
     * @param int $id ID of the group that should be deleted.
     *
     * @return bool Return true on success and false on failure.
     */
    public function deleteAdminAccessGroupById($groupId)
    {
        $query = sprintf('DELETE FROM `admin_access_groups` WHERE `admin_access_group_id` = %d;', (int)$groupId);
        $error = $this->query($query) !== false;
        
        $query = sprintf('DELETE FROM `admin_access_group_items` WHERE `admin_access_group_id` = %d;', (int)$groupId);
        $error &= $this->query($query) !== false;
        
        $query = sprintf('DELETE FROM `admin_access_group_descriptions` WHERE `admin_access_group_id` = %d;',
                         (int)$groupId);
        $error &= $this->query($query) !== false;
        
        $query = sprintf('DELETE FROM `admin_access_permissions` WHERE `admin_access_group_id` = %d;', (int)$groupId);
        $error &= $this->query($query) !== false;
        
        return $error;
    }
	
	
	/**
	 * @param $mysqli
	 */
	public function reset_mysqli($mysqli)
	{
		$this->coo_mysqli = $mysqli;
		self::$mysqli     = $mysqli;
		$this->sql_errors = array();
	}
    
    
    /**
     * @return bool
     */
    public function gxConfigurationTableExists()
    {
        return $this->table_exists('gx_configurations');
    }
    
    
    /**
     * @param string $key
     *
     * @return mysqli_result
     */
    public function queryConfiguration($key)
    {
        if ($this->gxConfigurationTableExists()) {
            return $this->query("SELECT `value` FROM `gx_configurations` WHERE `key` LIKE 'configuration/$key'",
                                true);
        }
        
        return $this->query("SELECT `configuration_value` AS 'value' FROM `configuration` WHERE `configuration_key` LIKE '$key'",
                            true);
    }
    
    
    /**
     * @param $key
     *
     * @return string
     */
    public function getConfiguration($key)
    {
        $result = $this->queryConfiguration($key);
    
        return $this->getConfigurationValueOrFallback($result);
    }
    
    
    /**
     * @param $key
     * @param $value
     */
    public function replaceConfiguration($key, $value)
    {
        $this->deleteConfiguration($key);
        $this->insertConfiguration($key, $value);
    }
    
    
    /**
     * @param $key
     */
    public function deleteConfiguration($key)
    {
        if ($this->gxConfigurationTableExists()) {
            $this->query('DELETE FROM `gx_configurations` WHERE `key` LIKE "configuration/'
                         . $this->real_escape_string($key) . '"');
            
            return;
        }
        
        $this->query('DELETE FROM `configuration` WHERE `configuration_key` LIKE "' . $this->real_escape_string($key)
                     . '"');
    }
    
    
    /**
     * @param $key
     * @param $value
     */
    public function insertConfiguration($key, $value)
    {
        if ($this->gxConfigurationTableExists()) {
            $this->query("
                INSERT INTO
                    `gx_configurations`
                SET
                    `key`	= 'configuration/" . $this->real_escape_string($key) . "',
                    `value`	= '" . $this->real_escape_string($value) . "'");
            
            return;
        }
        
        $this->query("
            INSERT INTO
                `configuration`
            SET
                `configuration_key`	= '" . $this->real_escape_string($key) . "',
                `configuration_value`	= '" . $this->real_escape_string($value) . "'");
    }
    
    
    /**
     * @param string $key
     *
     * @return mysqli_result
     */
    public function queryGmConfiguration($key)
    {
        if ($this->gxConfigurationTableExists()) {
            return $this->query("SELECT `value` FROM `gx_configurations` WHERE `key` LIKE 'gm_configuration/$key'",
                                true);
        }
        
        return $this->query("SELECT `gm_value` AS 'value' FROM `gm_configuration` WHERE `gm_key` LIKE '$key'",
                            true);
    }
    
    
    /**
     * @param $key
     *
     * @return string
     */
    public function getGmConfiguration($key)
    {
        $result = $this->queryGmConfiguration($key);
        
        return $this->getConfigurationValueOrFallback($result);
    }
    
    
    /**
     * @param $key
     * @param $value
     * @param $type
     */
    public function replaceGmConfiguration($key, $value, $type = '')
    {
        // REPLACE INTO not possible in old shops because of missing UNIQUE KEY
        $this->deleteGmConfiguration($key);
        $this->insertGmConfiguration($key, $value, $type);
    }
    
    
    /**
     * @param $key
     */
    public function deleteGmConfiguration($key)
    {
        if ($this->gxConfigurationTableExists()) {
            $this->query('DELETE FROM `gx_configurations` WHERE `key` LIKE "gm_configuration/'
                         . $this->real_escape_string($key) . '"');
            
            return;
        }
        
        $this->query('DELETE FROM `gm_configuration` WHERE `gm_key` LIKE "' . $this->real_escape_string($key) . '"');
    }
    
    
    /**
     * @param $key
     * @param $value
     * @param $type
     */
    public function insertGmConfiguration($key, $value, $type = '')
    {
        if ($this->gxConfigurationTableExists()) {
            $this->query("
                INSERT INTO
                    `gx_configurations`
                SET
                    `key`	= 'gm_configuration/" . $this->real_escape_string($key) . "',
                    `value`	= '" . $this->real_escape_string($value) . "',
                    `type`	= '" . $this->real_escape_string($type) . "'");
    
            return;
        }
    
        $this->query("
            INSERT INTO
                `gm_configuration`
            SET
                `gm_key`	= '" . $this->real_escape_string($key) . "',
                `gm_value`	= '" . $this->real_escape_string($value) . "'");
    }
    
    
    /**
     * @param mysqli_result $result
     * @param string        $fallback
     *
     * @return string
     */
    public function getConfigurationValueOrFallback($result, $fallback = '')
    {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (isset($row['value'])) {
                return $row['value'];
            }
        }
        
        return $fallback;
    }


    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->query("SELECT * FROM `languages`");
    }
}