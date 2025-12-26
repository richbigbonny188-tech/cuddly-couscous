<?php
/* --------------------------------------------------------------
  AutoUpdateChecker.inc.php 2018-08-31
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2018 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
 */

class AutoUpdateChecker
{
	/**
	 * @var \DatabaseModel
	 */
	private $db;
	
	
	public function __construct($db)
	{
		$this->db = $db;
	}
	
	
	public function isUpdateAvailable()
	{
		if(!$this->wasDataPrivacyAccepted())
		{
			return false;
		}
		
		$updateAvailable = false;
		$options         = array(
			CURLOPT_URL            => $this->getUpdateServerUrl(),
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => array(
				'shopVersion'       => $this->getInstalledVersion(),
				'shopUrl'           => HTTP_SERVER . DIR_WS_CATALOG,
				'shopKey'           => $this->getShopKey(),
				'versionHistory'    => json_encode($this->getVersionHistory()),
				'versionReceipts'   => json_encode($this->getVersionReceipts()),
				'downloadedUpdates' => json_encode(array()),
			),
		);
		
		$curlHandle = @curl_init();
		@curl_setopt_array($curlHandle, $options);
		$response = @curl_exec($curlHandle);
		$header   = @curl_getinfo($curlHandle);
		if(isset($header['http_code']) && (int)$header['http_code'] === 200)
		{
			$body            = json_decode($response, true);
			$updateAvailable = isset($body['updates']) && count($body['updates']) > 0;
		}
		@curl_close($curlHandle);
		
		return $updateAvailable;
	}
	
	
	private function getUpdateServerUrl()
	{
		/** @var \mysqli_result $result */
		$result = $this->db->query("SELECT `value` FROM `gx_configurations` WHERE `key` = 'gm_configuration/AUTO_UPDATER_UPDATES_URL'",
		                           true);
		
		if($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			return isset($row['gm_value'])? $row['gm_value'] : 'https://updates.gambio-support.de/v2/check.php';
		}
		
		return 'https://updates.gambio-support.de/v2/check.php';
	}
	
	
	private function getInstalledVersion()
	{
		/** @var \mysqli_result $result */
		$result = $this->db->query("SELECT `value` FROM `gx_configurations` WHERE `key` = 'gm_configuration/INSTALLED_VERSION'",
		                           true);
		
		if($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			return isset($row['value'])? $row['value'] : '0.0.0.0';
		}
		
		return '0.0.0.0';
	}
	
	
	private function getShopKey()
	{
		/** @var \mysqli_result $result */
		$result = $this->db->query("SELECT `value` FROM `gx_configurations` WHERE `key` = 'configuration/GAMBIO_SHOP_KEY'",
		                           true);
		
		if($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			return isset($row['configuration_value'])? $row['configuration_value'] : '';
		}
		
		return '';
	}
	
	
	private function wasDataPrivacyAccepted()
	{
		/** @var \mysqli_result $result */
		$result = $this->db->query("SELECT `value` FROM `gx_configurations` WHERE `key` = 'gm_configuration/AUTO_UPDATER_ACCEPT_DATA_PROCESSING'",
		                           true);
		
		if($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			return isset($row['gm_value']) && $row['gm_value'] !== 'true'? false : true;
		}
		
		return true;
	}
	
	
	private function getVersionHistory()
	{
		/** @var \mysqli_result $result */
		$result = $this->db->query("SELECT * FROM `version_history`", true);
		
		if($result->num_rows > 0)
		{
		    $resultArray = array();
		    while($row = $result->fetch_assoc())
            {
                $resultArray[] = $row;
            }
            
			return $resultArray;
		}
		
		return array();
	}
	
	
	private function getVersionReceipts()
	{
		return scandir(DIR_FS_CATALOG . 'version_info');
	}
}
