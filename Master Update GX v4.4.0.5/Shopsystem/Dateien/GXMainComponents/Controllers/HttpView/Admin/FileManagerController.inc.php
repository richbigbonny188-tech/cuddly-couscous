<?php
/* --------------------------------------------------------------
   FileManagerController.inc.php 2017-06-12
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------

   based on:
   (c) 2013 John Campbell (jcampbell1) - Simple PHP File Manager

   Released under the MIT License (MIT)

   Permission is hereby granted, free of charge, to any person obtaining a copy of
   this software and associated documentation files (the "Software"), to deal in
   the Software without restriction, including without limitation the rights to
   use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
   the Software, and to permit persons to whom the Software is furnished to do so,
   subject to the following conditions:
   
   The above copyright notice and this permission notice shall be included in all
   copies or substantial portions of the Software.
   
   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
   FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
   IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
   CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
   --------------------------------------------------------------
*/

MainFactory::load_class('AdminHttpViewController');

/**
 * Class FileManagerController
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class FileManagerController extends AdminHttpViewController
{
    /**
     * @var string
     */
    protected $defaultContent = 'product_images';
    
    /**
     * @var string $subDirectory
     */
    protected $subDirectory;
    
    /**
     * @var string $baseDirectory
     */
    protected $baseDirectory;
    
    /**
     * @var string $content
     */
    protected $content;
    
    /**
     * @var string $file
     */
    protected $file = '';
    
    /**
     * @var bool $allowDelete
     */
    protected $allowDelete = true; // Set to false to disable delete button and delete POST request.
    
    /**
     * @var bool $allowCreateFolder
     */
    protected $allowCreateFolder = true; // Set to false to disable folder creation
    
    /**
     * @var bool $allowUpload
     */
    protected $allowUpload = true; // Set to true to allow upload files
    
    /**
     * @var bool $allowDirectLink
     */
    protected $allowDirectLink = true; // Set to false to only allow downloads and not direct link
    
    /**
     * @var array $disallowedExtensions
     */
    protected $disallowedExtensions;  // must be an array
    
    /**
     * @var array $listingFileSuffixBlacklist
     */
    protected $listingFileSuffixBlacklist;
    
    /**
     * @var array $listingFilePrefixBlacklist
     */
    protected $listingFilePrefixBlacklist;
    
    /**
     * @var array $deleteBlackList
     */
    protected $deleteBlackList;
    
    /**
     * @var int $maxUploadSize
     */
    protected $maxUploadSize = 0;
    
    /**
     * @var array $infoMessages
     */
    protected $infoMessages = [];
    
    
    /**
     * Initializes the controller
     *
     * @param HttpContextInterface $httpContext
     */
    public function proceed(HttpContextInterface $httpContext)
    {
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/');
        
        parent::proceed($httpContext);
    }
    
    
    /**
     * @return AdminLayoutHttpControllerResponse
     * @throws InvalidArgumentException
     *
     */
    public function actionDefault()
    {
        $this->_init();
        
        $this->maxUploadSize = min($this->_asBytes(ini_get('post_max_size')),
                                   $this->_asBytes(ini_get('upload_max_filesize')));
        
        $languageTextManager = MainFactory::create('LanguageTextManager', 'file_manager', $_SESSION['languages_id']);
        $title               = new NonEmptyStringType($languageTextManager->get_text('HEADING_TITLE'));
        $template            = new ExistingFile(new NonEmptyStringType(DIR_FS_ADMIN
                                                                       . '/html/content/file_manager.html'));
        $data                = MainFactory::create('KeyValueCollection',
                                                   [
                                                       'baseDirectory'     => $this->baseDirectory,
                                                       'subDirectory'      => $this->subDirectory,
                                                       'content'           => $this->content,
                                                       'file'              => $this->file,
                                                       'maxUploadSize'     => $this->maxUploadSize,
                                                       'allowUpload'       => $this->allowUpload,
                                                       'allowDirectLink'   => $this->allowDirectLink,
                                                       'allowCreateFolder' => $this->allowCreateFolder
                                                   ]);
        $assets              = MainFactory::create('AssetCollection',
                                                   [
                                                       MainFactory::create('Asset', 'file_manager.lang.inc.php')
                                                   ]);
        $contentNavigation   = MainFactory::create('ContentNavigationCollection', []);
        $contentNavigation->add(new StringType($languageTextManager->get_text('PRODUCT_IMAGES')),
                                new StringType('admin.php?do=FileManager&content=product_images'),
                                new BoolType($this->content === 'product_images'));
        $contentNavigation->add(new StringType($languageTextManager->get_text('MEDIA')),
                                new StringType('admin.php?do=FileManager&content=media'),
                                new BoolType($this->content === 'media'));
        $contentNavigation->add(new StringType($languageTextManager->get_text('DOWNLOAD')),
                                new StringType('admin.php?do=FileManager&content=download'),
                                new BoolType($this->content === 'download'));
        $contentNavigation->add(new StringType($languageTextManager->get_text('IMAGES')),
                                new StringType('admin.php?do=FileManager&content=images'),
                                new BoolType($this->content === 'images'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $template,
                                   $data,
                                   $assets,
                                   $contentNavigation);
    }
    
    
    public function actionList()
    {
        $this->_init();
        
        $result    = [];
        $directory = $this->baseDirectory . $this->file . ($this->file !== '' ? '/' : '');
        if (is_dir($directory)) {
            $files = array_diff(scandir($directory), ['.', '..']);
            
            foreach ($files as $entry) {
                if ($entry !== basename(__FILE__) && !$this->_fileIsBlacklisted($entry)) {
                    $i    = $directory . $entry;
                    $stat = stat($i);
                    
                    $result[] = [
                        'mtime'         => $stat['mtime'],
                        'size'          => $stat['size'],
                        'name'          => basename($i),
                        'path'          => preg_replace('@^\./@', '', str_replace($this->baseDirectory, '', $i)),
                        'is_dir'        => is_dir($i),
                        'is_deleteable' => $this->allowDelete && !$this->_isOnDeleteBlacklist($i)
                                           && ((!is_dir($i) && is_writable($directory))
                                               || (is_dir($i) && is_writable($directory)
                                                   && $this->_isRecursivelyDeletable($i))),
                        'is_readable'   => is_readable($i),
                        'is_writable'   => is_writable($i),
                        'is_executable' => is_executable($i),
                        'info_message'  => $this->_getInfoMessage($i)
                    ];
                }
            }
        } else {
            http_response_code(412);
            
            return MainFactory::create('JsonHttpControllerResponse',
                                       ['error' => ['code' => 412, 'msg' => 'Not a Directory']]);
        }
        
        return MainFactory::create('JsonHttpControllerResponse',
                                   ['success' => true, 'is_writable' => is_writable($directory), 'results' => $result]);
    }
    
    
    public function actionDelete()
    {
        $this->_init();
        
        if ($this->allowDelete) {
            $this->file = $this->_getPostData('file');
            $this->_removeRecursively($this->baseDirectory . $this->file);
        } else {
            http_response_code(403);
            
            return MainFactory::create('JsonHttpControllerResponse',
                                       ['error' => ['code' => 403, 'msg' => 'Deletion is not allowed.']]);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => true]);
    }
    
    
    public function actionMkdir()
    {
        $this->_init();
        
        if ($this->allowCreateFolder) {
            // filter some special chars for windows compatibility: " * : < > ? / \ |
            $this->postDataArray['name'] = str_replace(['"', '*', ':', '<', '>', '?', '/', '\\', '|'],
                                                       '',
                                                       $this->postDataArray['name']);
            // also filter §, because this seems to create some strange errors ...
            $this->postDataArray['name'] = str_replace('§', '', $this->postDataArray['name']);
            chdir($this->baseDirectory . $this->file);
            $oldMask = umask(0);
            if (!@mkdir($this->postDataArray['name'],
                        0777)
                && !is_dir($this->postDataArray['name'])
                && strlen($this->postDataArray['name']) > 0) {
                umask($oldMask);
                http_response_code(500);
                
                return MainFactory::create('JsonHttpControllerResponse',
                                           [
                                               'error' => [
                                                   'code' => 500,
                                                   'msg'  => 'The directory could not be created.'
                                               ]
                                           ]);
            }
            umask($oldMask);
            
            http_response_code(201);
            
            return MainFactory::create('JsonHttpControllerResponse', ['success' => true]);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => true]);
    }
    
    
    public function actionUpload()
    {
        $this->_init();
        
        if ($this->allowUpload) {
            foreach ($this->disallowedExtensions as $extension) {
                if (preg_match(sprintf('/\.%s$/', preg_quote($extension)), $_FILES['file_data']['name'])) {
                    http_response_code(403);
                    
                    return MainFactory::create('JsonHttpControllerResponse',
                                               [
                                                   'error' => [
                                                       'code' => 403,
                                                       'msg'  => 'Files of this type are not allowed.'
                                                   ]
                                               ]);
                }
            }
            
            $dir       = $this->_getQueryParameter('directory');
            $targetDir = $this->baseDirectory . '/' . (!empty($dir) ? $dir . '/' : '');
            
            if (!@move_uploaded_file($_FILES['file_data']['tmp_name'], $targetDir . $_FILES['file_data']['name'])
                && !is_file($targetDir . $_FILES['file_data']['name'])) {
                http_response_code(500);
                
                return MainFactory::create('JsonHttpControllerResponse',
                                           ['error' => ['code' => 500, 'msg' => 'The file could not be uploaded.']]);
            }
            
            http_response_code(201);
            
            return MainFactory::create('JsonHttpControllerResponse', ['success' => true]);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => true]);
    }
    
    
    public function actionDownload()
    {
        $this->_init();
        
        $file     = $this->baseDirectory . $this->file;
        $filename = basename($this->file);
        header('Content-Type: ' . mime_content_type($file));
        header('Content-Length: ' . filesize($file));
        header(sprintf('Content-Disposition: attachment; filename=%s',
                       strpos('MSIE', $_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : '"' . $filename . '"'));
        ob_flush();
        readfile($file);
        
        return MainFactory::create('HttpControllerResponse', '');
    }
    
    
    public function actionThumb()
    {
        $this->_init();
        
        $file = $this->baseDirectory . $this->file;
        
        // Get mime type info
        $mime = mime_content_type($file);
        
        // Return thumbnail
        if (strpos($mime, 'image') === 0) {
            return MainFactory::create('HttpControllerResponse', $this->_generateThumbnail($file, 32, 32));
        }
    }
    
    
    protected function _init()
    {
        $this->_initDisallowedExtensions();
        $this->_initListingFileSuffixBlacklist();
        $this->_initListingFilePrefixBlacklist();
        $this->_initDeleteBlackList();
        $this->_initInfoMessages();
        
        if (array_key_exists('content', $this->queryParametersArray)) {
            $this->content = $this->queryParametersArray['content'];
        } else {
            $this->content = $this->defaultContent;
        }
        
        switch ($this->content) {
            case 'product_images':
                $this->subDirectory = 'images/product_images/';
                break;
            case 'images':
                $this->subDirectory = 'images/';
                break;
            case 'media':
                $this->subDirectory = 'media/';
                break;
            case 'download':
                $this->subDirectory = 'download/';
                break;
        }
        
        $this->baseDirectory = DIR_FS_CATALOG . $this->subDirectory;
        
        if (array_key_exists('file', $this->queryParametersArray) && $this->queryParametersArray['file'] !== '') {
            $this->file = urldecode($this->queryParametersArray['file']);
        }
    }
    
    
    protected function _initDisallowedExtensions()
    {
        $this->disallowedExtensions = ['php', 'htaccess'];
    }
    
    
    protected function _initListingFileSuffixBlacklist()
    {
        $this->listingFileSuffixBlacklist = ['.php', '.htaccess', 'index.html'];
    }
    
    
    protected function _initListingFilePrefixBlacklist()
    {
        $this->listingFilePrefixBlacklist = ['secure_token_', 'product_images'];
    }
    
    
    protected function _initDeleteBlackList()
    {
        $this->deleteBlackList = [
            'images/product_images/attribute_images',
            'images/product_images/gallery_images',
            'images/product_images/info_images',
            'images/product_images/original_images',
            'images/product_images/popup_images',
            'images/product_images/properties_combis_images',
            'images/product_images/thumbnail_images'
        ];
    }
    
    
    protected function _initInfoMessages()
    {
        $this->infoMessages = [
            'images/product_images/original_images' => 'INFO_ORIGINAL_IMAGES'
        ];
    }
    
    
    protected function _isRecursivelyDeletable($topDir)
    {
        $stack = [$topDir];
        while ($dir = array_pop($stack)) {
            if (!is_readable($dir) || !is_writable($dir)) {
                return false;
            }
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                if (is_dir($file)) {
                    $stack[] = $dir . '/' . $file;
                }
            }
        }
        
        return true;
    }
    
    
    protected function _removeRecursively($dir)
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $this->_removeRecursively($dir . '/' . $file);
            }
            rmdir($dir);
        } else {
            unlink($dir);
        }
    }
    
    
    protected function _asBytes($ini_v)
    {
        $ini_v = trim($ini_v);
        $s     = ['g' => 1 << 30, 'm' => 1 << 20, 'k' => 1 << 10];
        
        return (int)$ini_v * ($s[strtolower(substr($ini_v, -1))] ? : 1);
    }
    
    
    protected function _fileIsBlacklisted($filename)
    {
        $filenameLength = strlen($filename);
        
        foreach ($this->listingFilePrefixBlacklist as $blacklistItem) {
            $blacklistItemLength = strlen($blacklistItem);
            
            if ($blacklistItemLength <= $filenameLength && strpos($filename, $blacklistItem) === 0) {
                return true;
            }
        }
        
        foreach ($this->listingFileSuffixBlacklist as $blacklistItem) {
            $blacklistItemLength = strlen($blacklistItem);
            
            if ($blacklistItemLength <= $filenameLength
                && substr_compare($filename,
                                  $blacklistItem,
                                  $blacklistItemLength - $filenameLength,
                                  $filenameLength) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    
    protected function _isOnDeleteBlacklist($file)
    {
        foreach ($this->deleteBlackList as $blackListItem) {
            if ($file === DIR_FS_CATALOG . $blackListItem) {
                return true;
            }
        }
        
        return false;
    }
    
    
    protected function _getInfoMessage($file)
    {
        $relativeFilePath = substr($file, strlen(DIR_FS_CATALOG));
        if (array_key_exists($relativeFilePath, $this->infoMessages)) {
            return $this->infoMessages[$relativeFilePath];
        }
        
        return '';
    }
    
    
    /**
     * Generates thumbnails for images
     *
     * @param     $img
     * @param     $width
     * @param     $height
     *
     * @return \Imagick
     */
    protected function _generateThumbnail($img, $width, $height)
    {
        $data        = file_get_contents($img);
        $image       = imagecreatefromstring($data);
        $imageSize   = getimagesize($img);
        $imageWidth  = $imageSize[0];
        $imageHeight = $imageSize[1];
        $imageType   = $imageSize[2];
        $thumbWidth  = $imageWidth;
        $thumbHeight = $imageHeight;
        
        if ($thumbWidth > $width) {
            $factor      = $width / $thumbWidth;
            $thumbWidth  *= $factor;
            $thumbHeight *= $factor;
        }
        
        if ($thumbHeight > $height) {
            $factor      = $height / $thumbHeight;
            $thumbWidth  *= $factor;
            $thumbHeight *= $factor;
        }
        
        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $imageWidth, $imageHeight);
        
        header('Content-Type: image/png');
        imagepng($thumb);
        imagedestroy($thumb);
    }
}