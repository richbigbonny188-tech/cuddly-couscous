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
	    $result = $this->db->queryGmConfiguration('AUTO_UPDATER_UPDATES_URL');
        return $this->db->getConfigurationValueOrFallback($result, 'https://updates.gambio-support.de/v2/check.php');
	}
	
	
	private function getInstalledVersion()
	{
        $result = $this->db->queryGmConfiguration('INSTALLED_VERSION');
        return $this->db->getConfigurationValueOrFallback($result, '0.0.0.0');
	}
	
	
	private function getShopKey()
	{
        return $this->db->getConfiguration('GAMBIO_SHOP_KEY');
	}
	
	
	private function wasDataPrivacyAccepted()
	{
        $result = $this->db->queryConfiguration('AUTO_UPDATER_ACCEPT_DATA_PROCESSING');
        return $this->db->getConfigurationValueOrFallback($result, true) !== 'false';
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