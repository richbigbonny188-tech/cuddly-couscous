<?php
/*--------------------------------------------------------------
   GMGPrintCartManager.php 2020-12-22
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

class GMGPrintCartManager_ORIGIN
{
    /**
     * @var float
     */
    protected $maxFileSize = -1.0;
    
	/**
	 * @var array
	 */
	public $v_elements = array();
	
	/**
	 * @var array
	 */
	public $v_files = array();
	
	
	/**
	 * GMGPrintCartManager_ORIGIN constructor.
	 */
	function __construct()
	{
		$this->restore();
	}
	
	
	/**
	 * @param $productId
	 * @param $elementId
	 * @param $value
	 */
	public function add($productId, $elementId, $value)
	{
		$this->v_elements[$productId][$elementId] = $this->correct_length($elementId, $value);
	}
	
	
	/**
	 * @param $elementId
	 * @param $value
	 *
	 * @return bool|mixed|string
	 */
	public function correct_length($elementId, $value)
	{
		$elementId = (int)$elementId;
		$newValue  = $value;
		
		$t_get_max_characters = xtc_db_query("SELECT e.max_characters
												FROM 
													" . TABLE_GM_GPRINT_ELEMENTS . " e,
													" . TABLE_GM_GPRINT_ELEMENTS_GROUPS . " g
												WHERE
													e.gm_gprint_elements_id = '" . $elementId . "'
													AND e.gm_gprint_elements_groups_id = g.gm_gprint_elements_groups_id
													AND (g.group_type = 'text_input' OR g.group_type = 'textarea')");
		if(xtc_db_num_rows($t_get_max_characters) == 1)
		{
			$maxCharacters = xtc_db_fetch_array($t_get_max_characters);
			$maxCharacters = (int)$maxCharacters['max_characters'];
			
			if($maxCharacters > 0)
			{
				if(gm_get_conf('GM_GPRINT_EXCLUDE_SPACES') === '1')
				{
					$newValue = str_replace(' ', '', $newValue);
				}
				$newValue = str_replace("\n", '', $newValue);
				$newValue = str_replace("\r", '', $newValue);
				$newValue = str_replace("\t", '', $newValue);
				$newValue = str_replace("\v", '', $newValue);
				
				if(strlen_wrapper($newValue) > $maxCharacters)
				{
					$newValue = substr($value, 0, (strlen_wrapper($newValue) - $maxCharacters) * -1);
				}
				else
				{
					$newValue = $value;
				}
			}
		}
		
		return $newValue;
	}
	
	
	/**
	 * @param $productId
	 * @param $elementId
	 * @param $filename
	 *
	 * @return string
	 */
	public function add_file($productId, $elementId, $filename)
	{
		$customerId = (int)$_SESSION['customer_id'];
		$filename   = basename($filename);
		
		$randomFilename = rand(10000000, 99999999);
		$downloadKey    = md5(time() . $randomFilename);
		
		if($_SERVER['HTTP_X_FORWARDED_FOR'])
		{
			$customerIpHash = md5($_SERVER['HTTP_X_FORWARDED_FOR']);
		}
		else
		{
			$customerIpHash = md5($_SERVER['REMOTE_ADDR']);
		}
		
		$result   = xtc_db_query("INSERT INTO " . TABLE_GM_GPRINT_UPLOADS . "
									SET 
										datetime = NOW(),
										customers_id = '" . $customerId . "',
										filename = '" . xtc_db_input($filename) . "',
										download_key = '" . $downloadKey . "',
										ip_hash = '" . $customerIpHash . "'");
		$uploadId = xtc_db_insert_id($result);
		$filename = $uploadId . '_' . $randomFilename;
		
		xtc_db_query("UPDATE " . TABLE_GM_GPRINT_UPLOADS . "
											SET encrypted_filename = '" . $filename . "'
											WHERE gm_gprint_uploads_id = '" . $uploadId . "'");
		
		$this->v_files[$productId][$elementId] = $uploadId;
		
		return $filename;
	}
	
	
	/**
	 * @param $elementId
	 *
	 * @return string
	 */
	public function get_allowed_extensions($elementId)
	{
		$elementId         = (int)$elementId;
		$allowedExtensions = '';
		
		$result = xtc_db_query("SELECT allowed_extensions
								FROM " . TABLE_GM_GPRINT_ELEMENTS . "
								WHERE gm_gprint_elements_id = '" . $elementId . "'");
		if(xtc_db_num_rows($result) === 1)
		{
			$row               = xtc_db_fetch_array($result);
			$allowedExtensions = $row['allowed_extensions'];
		}
		
		return $allowedExtensions;
	}
	
	
	/**
	 * @param $elementId
	 *
	 * @return float|int
	 */
	public function get_minimum_filesize($elementId)
	{
		$elementId       = (int)$elementId;
		$minimumFilesize = 0;
		
		$result = xtc_db_query("SELECT minimum_filesize
								FROM " . TABLE_GM_GPRINT_ELEMENTS . "
								WHERE gm_gprint_elements_id = '" . $elementId . "'");
		if(xtc_db_num_rows($result) === 1)
		{
			$row             = xtc_db_fetch_array($result);
			$minimumFilesize = (double)$row['minimum_filesize'];
		}
		
		return $minimumFilesize;
	}
	
	
	/**
	 * @param $elementId
	 *
	 * @return float|int
	 */
	public function get_maximum_filesize($elementId)
	{
		$elementId       = (int)$elementId;
		$maximumFilesize = 0;
		
		$result = xtc_db_query("SELECT maximum_filesize
								FROM " . TABLE_GM_GPRINT_ELEMENTS . "
								WHERE gm_gprint_elements_id = '" . $elementId . "'");
		if(xtc_db_num_rows($result) === 1)
		{
			$row             = xtc_db_fetch_array($result);
			$maximumFilesize = (double)$row['maximum_filesize'];
		}
		
		return $maximumFilesize > 0 ? $maximumFilesize : $this->fileUploadMaxSize();
	}
	
	
	/**
	 * @param $productId
	 * @param $elementId
	 * @param $uploadId
	 */
	public function restore_file($productId, $elementId, $uploadId)
	{
		$this->v_files[$productId][$elementId] = $uploadId;
	}
	
	
	public function save()
	{
		$customerId = (int)$_SESSION['customer_id'];
		
		if($customerId > 0)
		{
			xtc_db_query("DELETE FROM " . TABLE_GM_GPRINT_CART_ELEMENTS . "
												WHERE customers_id = '" . $customerId . "'");
			
			foreach($this->v_elements AS $t_products_id => $t_element)
			{
				foreach($this->v_elements[$t_products_id] AS $elementId => $elementValue)
				{
					
					if(isset($this->v_files[$t_products_id][$elementId]))
					{
						$uploadId    = (int)$this->v_files[$t_products_id][$elementId];
						$uploadIdSql = ", gm_gprint_uploads_id = '" . $uploadId . "'";
					}
					else
					{
						$uploadIdSql = '';
					}
					
					$elementId    = (int)$elementId;
					$productId    = gm_string_filter($t_products_id, '0-9{}x');
					$elementValue = ((isset($GLOBALS["___mysqli_ston"])
					                  && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],
					                                                                                        stripslashes($elementValue)) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.",
					                                                                                                                                       E_USER_ERROR)) ? "" : ""));
					
					xtc_db_query("INSERT INTO " . TABLE_GM_GPRINT_CART_ELEMENTS . "
									SET gm_gprint_elements_id = '" . $elementId . "',
										products_id = '" . $productId . "',
										customers_id = '" . $customerId . "',
										elements_value = '" . $elementValue . "'
										" . $uploadIdSql . "");
				}
			}
		}
	}
	
	
	/**
	 * @param $productId
	 */
	public function remove($productId)
	{
		$customerId = (int)$_SESSION['customer_id'];
		$productId  = gm_prepare_string($productId);
		
		unset($this->v_elements[$productId]);
		unset($this->v_files[$productId]);
		
		xtc_db_query("DELETE FROM " . TABLE_GM_GPRINT_CART_ELEMENTS . "
						WHERE 
							customers_id = '" . $customerId . "' AND 
							products_id = '" . $productId . "'");
	}
	
	
	public function restore()
	{
		$this->clean_up();
		
		$customerId = (int)$_SESSION['customer_id'];
		
		$oldProductIds = array();
		
		if($customerId > 0)
		{
			$getCustomersCart = xtc_db_query("SELECT
														gm_gprint_elements_id,
														products_id,
														elements_value,
														gm_gprint_uploads_id
													FROM " . TABLE_GM_GPRINT_CART_ELEMENTS . "
													WHERE customers_id = '" . $customerId . "'
													ORDER BY gm_gprint_elements_id");
			while($customerCart = xtc_db_fetch_array($getCustomersCart))
			{
				$newProductId = $this->check_cart($customerCart['products_id'], 'cart');
				
				if($newProductId !== false)
				{
					if(!in_array($customerCart['products_id'], $oldProductIds))
					{
						$oldProductIds[] = $customerCart['products_id'];
					}
					
					$this->add($newProductId, $customerCart['gm_gprint_elements_id'], $customerCart['elements_value']);
					
					if($customerCart['gm_gprint_uploads_id'] > 0)
					{
						$this->restore_file($newProductId, $customerCart['gm_gprint_elements_id'],
						                    $customerCart['gm_gprint_uploads_id']);
					}
				}
				else
				{
					$this->add($customerCart['products_id'], $customerCart['gm_gprint_elements_id'],
					           $customerCart['elements_value']);
					
					if($customerCart['gm_gprint_uploads_id'] > 0)
					{
						$this->restore_file($customerCart['products_id'], $customerCart['gm_gprint_elements_id'],
						                    $customerCart['gm_gprint_uploads_id']);
					}
				}
			}
			
			for($i = 0; $i < count($oldProductIds); $i++)
			{
				$this->remove($oldProductIds[$i]);
			}
			
			$this->save();
		}
		else
		{
			foreach($this->v_elements AS $t_products_id => $t_content)
			{
				$this->check_cart($t_products_id, 'cart');
			}
		}
	}
	
	
	public function clean_up()
	{
		$getOldData = xtc_db_query("SELECT DISTINCT products_id
										FROM " . TABLE_GM_GPRINT_CART_ELEMENTS);
		while($oldData = xtc_db_fetch_array($getOldData))
		{
			$productId = (int)xtc_get_prid($oldData['products_id']);
			
			$check       = xtc_db_query("SELECT COUNT(*) AS count
										FROM " . TABLE_GM_GPRINT_SURFACES_GROUPS_TO_PRODUCTS . "
										WHERE products_id = '" . $productId . "'");
			$checkResult = xtc_db_fetch_array($check);
			
			if($checkResult['count'] == 0)
			{
				$product = gm_string_filter($oldData['products_id'], '0-9{}x');
				
				xtc_db_query("DELETE FROM " . TABLE_GM_GPRINT_CART_ELEMENTS . "
											WHERE products_id = '" . $product . "'");
				
				unset($this->v_elements[$product]);
				unset($this->v_files[$product]);
			}
		}
		
		$getOldData = xtc_db_query("SELECT 
											c.gm_gprint_cart_elements_id, 
											c.gm_gprint_elements_id, 
											c.products_id,
											e.gm_gprint_elements_id
										FROM
											" . TABLE_GM_GPRINT_CART_ELEMENTS . " c
										LEFT JOIN	" . TABLE_GM_GPRINT_ELEMENTS . " AS e USING (gm_gprint_elements_id)
										WHERE e.gm_gprint_elements_id IS NULL");
		while($oldData = xtc_db_fetch_array($getOldData))
		{
			xtc_db_query("DELETE FROM " . TABLE_GM_GPRINT_CART_ELEMENTS . "
										WHERE gm_gprint_cart_elements_id = '" . $oldData['gm_gprint_cart_elements_id']
			             . "'");
			unset($this->v_elements[$oldData['products_id']][$oldData['gm_gprint_wishlist_elements_id']]);
			unset($this->v_files[$oldData['products_id']][$oldData['gm_gprint_wishlist_elements_id']]);
		}
		
		$this->clean_up_uploads();
	}
	
	
	public function empty_cart()
	{
		$customerId = (int)$_SESSION['customer_id'];
		
		xtc_db_query("DELETE FROM " . TABLE_GM_GPRINT_CART_ELEMENTS . "
										WHERE customers_id = '" . $customerId . "'");
		
		$this->v_elements = array();
		$this->v_files    = array();
	}
	
	
	/**
	 * @param      $elementId
	 * @param      $product
	 * @param bool $decryptedFilename
	 *
	 * @return bool
	 */
	public function get_filename($elementId, $product, $decryptedFilename = false)
	{
		$filename   = false;
		$customerId = (int)$_SESSION['customer_id'];
		$product    = gm_prepare_string($product);
		
		if(isset($_SESSION['coo_gprint_cart']->v_files[$product][$elementId]))
		{
			$uploadId = (int)$_SESSION['coo_gprint_cart']->v_files[$product][$elementId];
			
			$getFilename = xtc_db_query("SELECT
												filename,
												encrypted_filename
											FROM " . TABLE_GM_GPRINT_UPLOADS . "
											WHERE
												gm_gprint_uploads_id = '" . $uploadId . "'
												AND customers_id = '" . $customerId . "'");
			if(xtc_db_num_rows($getFilename) == 1)
			{
				$file     = xtc_db_fetch_array($getFilename);
				$filename = $file['encrypted_filename'];
				if($decryptedFilename)
				{
					$filename = $file['filename'];
				}
			}
		}
		
		return $filename;
	}
	
	
	/**
	 * @param      $product
	 * @param      $source
	 * @param bool $fix
	 *
	 * @return bool|int|string
	 */
	public function check_cart($product, $source, $fix = true)
	{
		$countFound = 0;
		$newKey     = false;
		
		$filteredProduct = str_replace('}', '{', $product);
		$filteredProduct = explode('{', $filteredProduct);
		
		if($source === 'cart' && !empty($filteredProduct))
		{
			foreach($_SESSION['cart']->contents AS $productId => $content)
			{
				$productIds = str_replace('}', '{', $productId);
				$productIds = explode('{', $productIds);
				
				if($filteredProduct[0] === $productIds[0])
				{
					for($i = 1; $i < count($filteredProduct); $i += 2)
					{
						for($j = 1; $j < count($productIds); $j += 2)
						{
							if($filteredProduct[$i] === $productIds[$j]
							   && $filteredProduct[$i + 1] === $productIds[$j + 1])
							{
								$countFound++;
							}
						}
					}
				}
				
				if($countFound === (count($filteredProduct) - 1) / 2)
				{
					$newKey = $productId;
					
					if($product !== $newKey && $fix === true)
					{
						$this->fix_product_key($product, $newKey);
					}
					elseif($fix === true)
					{
						$newKey = false;
					}
				}
				
				$countFound = 0;
			}
		}
		elseif($source === 'coo_gprint_cart' && !empty($filteredProduct))
		{
			foreach($_SESSION['coo_gprint_cart']->v_elements AS $productId => $content)
			{
				$productIds = str_replace('}', '{', $productId);
				$productIds = explode('{', $productIds);
				
				if($filteredProduct[0] === $productIds[0])
				{
					for($i = 1; $i < count($filteredProduct); $i = $i + 2)
					{
						for($j = 1; $j < count($productIds); $j = $j + 2)
						{
							if($filteredProduct[$i] === $productIds[$j]
							   && $filteredProduct[$i + 1] === $productIds[$j + 1])
							{
								$countFound++;
							}
						}
					}
				}
				
				if($countFound === (count($filteredProduct) - 1) / 2)
				{
					$newKey = $productId;
					
					if($productId !== $product && $fix === true)
					{
						$this->fix_product_key($productId, $product);
					}
					elseif($fix === true)
					{
						$newKey = false;
					}
				}
				
				$countFound = 0;
			}
		}
		else
		{
			$newKey = false;
		}
		
		return $newKey;
	}
	
	
	/**
	 * @param $oldKey
	 * @param $newKey
	 */
	public function fix_product_key($oldKey, $newKey)
	{
		$customerId = (int)$_SESSION['customer_id'];
		$oldKey     = gm_string_filter($oldKey, '0-9{}x');
		$newKey     = gm_string_filter($newKey, '0-9{}x');
		
		if($customerId > 0)
		{
			xtc_db_query("UPDATE " . TABLE_GM_GPRINT_CART_ELEMENTS . " 
							SET products_id = '" . $newKey . "'
							WHERE 
								products_id = '" . $oldKey . "'
								AND customers_id = '" . $customerId . "'");
		}
		
		if(isset($this->v_elements[$oldKey]))
		{
			$this->v_elements[$newKey] = $this->v_elements[$oldKey];
			unset($this->v_elements[$oldKey]);
		}
		
		if(isset($this->v_files[$oldKey]))
		{
			$this->v_files[$newKey] = $this->v_files[$oldKey];
			unset($this->v_files[$oldKey]);
		}
	}
	
	
	public function clean_up_uploads()
	{
        $cacheFile   = DIR_FS_CATALOG . 'cache/clean_up_uploads';
        
        if ($this->fileIsOlderThanOneDay($cacheFile) === false) {
            
            return;
        }
        
        $fileManager = new GMGPrintFileManager();
		
		// get unused uploads older than 1 day
		$query  = "SELECT DISTINCT
						u.gm_gprint_uploads_id,
						u.encrypted_filename
					FROM
						" . TABLE_GM_GPRINT_UPLOADS . " u
					LEFT JOIN " . TABLE_GM_GPRINT_CART_ELEMENTS . " AS c ON (u.gm_gprint_uploads_id = c.gm_gprint_uploads_id)
					LEFT JOIN " . TABLE_GM_GPRINT_ORDERS_ELEMENTS . " AS o ON (u.gm_gprint_uploads_id = o.gm_gprint_uploads_id)
					LEFT JOIN " . TABLE_GM_GPRINT_WISHLIST_ELEMENTS . " AS w ON (u.gm_gprint_uploads_id = w.gm_gprint_uploads_id)
					LEFT JOIN " . TABLE_WHOS_ONLINE . " AS wo ON (CONVERT(u.ip_hash USING utf8) = CONVERT(MD5(wo.ip_address) USING utf8))
					WHERE
						c.gm_gprint_uploads_id IS NULL AND
						o.gm_gprint_uploads_id IS NULL AND
						w.gm_gprint_uploads_id IS NULL AND
						wo.ip_address IS NULL AND
						u.datetime < DATE_SUB(NOW(), INTERVAL 1 DAY)";
		$result = xtc_db_query($query, 'db_link', false);
		while($row = xtc_db_fetch_array($result))
		{
			$filename = trim(basename($row['encrypted_filename']));
			if($filename !== '')
			{
				// delete file
				$fileManager->delete_file(DIR_FS_CATALOG . 'gm/customers_uploads/gprint/' . $filename);
			}
			
			// delete gm_print_uploads entity
			$deleteQuery = "DELETE FROM " . TABLE_GM_GPRINT_UPLOADS . " 
							WHERE gm_gprint_uploads_id = '" . (int)$row['gm_gprint_uploads_id'] . "'";
			xtc_db_query($deleteQuery);
		}
		
		touch($cacheFile);
	}
    
    /**
     * @return float
     */
    protected function fileUploadMaxSize(): float
    {
        if ($this->maxFileSize < 0) {
            
            $postMaxSize = $this->stringSizeToMegaBytesFloat(ini_get('post_max_size'));
            
            if ($postMaxSize > 0) {
                
                $this->maxFileSize = $postMaxSize;
            }
            
            $uploadMax = $this->stringSizeToMegaBytesFloat(ini_get('upload_max_filesize'));
            
            if ($uploadMax > 0 && $uploadMax < $this->maxFileSize) {
                
                $this->maxFileSize = $uploadMax;
            }
        }
        
        return $this->maxFileSize;
    }
    
    
    /**
     * @param string $size as number of bytes with unit suffix (e.g 1K, 8M, 5T)
     *
     * @return float An float representation of the $size
     */
    protected function stringSizeToMegaBytesFloat(string $size): float
    {
        // bkmgtpezy is the order of bytes, kilobytes, megabytes...
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\\.]/', '', $size);
    
        return $unit ? round($size * (1024 ** stripos('bkmgtpezy', $unit[0]))) / 1024 / 1024 : round($size);
    }
    
    /**
     * @param string $filepath
     *
     * @return bool
     */
    protected function fileIsOlderThanOneDay(string $filepath): bool
    {
        if (!file_exists($filepath)) {
            return true;
        }
        
        $fileAge   = filemtime($filepath);
        $yesterday = strtotime('-1 day');
        
        return $fileAge < $yesterday;
    }
}

MainFactory::load_origin_class('GMGPrintCartManager');
