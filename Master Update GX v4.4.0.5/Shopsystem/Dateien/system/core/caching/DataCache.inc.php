<?php
/* --------------------------------------------------------------
   DataCache.inc.php 2020-09-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class DataCache
{
    public $v_cache_content_array = array();
    public $v_coo_error_log = NULL;

    public $v_cache_file_prefix = 'persistent_data_cache-';

    protected $v_persistence_index_array = array();
    protected $v_persistence_tags_array = array();
	
	protected static $isDataCacheActive                   = null;
	protected static $isPersistentDataCacheActive         = null;
	protected static $persistentDataCache                 = [];
	protected static $fileNamesOfPersistentCachesToUpdate = [];
    protected static $keyCache                            = [];
    protected static $cacheFiles                          = [];
    protected static $cacheFileNames                      = [];

    /*
    * constructor
    */
    protected function __construct()
    {
        self::$isPersistentDataCacheActive = self::$isPersistentDataCacheActive ?? !isset($GLOBALS['coo_debugger'])
                                                                                   || !is_object($GLOBALS['coo_debugger'])
                                                                                   || !$GLOBALS['coo_debugger']->is_enabled('DataCache_disable_persistent');
        self::$isDataCacheActive           = self::$isDataCacheActive ?? !isset($GLOBALS['coo_debugger'])
                                                                         || !is_object($GLOBALS['coo_debugger'])
                                                                         || !$GLOBALS['coo_debugger']->is_enabled('DataCache_disable_cache');
        $this->init_persistence_tags_array();
        $this->init_persistence_index_array();
    }
	
	
	/**
	 * To avoid too many filesystem operations, the persistent data cache is written during destruct
	 */
	public function __destruct()
	{
		foreach(self::$fileNamesOfPersistentCachesToUpdate as $fileName)
		{
			file_put_contents($this->get_cache_dir() . $fileName, serialize(self::$persistentDataCache[$fileName]));
		}
    }
    

    public static function &get_instance()
    {
        static $s_instance;

        if($s_instance === NULL)   {
            $s_instance = new DataCache();
        }
        return $s_instance;
    }

    protected function init_persistence_tags_array()
    {
        $this->v_persistence_tags_array = array(
            'CORE',
            'TEMPLATE',
            'CHECKOUT',
            'ADMIN'
        );
    }

    protected function init_persistence_index_array()
    {
        $t_index_file = $this->get_cache_dir() .'persistence_index';

        #cancel if cache file not found
        if(is_readable($t_index_file) == false) return NULL;

        #load cached object
        $t_data_serialized = file_get_contents($t_index_file);
        $coo_cached_data = unserialize($t_data_serialized);

        #cancel if unserialize was not successful
        if($coo_cached_data === false) return NULL;

        $this->v_persistence_index_array = $coo_cached_data;
    }

    public function persistence_tag_allowed($p_tag)
    {
        if(in_array($p_tag, $this->v_persistence_tags_array, true)) {
            # tag found + allowed
            return true;
        }
        # tag not found
        return false;
    }

    public function add_persistence_tag($p_key, $p_tags_array)
    {
        if(isset($this->v_persistence_index_array[$p_key]))
        {
            # init if key doesnt exist
            $this->v_persistence_index_array[$p_key] = array();
        }
        # set tag array
        $this->v_persistence_index_array[$p_key] = $p_tags_array;

        $this->write_persistence_index();
    }

    protected function write_persistence_index()
    {
        $t_index_file = $this->get_cache_dir() .'persistence_index';

        #serialize given data
        $t_data_serialized = serialize($this->v_persistence_index_array);

        if((file_exists($t_index_file) && is_writable($t_index_file)) || (!file_exists($t_index_file) && is_writable($this->get_cache_dir())))
        {
            #write data string to cache file
            file_put_contents($t_index_file, $t_data_serialized);
        }
        else
        {
            trigger_error($t_index_file . ' is not writable', E_USER_WARNING);
        }
    }

    public function get_cache_dir()
    {
        return DIR_FS_CATALOG . 'cache/';
    }

    protected function filter_key($p_key)
    {
        if (isset(self::$keyCache[$p_key])) {
            $key = self::$keyCache[$p_key];
        } else {
            $key                    = str_replace([
                                                      '~',
                                                      '"',
                                                      '#',
                                                      '%',
                                                      '&',
                                                      '*',
                                                      ':',
                                                      '<',
                                                      '>',
                                                      '?',
                                                      '/',
                                                      '\\',
                                                      '{',
                                                      '}',
                                                      '|',
                                                      "\b",
                                                      "\0",
                                                      "\t",
                                                      "\n"
                                                  ],
                                                  '',
                                                  $p_key);
            self::$keyCache[$p_key] = $key;
        }

        return $key;
    }

    public function set_data($p_key, $p_value, $p_persistent=false, $p_persistence_tags_array=false)
    {
        $this->v_cache_content_array[$p_key] = $p_value;

        if($p_persistent)
        {
            $this->write_persistent_data($p_key, $p_value);

            if($p_persistence_tags_array)
            {
                # tags given? add them
                $this->add_persistence_tag($p_key, $p_persistence_tags_array);
            }
        }
    }


    public function add_data($p_key, $p_value, $p_persistent=false, $p_persistence_tags_array=false)
    {
        if($p_persistent)
        {
            $value = $this->get_persistent_data($p_key) ?: [];
        }
        else
        {
            $value = $this->get_data($p_key) ?: [];
        }

        $value = array_merge($value, $p_value);

        $this->set_data($p_key, $value, $p_persistent, $p_persistence_tags_array);
    }


    public function get_data($p_key, $p_persistent=false)
    {
        $t_output = false;

        if($this->key_exists($p_key, $p_persistent) === false)
        {
            # need existing key, so trigger error
            trigger_error("key '$p_key' not found in DataCache", E_USER_ERROR);
        }
        else {
            # key found, return cached data
            $t_output = $this->v_cache_content_array[$p_key];
        }
        return $t_output;
    }

    public function key_exists($p_key, $p_persistent=false)
    {
        if(self::$isDataCacheActive === false){
            return false;
        }

        $t_output = false;

        if(array_key_exists($p_key, $this->v_cache_content_array))
        {
            return true;
        }

        #key not found in cache_content? try persistent?
        if($p_persistent === true)
        {
            $t_data = $this->get_persistent_data($p_key);
            if($t_data !== null)
            {
                #found persistent data, write to cache_content
                $this->set_data($p_key, $t_data);
                # key found. return true
                $t_output = true;
            }
        }
        return $t_output;
    }

    public function build_key($p_data)
    {
        return md5($p_data);
    }

    public function get_cache_file($key)
    {
        self::$cacheFiles[$key] = self::$cacheFiles[$key] ?? $this->get_cache_dir() . $this->get_cache_file_name($key);
        
        return self::$cacheFiles[$key];
    }
	
	protected function get_cache_file_name($key)
	{
        self::$cacheFileNames[$key] = self::$cacheFileNames[$key] ?? $key . '-' . $this->v_cache_file_prefix . LogControl::get_secure_token() . '.pdc';
        
        return self::$cacheFileNames[$key];
	}

    public function write_persistent_data($key, $data)
    {
        if (self::$isPersistentDataCacheActive === false){
            return false;
        }

        $key = $this->filter_key($key);
        
        $cacheFilePath = $this->get_cache_file($key);
        $cacheFileName = $this->get_cache_file_name($key);

        if((file_exists($cacheFilePath) && is_writable($cacheFilePath)) || (!file_exists($cacheFilePath) && is_writable($this->get_cache_dir())))
        {
            self::$persistentDataCache[$cacheFileName] = is_object($data) ? clone $data : $data;
	        if(!in_array($cacheFileName, self::$fileNamesOfPersistentCachesToUpdate, true))
	        {
		        self::$fileNamesOfPersistentCachesToUpdate[] = $cacheFileName;
	        }
        }
        else
        {
            trigger_error($cacheFilePath . ' is not writable', E_USER_WARNING);
        }
    }

    public function get_persistent_data($key)
    {
        $key = $this->filter_key($key);
        
	    $cacheFilePath = $this->get_cache_file($key);
	    $cacheFileName = $this->get_cache_file_name($key);
	
	    #load cached object
	    if(isset(self::$persistentDataCache[$cacheFileName]))
	    {
            $cachedData = self::$persistentDataCache[$cacheFileName];
	    }
	    elseif(is_readable($cacheFilePath))
	    {
            $cachedData = self::$persistentDataCache[$cacheFileName] = unserialize(file_get_contents($cacheFilePath));
	    }
	    else
	    {
		    return null;
	    }

	    #cancel if unserialize was not successful
	    if($cachedData === false) {
		    return null;
	    }
	    
	    if(is_object($cachedData)) {
            $cachedData = clone $cachedData;
        }

	    return $cachedData;
    }

    public function clear_cache_by_tag($p_cache_tag)
    {
        foreach($this->v_persistence_index_array as $t_cache_key => $t_cache_tags_array)
        {
            if(is_array($t_cache_tags_array) && in_array($p_cache_tag, $t_cache_tags_array))
            {
                $this->clear_cache($t_cache_key);
                $this->add_persistence_tag($t_cache_key, NULL);
            }
        }
    }

    public function clear_cache($key = null)
    {
	    if($key === null)
	    {
		    $key = '*';
	    }
	
        $searchPattern = $this->get_cache_file($key);
        $files         = glob($searchPattern);
	
	    if(is_array($files))
	    {
		    foreach($files as $filePath)
		    {
			    if(file_exists($filePath))
			    {
				    #delete found cache files
				    $unlink   = @unlink($filePath);
				    $fileName = basename($filePath);
				    
				    if(isset(self::$persistentDataCache[$fileName]))
				    {
					    unset(self::$persistentDataCache[$fileName]);
	                }

                    if (($arrayKey = array_search($fileName, self::$fileNamesOfPersistentCachesToUpdate, true))
                        !== false) {
                        unset(self::$fileNamesOfPersistentCachesToUpdate[$arrayKey]);
                    }
				    
				    if($unlink !== true)
				    {
					    trigger_error((string)$filePath . ' cannot be deleted', E_USER_WARNING);
				    }
			    }
		    }
	    }
    }
}
