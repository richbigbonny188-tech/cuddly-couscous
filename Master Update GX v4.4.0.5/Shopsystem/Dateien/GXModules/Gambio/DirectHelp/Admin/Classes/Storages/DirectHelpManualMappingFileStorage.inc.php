<?php
/* --------------------------------------------------------------
   DirectHelpManualMappingFileStorage.inc.php 2020-11-19
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class representing the manual links mapping file storage
 */
class DirectHelpManualMappingFileStorage
{
    /**
     * Fallback remote content (if the remote server can not be reached)
     *
     * @var string
     */
    const FALLBACK_CONTENT = '{ "root": "https://developers.gambio.de/manual.html", "links": {} }';
    
    /**
     * HTTP Not-Found status code
     *
     * @var int
     */
    const HTTP_NOT_FOUND_STATUS_CODE = 404;
    
    /**
     * Maximum cache time
     *
     * @var int
     */
    protected $ttl;
    
    /**
     * Local file location
     *
     * @var string
     */
    protected $cacheLocation;
    
    /**
     * Remote file location
     *
     * @var string
     */
    protected $remoteLocation;
    
    /**
     * Shop version
     *
     * @var string
     */
    protected $shopVersion;
    
    /**
     * Online manual root page
     *
     * @var string
     */
    protected $rootPage;
    
    /**
     * Online manual page link mapping
     *
     * @var array
     */
    protected $mapping;
    
    
    /**
     * Create instance
     *
     * @param IntType            $ttl            Maximum cache time
     * @param NonEmptyStringType $localLocation  Local cache file location
     * @param NonEmptyStringType $remoteLocation Remote file location
     * @param NonEmptyStringType $version        Shop version
     */
    public function __construct(
        IntType $ttl,
        NonEmptyStringType $localLocation,
        NonEmptyStringType $remoteLocation,
        NonEmptyStringType $version
    ) {
        $this->ttl            = $ttl->asInt();
        $this->cacheLocation  = $localLocation->asString();
        $this->remoteLocation = $remoteLocation->asString();
        $this->shopVersion    = $version->asString();
    }
    
    
    /**
     * Return the online manual page link mapping
     *
     * @return array
     */
    public function mapping()
    {
        if (!$this->mapping) {
            $content       = $this->content();
            $this->mapping = $content['links'];
        }
        
        return $this->mapping;
    }
    
    
    /**
     * Return the online manual root page
     *
     * @return string
     */
    public function rootPage()
    {
        if (!$this->rootPage) {
            $content        = $this->content();
            $this->rootPage = $content['root'];
        }
        
        return $this->rootPage;
    }
    
    
    /**
     * Return the decoded mapping file content
     *
     * @return array
     */
    protected function content()
    {
        $decodeAsArray = true;
        $localContent  = $this->localContent();
        
        if ($localContent) {
            return json_decode($localContent, $decodeAsArray);
        }
        
        $remoteContent  = $this->remoteContent();
        $decodedContent = json_decode($remoteContent, $decodeAsArray);
        
        if (count($decodedContent['links'])) {
            $this->renewCache($remoteContent);
        }
        
        return $decodedContent;
    }
    
    
    /**
     * Return the cached file content
     *
     * @return string|null
     */
    protected function localContent()
    {
        if (!file_exists($this->cacheLocation)) {
            return null;
        }
        
        if (filemtime($this->cacheLocation) + $this->ttl < time()) {
            return null;
        }
        
        return file_get_contents($this->cacheLocation);
    }
    
    
    /**
     * Return the content from remote file
     *
     * @return string
     */
    protected function remoteContent()
    {
        $request = curl_init();
        $url     = $this->remoteLocation . '?shopVersion=' . $this->shopVersion;
        
        curl_setopt_array($request,
                          [
                              CURLOPT_URL            => $url,
                              CURLOPT_RETURNTRANSFER => true,
                              CURLOPT_CONNECTTIMEOUT => 5,
                              CURLOPT_TIMEOUT        => 5,
                          ]);
        
        $content    = curl_exec($request);
        $statusCode = curl_getinfo($request, CURLINFO_HTTP_CODE);
        
        curl_close($request);
        
        if ($statusCode === self::HTTP_NOT_FOUND_STATUS_CODE || strpos((string)$content, '"links"') === false) {
            $content = self::FALLBACK_CONTENT;
        }
        
        return (string)$content;
    }
    
    
    /**
     * Renew cache file
     *
     * @param string $content New file content
     */
    protected function renewCache($content)
    {
        if (file_exists($this->cacheLocation)) {
            unlink($this->cacheLocation);
        }
        
        touch($this->cacheLocation);
        file_put_contents($this->cacheLocation, $content);
    }
}