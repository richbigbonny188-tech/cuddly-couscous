<?php
/* --------------------------------------------------------------
 GoogleFontDownloader.inc.php 2018-08-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2018 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

/**
 * Class GoogleFontDownloader
 */
class GoogleFontDownloader
{
	/**
	 * @var string
	 */
	protected $fsFontDirectory;
	
	/**
	 * @var string
	 */
	protected $wsFontDirectory;
	
	/**
	 * @var \LanguageTextManager
	 */
	protected $languageTextManager;
	
	
	/**
	 * GoogleFontDownloader constructor.
	 */
	public function __construct()
	{
		$this->fsFontDirectory = DIR_FS_CATALOG . 'public/fonts/';
		$this->wsFontDirectory = GM_HTTP_SERVER  . DIR_WS_CATALOG . 'public/fonts/';
		
		if(!file_exists($this->fsFontDirectory))
		{
			@mkdir($this->fsFontDirectory, 0777);
			@chmod($this->fsFontDirectory, 0777);
		}
	}
	
	
	/**
	 * Downloads the fonts of a given google font definition url and creates a local font definition css file.
	 *
	 * @param $fontUrl string URL of the google font definition.
	 */
	public function downloadFont($fontUrl)
	{
		$css = $this->_getCss($fontUrl);
		preg_match_all('/.*url\(([^)]*)\).*/m', $css, $matches, PREG_SET_ORDER, 0);
		
		if(count($matches) > 0)
		{
			foreach($matches as $match)
			{
				$source       = $match[1];
				$parsedSource = preg_replace('/\?.*/', '', basename($source));
				$destination  = $this->fsFontDirectory . $parsedSource;
				
				if(!$this->_downloadFile($destination, $source))
				{
					return;
				}
				$css = str_replace($source, $this->wsFontDirectory . $parsedSource, $css);
			}
			
			file_put_contents($this->fsFontDirectory . md5($fontUrl) . '.css', $css);
			@chmod($this->fsFontDirectory . md5($fontUrl) . '.css', 0777);
		}
	}
    
	
	/**
	 * Returns the css font definition of a given google font definition URL.
	 *
	 * @param $fontUrl string URL of the google font definition.
	 *
	 * @return string Css font definition.
	 */
	protected function _getCss($fontUrl)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL            => $fontUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_CUSTOMREQUEST  => "GET",
			CURLOPT_HTTPHEADER     => array(
				"cache-control: no-cache"
			),
		));
		$response = curl_exec($curl);
		$error    = curl_error($curl);
		curl_close($curl);
		
		return ($error !== '') ? '' : $response;
	}
	
	
	/**
	 * Downloads a file with curl.
	 *
	 * @param $destination string Destination path on the file system.
	 * @param $source string Source URL of the file.
	 *
	 * @return bool
	 */
	protected function _downloadFile($destination, $source)
	{
		if(file_exists($destination) && filesize($destination) > 0)
		{
			return true;
		}
		
		$file = fopen($destination, 'w');
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_FILE    => $file,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_URL     => $source,
		]);
		curl_exec($curl);
		curl_close($curl);
		@fclose($file);
		@chmod($destination, 0777);
		
		if(file_exists($destination) && filesize($destination) > 0)
		{
			return true;
		}
		
		return false;
	}
}