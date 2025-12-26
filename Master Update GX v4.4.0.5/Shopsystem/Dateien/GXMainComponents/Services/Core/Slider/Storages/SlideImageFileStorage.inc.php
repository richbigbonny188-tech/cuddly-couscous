<?php

/* --------------------------------------------------------------
   SlideImageFileStorage.inc.php 2021-03-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class SlideImageFileStorage
 *
 * @category   System
 * @package    Slider
 * @subpackage Storages
 */
class SlideImageFileStorage extends ImageFileStorage
{
    /**
     * Settings
     *
     * @var EnvSlideImageFileStorageSettings
     */
    protected $settings;
    
    /**
     * Slide image directory.
     *
     * @var WritableDirectory
     */
    protected $imagesDirectory;
    
    /**
     * Slide thumbnail image directory.
     *
     * @var WritableDirectory
     */
    protected $thumbnailImagesDirectory;
    
    /**
     * Valid file extensions.
     * @var array
     */
    protected $validExtensions = [];
    
    
    /**
     * SlideImageFileStorage constructor.
     *
     * @param SlideImagePathsSettingsInterface $settings Slide image path settings.
     */
    public function __construct(SlideImagePathsSettingsInterface $settings)
    {
        $this->settings                 = $settings;

        if(!file_exists($this->settings->getSlideImagesDirPath()))
        {
            mkdir($this->settings->getSlideImagesDirPath(),0777,true);
        }

        if(!file_exists($this->settings->getSlideThumbnailImagesDirPath()))
        {
            mkdir($this->settings->getSlideThumbnailImagesDirPath(),0777,true);
        }

        $this->imagesDirectory          = MainFactory::create('WritableDirectory',
                                                              $this->settings->getSlideImagesDirPath());
        $this->thumbnailImagesDirectory = MainFactory::create('WritableDirectory',
                                                              $this->settings->getSlideThumbnailImagesDirPath());
        $this->validExtensions          = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        
        parent::__construct($this->imagesDirectory);
    }
    
    
    /**
     * Saves a slide image file to a writable directory.
     *
     * @param ExistingFile       $sourceFile        The source file to import.
     * @param FilenameStringType $preferredFilename The preferred name of the file to be saved.
     *
     * @return string Preferred filename
     * @throws InvalidArgumentException If the provided source file of the preferred filename is not valid.
     *
     */
    public function importImage(ExistingFile $sourceFile, FilenameStringType $preferredFilename)
    {
        $filename = parent::importFile($sourceFile, $preferredFilename);
        $filename = new FilenameStringType($filename);
        
        return $filename->asString();
    }
    
    
    /**
     * Saves a slide thumbnail image file to a writable directory.
     *
     * @param ExistingFile       $sourceFile        The source file to import.
     * @param FilenameStringType $preferredFilename The preferred name of the file to be saved.
     *
     * @return string Preferred filename
     * @throws InvalidArgumentException If the provided source file of the preferred filename is not valid.
     *
     */
    public function importThumbnailImage(ExistingFile $sourceFile, FilenameStringType $preferredFilename)
    {
        $this->_validateFile($sourceFile);
        $this->_validateFilename($preferredFilename);
        
        $uniqueFilename = $preferredFilename;
        
        if ($this->fileExists($preferredFilename)) {
            $uniqueFilename = new FilenameStringType($this->_createAndReturnNewFilename($preferredFilename));
        }
        
        copy($sourceFile->getFilePath(),
             $this->thumbnailImagesDirectory->getDirPath() . DIRECTORY_SEPARATOR . $uniqueFilename->asString());
        
        return $uniqueFilename->asString();
    }
    
    
    /**
     * Renames an existing slide image file.
     *
     * @param FilenameStringType $oldName The old name of the file.
     * @param FilenameStringType $newName The new name of the file.
     *
     * @return AbstractFileStorage Same instance for chained method calls.
     * @throws InvalidArgumentException If a file with the preferred name already exists.
     *
     * @throws InvalidArgumentException If the file that should be renamed does not exists.
     */
    public function renameImage(FilenameStringType $oldName, FilenameStringType $newName)
    {
        if (!$this->fileExists($oldName)) {
            throw new InvalidArgumentException($oldName->asString() . ' does not exist in '
                                               . $this->imagesDirectory->getDirPath());
        }
        
        if ($this->fileExists($newName)) {
            throw new InvalidArgumentException($newName->asString() . ' already exists in '
                                               . $this->imagesDirectory->getDirPath());
        }
        
        $this->_validateFilename($newName);
        
        rename($this->imagesDirectory->getDirPath() . DIRECTORY_SEPARATOR . $oldName->asString(),
               $this->imagesDirectory->getDirPath() . DIRECTORY_SEPARATOR . $newName->asString());
        
        return $this;
    }
    
    
    /**
     * Renames an existing slide thumbnail image file.
     *
     * @param FilenameStringType $oldName The old name of the file.
     * @param FilenameStringType $newName The new name of the file.
     *
     * @return AbstractFileStorage Same instance for chained method calls.
     * @throws InvalidArgumentException If a file with the preferred name already exists.
     *
     * @throws InvalidArgumentException If the file that should be renamed does not exists.
     */
    public function renameThumbnailImage(FilenameStringType $oldName, FilenameStringType $newName)
    {
        if (!$this->fileExists($oldName)) {
            throw new InvalidArgumentException($oldName->asString() . ' does not exist in '
                                               . $this->thumbnailImagesDirectory->getDirPath());
        }
        
        if ($this->fileExists($newName)) {
            throw new InvalidArgumentException($newName->asString() . ' already exists in '
                                               . $this->thumbnailImagesDirectory->getDirPath());
        }
        
        $this->_validateFilename($newName);
        
        rename($this->thumbnailImagesDirectory->getDirPath() . DIRECTORY_SEPARATOR . $oldName->asString(),
               $this->thumbnailImagesDirectory->getDirPath() . DIRECTORY_SEPARATOR . $newName->asString());
        
        return $this;
    }
    
    
    /**
     * Checks if the provided slider image exists.
     *
     * @param FilenameStringType $filename The filename of the slider image file to be checked.
     *
     * @return bool Does it exist?
     */
    public function imageExists(FilenameStringType $filename)
    {
        $filePath = $this->imagesDirectory->getDirPath() . DIRECTORY_SEPARATOR . $filename->asString();
        
        return file_exists($filePath) && !is_dir($filePath);
    }
    
    
    /**
     * Checks if the provided slider thumbnail image exists.
     *
     * @param FilenameStringType $filename The filename of the slider thumbnail image file to be checked.
     *
     * @return bool Does it exist?
     */
    public function thumbnailImageExists(FilenameStringType $filename)
    {
        $filePath = $this->thumbnailImagesDirectory->getDirPath() . DIRECTORY_SEPARATOR . $filename->asString();
        
        return file_exists($filePath) && !is_dir($filePath);
    }
    
    
    /**
     * Deletes an existing slider image.
     *
     * @param FilenameStringType $filename The file to delete.
     *
     * @return AbstractFileStorage Same instance for chained method calls.
     */
    public function deleteImage(FilenameStringType $filename)
    {
        if ($this->imageExists($filename)) {
            unlink($this->imagesDirectory->getDirPath() . DIRECTORY_SEPARATOR . $filename->asString());
        }
        
        return $this;
    }
    
    
    /**
     * Deletes an existing slider thumbnail image.
     *
     * @param FilenameStringType $filename The file to delete.
     *
     * @return AbstractFileStorage Same instance for chained method calls.
     */
    public function deleteThumbnailImage(FilenameStringType $filename)
    {
        if ($this->thumbnailImageExists($filename)) {
            unlink($this->thumbnailImagesDirectory->getDirPath() . DIRECTORY_SEPARATOR . $filename->asString());
        }
        
        return $this;
    }
    
    
    /**
     * Returns an array of slide images.
     *
     * @return array Found files.
     */
    public function getImages()
    {
        return $this->getFileList($this->imagesDirectory, $this->validExtensions);
    }
    
    
    /**
     * Returns an array of slide thumbnail images.
     *
     * @return array Found files.
     */
    public function getThumbnailImages()
    {
        return $this->getFileList($this->thumbnailImagesDirectory, $this->validExtensions);
    }
}