<?php
/* --------------------------------------------------------------
   AutoUpdaterFactory.inc.php 2022-02-25
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2022 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class AutoUpdaterFactory
 */
class AutoUpdaterFactory
{
	/**
	 * @var \CI_DB_query_builder
	 */
	protected $db;
	
	/**
	 * @var \DataCache
	 */
	protected $dataCache;
	
	/**
	 * @var \LogControl
	 */
	protected $logControl;
	
	/**
	 * @var \AutoUpdaterSettings
	 */
	protected $settings;
	
	/**
	 * @var \AutoUpdaterCurlClient
	 */
	protected $curlClient;
	
	/**
	 * @var \AutoUpdaterDataCache
	 */
	protected $autoUpdaterDataCache;
	
	/**
	 * @var \AutoUpdaterBackupHelper
	 */
	protected $backupHelper;
	
	/**
	 * @var \AutoUpdaterUpdatesHelper
	 */
	protected $updatesHelper;
	
	/**
	 * @var \AutoUpdaterDownloadHelper
	 */
	protected $downloadHelper;
	
	/**
	 * @var \AutoUpdaterFtpManager
	 */
	protected $ftpManager;
	
	/**
	 * @var \AutoUpdater
	 */
	protected $autoUpdater;
	
	
	/**
	 * AutoUpdaterFactory constructor.
	 *
	 * @param \CI_DB_query_builder $db
	 * @param \DataCache           $dataCache
	 * @param \LogControl          $logControl
	 */
	public function __construct(\CI_DB_query_builder $db, \DataCache $dataCache, \LogControl $logControl)
	{
		$this->db         = $db;
		$this->dataCache  = $dataCache;
		$this->logControl = $logControl;
	}
	
	
	/**
	 * Creates and returns an new instance of the auto updater.
	 *
	 * @return \AutoUpdater
	 */
	public function createAutoUpdater()
	{
		if($this->autoUpdater === null)
		{
			$this->autoUpdater = new AutoUpdater($this->createSettings(), $this->createBackupHelper(),
			                                     $this->createUpdatesHelper(), $this->createDownloadHelper(),
			                                     $this->logControl);
		}
		
		return $this->autoUpdater;
	}
	
	
	/**
	 * Creates and returns an new instance of the auto updater ftp manager.
	 *
	 * @param string $protocol
	 * @param string $server
	 * @param string $login
	 * @param string $password
	 * @param string $port
	 * @param bool   $passive
	 *
	 * @return \AutoUpdaterFtpManager
	 *
	 * @throws \AutoUpdaterException
	 */
	public function createFtpManager($protocol,
	                                 $server,
	                                 $login,
	                                 $password,
	                                 $port,
	                                 $passive)
	{
		if($this->ftpManager === null)
		{
			if($protocol === 'ftp')
			{
				$filesystem = new AutoUpdaterFilesystem(new AutoUpdaterFtpAdapter([
					                                                                  'host'     => $server,
					                                                                  'username' => $login,
					                                                                  'password' => $password,
					                                                                  'passive'  => $passive,
					                                                                  'timeout'  => 10,
					                                                                  'root'     => '/',
				                                                                  ]));
			}
			else
			{
				$filesystem = new AutoUpdaterFilesystem(new AutoUpdaterSFtpAdapter([
					                                                                   'host'     => $server,
					                                                                   'port'     => $port,
					                                                                   'username' => $login,
					                                                                   'password' => $password,
					                                                                   'timeout'  => 10,
					                                                                   'root'     => '/',
				                                                                   ]));
			}
            
            $testValue = bin2hex(openssl_random_pseudo_bytes(16));
            $file      = DIR_FS_CATALOG . 'cache/auto-update-ftp-test' . bin2hex(openssl_random_pseudo_bytes(16));
            file_put_contents($file, $testValue);
            
            if (@$filesystem->has($file) === false || @$filesystem->read($file) !== $testValue) {
                unlink($file);
                throw new AutoUpdaterException('Could not create FTP Manager.');
            }
            unlink($file);
			
			$this->ftpManager = new AutoUpdaterFtpManager($this->createSettings(), $filesystem, $this->logControl);
		}
		
		return $this->ftpManager;
	}
	
	
	/**
	 * @return \AutoUpdaterBackupHelper
	 */
	protected function createBackupHelper()
	{
		if($this->backupHelper === null)
		{
			$this->backupHelper = new AutoUpdaterBackupHelper($this->createSettings(),
			                                                  $this->createAutoUpdaterDataCache(), $this->logControl);
		}
		
		return $this->backupHelper;
	}
	
	
	/**
	 * @return \AutoUpdaterUpdatesHelper
	 */
	protected function createUpdatesHelper()
	{
		if($this->updatesHelper === null)
		{
			$this->updatesHelper = new AutoUpdaterUpdatesHelper($this->createSettings(), $this->db, $this->dataCache,
			                                                    $this->createCurlClient(), $this->logControl);
		}
		
		return $this->updatesHelper;
	}
	
	
	/**
	 * @return \AutoUpdaterDownloadHelper
	 */
	protected function createDownloadHelper()
	{
		if($this->downloadHelper === null)
		{
			$this->downloadHelper = new AutoUpdaterDownloadHelper($this->createSettings(), $this->createCurlClient(),
			                                                      $this->logControl);
		}
		
		return $this->downloadHelper;
	}
	
	
	/**
	 * @return \AutoUpdaterSettings
	 */
	protected function createSettings()
	{
		if($this->settings === null)
		{
			$this->settings = new AutoUpdaterSettings($this->createCurlClient(), $this->db);
		}
		
		return $this->settings;
	}
	
	
	/**
	 * @return \AutoUpdaterCurlClient
	 */
	protected function createCurlClient()
	{
		if($this->curlClient === null)
		{
			$this->curlClient = new AutoUpdaterCurlClient();
		}
		
		return $this->curlClient;
	}
	
	
	/**
	 * @return \AutoUpdaterDataCache
	 */
	protected function createAutoUpdaterDataCache()
	{
		if($this->autoUpdaterDataCache === null)
		{
			$this->autoUpdaterDataCache = AutoUpdaterDataCache::get_instance();
		}
		
		return $this->autoUpdaterDataCache;
	}
}