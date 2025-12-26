<?php
/*--------------------------------------------------------------------------------------------------
    ContentZoneService.php 2021-01-07
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Components\ContentZone\Repositories;

use FileNotFoundException;
use FilesystemAdapter;
use Gambio\StyleEdit\Configurations\ShopBaseUrl;
use Gambio\StyleEdit\Core\Components\ContentZone\Entities\ContentZoneData;
use Gambio\StyleEdit\Core\Components\ContentZone\Entities\ContentZoneOption;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\BasicThemeInterface;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\CurrentThemeInterface;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use ReflectionException;

/**
 * Class ContentZoneRepository
 * @package Gambio\StyleEdit\Core\Components\ContentZone\Repositories
 */
class ContentZoneRepository
{
    /**
     * @var string
     */
    protected const JSON_FILE_PATTERN    = '/\.json$/i';
    protected const JSON_DEFAULT_PATTERN = '/\.default\.json$/i';
    /**
     * @var CurrentThemeInterface
     */
    protected $currentTheme;
    /**
     * @var FilesystemAdapter
     */
    protected $filesystem;
    
    /**
     * @var ShopBaseUrl
     */
    protected $shopBaseUrl;
    
    
    /**
     * ContentZoneRepository constructor.
     *
     * @param CurrentThemeInterface $currentTheme
     * @param FilesystemAdapter     $filesystem
     * @param ShopBaseUrl           $shopBaseUrl
     */
    public function __construct(
        CurrentThemeInterface $currentTheme,
        FilesystemAdapter $filesystem,
        ShopBaseUrl $shopBaseUrl
    ) {
        $this->currentTheme = $currentTheme;
        $this->filesystem   = $filesystem;
        $this->shopBaseUrl = $shopBaseUrl;
    }
    
    
    /**
     * @param ContentZoneOption $option
     * @param Language          $languageCode
     *
     * @throws FileNotFoundException
     */
    public function saveContentZoneTemplate(ContentZoneOption $option, Language $languageCode): void
    {
        $fileContent = $option->content()->htmlContent($languageCode);
        
        $filePath = $this->currentTheme->id() . '/html/system/content_zone_' . $option->id() . '_'
                    . $languageCode->code() . '.html';
        
        if ($this->filesystem->has($filePath)) {
            $this->filesystem->delete($filePath);
        }
        
        $this->filesystem->write($filePath, $fileContent);
    }
    
    
    /**
     * @param ContentZoneOption $option
     *
     * @throws FileNotFoundException
     */
    public function saveContentZoneData(ContentZoneOption $option): void
    {
        $option->content()->persist();
        
        $filePath = $this->currentTheme->id() . '/contentzones/' . $option->id() . '.json';
        
        if ($this->filesystem->has($filePath)) {
            $this->filesystem->delete($filePath);
        }
        
        $this->filesystem->write($filePath, $this->createContentZoneJson($option));
    }
    
    
    /**
     * @return ContentZoneData
     * @throws FileNotFoundException
     * @throws ReflectionException
     */
    public function getAll(): ContentZoneData
    {
        $contentZoneJsonDirectory = $this->currentTheme->id() . '/contentzones/';
        
        $contentZones = [];
        
        $defaultFile = [];
        $customFiles = [];
        
        foreach ($this->filesystem->listContents($contentZoneJsonDirectory) as $file) {
            
            if (preg_match(self::JSON_DEFAULT_PATTERN, $file['basename'])) {
                $contentZoneName               = preg_replace(self::JSON_DEFAULT_PATTERN, '', $file['basename']);
                $defaultFile[$contentZoneName] = $file['path'];
            } elseif (preg_match(self::JSON_FILE_PATTERN, $file['basename'])) {
                $contentZoneName               = preg_replace(self::JSON_FILE_PATTERN, '', $file['basename']);
                $customFiles[$contentZoneName] = $file['path'];
            }
        }
        
        $contentZoneFiles = array_merge($defaultFile, $customFiles);
        foreach ($contentZoneFiles as $contentZoneFile) {
            $contentZones[] = $this->loadContentZone($contentZoneFile);
        }
        
        return new ContentZoneData($contentZones);
    }
    
    
    /**
     * @param string $contentZoneFile
     *
     * @return array|mixed
     * @throws FileNotFoundException
     */
    protected function loadContentZone(string $contentZoneFile)
    {
        $jsonContent = $this->filesystem->read($contentZoneFile);
        
        return json_decode($jsonContent, false);
    }
    
    
    /**
     * @param string $id
     *
     * @return array|mixed
     * @throws FileNotFoundException
     * @throws InvalidContentZoneException
     * @throws ReflectionException
     */
    public function getById(string $id)
    {
        $jsonObject = $this->loadFirstOnThemeHierarchy($this->currentTheme, $id);
    
        return (new ContentZoneData([$jsonObject]))->getContentZoneById($id);
    }
    
    
    /**
     * @param BasicThemeInterface $theme
     * @param                     $contentZoneId
     *
     * @return array|mixed
     * @throws FileNotFoundException
     * @throws InvalidContentZoneException
     */
    public function loadFirstOnThemeHierarchy(BasicThemeInterface $theme, $contentZoneId)
    {
        $path = implode(DIRECTORY_SEPARATOR,
                        [
                            $theme->id(),
                            'contentzones',
                            $contentZoneId
                        ]);
        if ($this->filesystem->has($path . '.json')) {
            return $this->loadContentZone($path . '.json');
        } elseif ($this->filesystem->has($path . '.default.json')) {
            return $this->loadContentZone($path . '.default.json');
        } elseif ($theme->parent()) {
            return $this->loadFirstOnThemeHierarchy($theme->parent(), $contentZoneId);
        } else {
            throw new InvalidContentZoneException("There is no Content Zone with the ID $contentZoneId!");
        }
    }
    
    
    /**
     * @param ContentZoneOption $option
     *
     * @return string
     */
    protected function createContentZoneJson(ContentZoneOption $option): string
    {
        $jsonObject = $option->jsonSerialize();
        
        if (isset($jsonObject->rows) && is_array($jsonObject->rows) && count($jsonObject->rows)) {
        
            foreach ($jsonObject->rows as $row) {
        
                if (is_string($row->background->items->image->url)) {
        
                    $row->background->items->image->url = $this->convertAbsoluteUrlToRelative($row->background->items->image->url);
                }
                
                if (is_string($row->background->default->image->url)) {
        
                    $row->background->default->image->url = $this->convertAbsoluteUrlToRelative($row->background->default->image->url);
                }
        
                if (isset($row->cols) && is_array($row->cols) && count($row->cols)) {
        
                    foreach ($row->cols as $col) {
        
                        if (is_string($col->background->items['image']->url)) {
        
                            $col->background->items['image']->url = $this->convertAbsoluteUrlToRelative($col->background->items['image']->url);
                        }
                        
                        if (is_string($col->background->default['image']->url)) {
        
                            $col->background->default['image']->url = $this->convertAbsoluteUrlToRelative($col->background->default['image']->url);
                        }
                    }
                }
            }
        }
        
        return json_encode($jsonObject, JSON_PRETTY_PRINT);
    }
    
    
    /**
     * @param string $url
     *
     * @return string
     */
    protected function convertAbsoluteUrlToRelative(string $url): string
    {
        return str_replace($this->shopBaseUrl->value(), '', $url);
    }
}
