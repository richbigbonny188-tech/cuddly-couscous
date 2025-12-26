<?php
/*--------------------------------------------------------------
   ImageProcessingController.inc.php 2020-11-20
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

/**
 * Class ImageProcessingController
 * @extends    AdminHttpViewController
 * @category   System
 * @package    AdminHttpViewControllers
 */
class ImageProcessingController extends AdminHttpViewController
{
    protected const ACCEPTED_FILE_TYPES = ['jpg', 'jpeg', 'gif', 'png'];
    
    /**
     * @return AdminLayoutHttpControllerResponse
     */
    public function actionDefault()
    {
        $lang = MainFactory::create_object('LanguageTextManager', ['image_processing', $_SESSION['languages_id']]);
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/');
        
        $template      = 'image_processing.html';
        $assets        = [
            'image_processing.lang.inc.php',
        ];
        $subNavigation = [
            [
                'text'   => $lang->get_text('image_processing_title'),
                'link'   => '',
                'active' => true,
            ],
        ];
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($lang->get_text('image_processing_title'),
                                                                                  $template,
                                                                                  [],
                                                                                  $assets,
                                                                                  $subNavigation);
    }
    
    
    /**
     * Runs the image Processing
     *
     * @return RedirectHttpControllerResponse
     */
    public function actionProcess()
    {
        @xtc_set_time_limit(0);
        require_once DIR_FS_CATALOG . 'admin/includes/classes/' . FILENAME_IMAGEMANIPULATOR;
        
        $logger          = LogControl::get_instance();
        $imageNumber     = (int)$this->_getPostData('image_number');
        $imageFile       = $this->_getPostData('image_file');
        $files           = $this->_getImageFiles();
        $responseMessage = '';
        $nextImageNumber = 0;
        $fileNotFound    = false;
        
        // search for image number if image filename is given
        if ($imageFile !== '') {
            $counter = -1;
            for ($i = 1; $i <= count($files); $i++) {
                if ($files[$i - 1]['text'] === $imageFile) {
                    $counter = $i;
                    break;
                }
            }
            // searching for file failed, if counter is still -1. otherwise set imageNumber to counter
            if ($counter === -1) {
                $fileNotFound    = true;
                $responseMessage = 'Image "' . $imageFile . '" could not be found.';
                $logger->notice($responseMessage,
                                'widgets',
                                'image_processing',
                                'notice',
                                $p_level_type = 'DEBUG NOTICE',
                                E_USER_NOTICE);
            } else {
                $imageNumber     = $counter;
                $nextImageNumber = $counter + 1;
            }
        }
        
        // do not rename this variables, because included files need them
        $products_image_name = $files[$imageNumber - 1]['text'];
        $image_error         = false;
        
        $filesCount = count($files);
        
        if (!$fileNotFound) {
            if ($imageNumber <= $filesCount && $imageNumber > 0) {
                include(DIR_WS_INCLUDES . 'product_popup_images.php');
                include(DIR_WS_INCLUDES . 'product_info_images.php');
                include(DIR_WS_INCLUDES . 'product_thumbnail_images.php');
                include(DIR_WS_INCLUDES . 'product_gallery_images.php');
                
                // image processing failed, log the error
                if ($image_error) {
                    $responseMessage = 'Image ' . $imageNumber . ' "' . $products_image_name
                                       . '" could not be processed.';
                    $logger->notice($responseMessage,
                                    'widgets',
                                    'image_processing',
                                    'notice',
                                    $p_level_type = 'DEBUG NOTICE',
                                    E_USER_NOTICE);
                } elseif ($imageNumber === $filesCount) {
                    $logger->notice('Image processing DONE',
                                    'widgets',
                                    'image_processing',
                                    'notice',
                                    $p_level_type = 'DEBUG NOTICE',
                                    E_USER_NOTICE);
                }
                
                $finished = $imageNumber === $filesCount;
            } else {
                $finished = true;
            }
        } else {
            $finished    = true;
            $image_error = true;
        }
        
        $payload = [
            'imagesCount'  => $filesCount,
            'finished'     => $finished,
            'imageName'    => $products_image_name,
            'nextImageNr'  => $nextImageNumber,
            'fileNotFound' => $fileNotFound
        ];
        
        $response = ['success' => !$image_error, 'msg' => $responseMessage, 'payload' => $payload];
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * @return array
     */
    protected function _getImageFiles(): array
    {
        $originalImagesPath = $this->addMissingDirectorySeparatorAtEndOfPath(DIR_FS_CATALOG_ORIGINAL_IMAGES);
        $files              = $this->recursiveImageFilesInDirectory($originalImagesPath);
        array_multisort($files);
        $files = array_values($files);
        
        foreach ($files as $index => &$file) {
            // nr is used for splitting up process to a one request per image
            $file['nr'] = $index;
        }
        
        unset($file);
        
        return $files;
    }
    
    /**
     * @param string      $directory
     * @param string|null $startDirectory
     *
     * @return array
     */
    protected function recursiveImageFilesInDirectory(string $directory, string $startDirectory = null): array
    {
        $startDirectory = $startDirectory ?? $directory;
        $result         = [];
    
        try {
        
            if ($dir = opendir($directory)) {
            
                while ($file = readdir($dir)) {
                
                    if (in_array($file, ['.', '..'], true)) {
                        continue;
                    }
                    
                    $absolutePath = $directory . $file;
                    $relativePath = str_replace($startDirectory, '', $absolutePath);
                
                    if (is_file($absolutePath)
                        && in_array(strtolower_wrapper(pathinfo($absolutePath, PATHINFO_EXTENSION)),
                                    self::ACCEPTED_FILE_TYPES,
                                    true)) {
                    
                        $result[] = [
                            'id'   => $relativePath,
                            'text' => $relativePath
                        ];
                    } elseif (is_dir($absolutePath)) {
                    
                        $subDirectoryPath  = $this->addMissingDirectorySeparatorAtEndOfPath($absolutePath);
                        $subDirectoryFiles = $this->recursiveImageFilesInDirectory($subDirectoryPath, $startDirectory);
                    
                        foreach ($subDirectoryFiles as $fileInformationArray) {
                        
                            $result[] = $fileInformationArray;
                        }
                    }
                }
            }
        } finally {
            closedir($dir);
        }
        
        return $result;
    }
    
    
    /**
     * @param string $path
     *
     * @return string
     */
    protected function addMissingDirectorySeparatorAtEndOfPath(string $path): string
    {
        $pattern = '#' . preg_quote(DIRECTORY_SEPARATOR, '#') . '$#';
        
        return preg_match($pattern, $path) === 1 ? $path : $path . DIRECTORY_SEPARATOR;
    }
}