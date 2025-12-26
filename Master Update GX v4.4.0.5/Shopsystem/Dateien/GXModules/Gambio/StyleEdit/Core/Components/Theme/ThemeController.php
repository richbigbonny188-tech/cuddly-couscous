<?php
/*--------------------------------------------------------------------------------------------------
    ThemeController.php 2020-07-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Components\Theme;

use Exception;
use Gambio\StyleEdit\Adapters\Interfaces\ThemeContentImporterAdapterInterface;
use Gambio\StyleEdit\Core\Components\BasicController;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\RequestedThemeInterface;
use Gambio\StyleEdit\Core\Components\Theme\Entities\InvalidThemeIdException;
use Gambio\StyleEdit\Core\Components\Theme\Entities\PreviewThemeSettings;
use Gambio\StyleEdit\Core\Components\Theme\Entities\ThemeConfiguration;
use Gambio\StyleEdit\Core\Components\Theme\Services\ThemeDeleterService;
use Gambio\StyleEdit\Core\Components\Theme\Services\ThemeExtenderService;
use Gambio\StyleEdit\Core\Helpers\StringParser;
use Gambio\StyleEdit\Core\Services\ImportThemeService;
use Gambio\StyleEdit\Core\Services\UploadThemeService;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\Core\TranslatedException;
use GoogleFontDownloader;
use GoogleFontManager;

/**
 * Class ThemeController
 * @package Gambio\StyleEdit\Core\Components\Theme
 */
class ThemeController extends BasicController
{
    
    /**
     * @var ThemeExtenderService
     */
    protected $themeExtender;
    /**
     * @var ThemeDeleterService
     */
    private $deleterService;
    /**
     * @var ImportThemeService
     */
    private $importThemeService;
    /**
     * @var StringParser
     */
    private $stringParser;
    /**
     * @var UploadThemeService
     */
    private $uploadThemeService;
    
    
    /**
     * ThemeController constructor.
     *
     * @param RequestedThemeInterface|null $requestedTheme
     * @param ThemeExtenderService         $themeExtender
     * @param ThemeDeleterService          $deleterService
     */
    public function __construct(
        ThemeExtenderService $themeExtender,
        ThemeDeleterService $deleterService,
        RequestedThemeInterface $requestedTheme = null
    ) {
        parent::__construct($requestedTheme);
        
        $this->themeExtender  = $themeExtender;
        $this->deleterService = $deleterService;
    }
    
    
    /**
     * @param array $uri
     *
     * @return mixed|void
     * @throws Exception
     */
    public function get(array $uri)
    {
        if (count($uri) === 4 && end($uri) === 'font') {
            return $this->additionalGoogleFonts();
        }

        if (count($uri) === 3) {
            if ($this->currentThemeId()) {
                return $this->outputTheme();
            }

            throw new TranslatedException('StyleEdit.exceptions.invalid-theme-id', [end($uri)], 404);
        }

        if (count($uri) === 2) {
            return $this->outputThemeList();
        }
    }
    
    
    /**
     *
     */
    protected function additionalGoogleFonts(): string
    {
        $themeId            = $this->currentThemeId();
        $masterFontVariable = \StyleEditServiceFactory::service()->getMasterFontVariableName();
        $reader             = \StyleEditServiceFactory::service()->getStyleEditReader($themeId);
        $googleFontUrl      = $reader->findSettingValueByName($masterFontVariable);
        $regularExpression  = '/[=\|]([\w\+]+)\:?/m';
        $result             = [];
        
        if (preg_match_all($regularExpression, $googleFontUrl, $matches, PREG_SET_ORDER, 0)) {
            
            //  only use the grouped value
            //  and replace any '+' for a whitespace character
            $matches = array_map(static function ($item) {
                
                return str_replace('+', ' ', end($item));
            },
                $matches);
            
            $requiredFonts = $this->fontManager($googleFontUrl)->requiredFonts();
            
            //  remove fonts that are all ready in the select input
            $matches = array_values(array_filter($matches,
                static function ($item) use ($requiredFonts) {
                    
                    return !in_array($item, $requiredFonts, true);
                }));
            
            if (count($matches)) {
                
                $result = $matches;
            }
        }
        
        // Print fonts that are available, but are not in select input
        return $this->outputJson($result);
    }
    
    
    /**
     * @throws Exception
     */
    protected function outputTheme(): string
    {
        return $this->outputJson($this->themeService()->getConfigurationById($this->currentThemeId()));
    }
    
    
    /**
     * @throws Exception
     */
    protected function outputThemeList(): string
    {
        $themeCollection  = $this->getThemesList();
        $gambioThemes     = [];
        $duplicatedThemes = [];
        foreach ($themeCollection as $theme) {
            /** @var ThemeConfiguration $theme */
            
            if ($theme->author() === "Gambio GmbH" && !preg_match('/\-\s(Copy|Kopie)$/', $theme->title())) {
                $gambioThemes[] = $theme;
            } else {
                $duplicatedThemes[] = $theme;
            }
        }
        //put the gambio themes inthe begning of the list
        $themeList = array_merge($gambioThemes, $duplicatedThemes);
        
        return $this->outputJson($this->themeService()->sortByActive($themeList));
    }
    
    
    /**
     * @param string $fontUrl
     *
     * @return GoogleFontManager
     */
    protected function fontManager(string $fontUrl): \GoogleFontManager
    {
        return new GoogleFontManager(new GoogleFontDownloader, $fontUrl);
    }
    
    
    /**
     * Save some theme
     *
     * @param array $uri
     * @param       $data
     *
     * @return mixed|void
     * @throws Exception
     */
    public function put(array $uri, $data)
    {
        $file     = $this->getStringParser()->toZip($data);
        $response = $this->getUploadThemeService()->upload($file);
        return $this->outputJson($response);
        
        return;
    }
    
    
    /**
     * @return StringParser
     * @throws Exception
     */
    protected function getStringParser(): StringParser
    {
        if ($this->stringParser === null) {
            $this->stringParser = SingletonPrototype::instance()->get(StringParser::class);
            if (!$this->stringParser) {
                throw new Exception('StringParser not published');
            }
        }
        
        return $this->stringParser;
    }
    
    
    /**
     * @return UploadThemeService
     * @throws Exception
     */
    protected function getUploadThemeService(): UploadThemeService
    {
        if ($this->uploadThemeService === null) {
            $this->uploadThemeService = SingletonPrototype::instance()->get(UploadThemeService::class);
            if (!$this->uploadThemeService) {
                throw new Exception('UploadThemeService not published');
            }
        }
        
        return $this->uploadThemeService;
    }
    
    
    /** Duplicate a theme
     *
     * @param array $uri
     * @param       $data
     *
     * @return mixed|void
     * @throws Exception
     */
    public function post(array $uri, $data)
    {
        if (count($uri) >= 3 && strtoupper($uri[2]) === 'IMPORT') {
            $jsonObject   = json_decode($data, false);
            $themeId      = $jsonObject->name ?? null;
            $themeTmpPath = $jsonObject->path ?? null;
            $overwrite    = $jsonObject->overwrite ?? false;
            $response     = $this->getImportThemeService()->import($themeId, $themeTmpPath, $overwrite);
            return $this->outputJson($response);
        }
        
        $theme = $this->themeService()->getConfigurationById($this->currentThemeId());
        //Ex: styleEdit/Theme/HoneyGrid/duplicate
        if ($theme && count($uri) >= 4 && strtoupper($uri[3]) === 'DUPLICATE') {
            return $this->createExtendedTheme($theme, $data);
        } elseif (count($uri) >= 4 && strtoupper($uri[3]) === 'CREATEPREVIEW') {
            
            if (!$theme) {
                throw new TranslatedException('StyleEdit.exceptions.invalid-theme-id', [end($uri)], 404);
            }
            
            if ($theme->isPreview()) {
                throw new TranslatedException('StyleEdit.exceptions.cant-create-preview-of-preview',
                                              [$theme->id()],
                                              404);
            }
            
            $previewThemeId = $theme->id() . '_preview';
            if ($this->getThemesList()->hasKey($previewThemeId)) {
                throw (new TranslatedException('StyleEdit.exceptions.preview-already-exists',
                                               [$theme->id()],
                                               100))->withHttpStatusCode(500);
            }
            
            $jsonObject = (object)[
                'title'   => $theme->title() . ' Preview',
                'id'      => $previewThemeId,
                'preview' => true
            ];
            
            $configFile = $this->themeService()->duplicateTheme($theme, $jsonObject);
            //needs to inherit from parent in order to replicate the behaviour of the source/original theme
            $this->themeService()->patch($configFile->id(),
                                         [
                                             'extends' => $theme->extendsOf()
                                         ]);
            
            $this->updateThemeList();
            $path            = $this->themeService()->createPreviewFolder();
            $previewSettings = new PreviewThemeSettings($previewThemeId, $path, 'cache/smarty/temp/' . $previewThemeId);
            $this->previewSettingsService()->save($previewSettings);
            $this->importThemeContent($theme->id());
            
            return $this->outputJson(['id' => $previewThemeId, 'path' => $path]);
        }
    }
    
    
    /**
     * @return ImportThemeService
     * @throws Exception
     */
    protected function getImportThemeService(): ImportThemeService
    {
        if ($this->importThemeService === null) {
            $this->importThemeService = SingletonPrototype::instance()->get(ImportThemeService::class);
            if (!$this->importThemeService) {
                throw new Exception('ImportThemeService not published');
            }
        }
        
        return $this->importThemeService;
    }
    
    
    /**
     * @param ThemeConfiguration $theme
     * @param                    $data
     *
     * @throws TranslatedException
     */
    public function createExtendedTheme(ThemeConfiguration $theme, $data)
    {
        $jsonObject = json_decode($data, false);
        $jsonObject->updatable = $jsonObject->updatable ?? true;
        
        $id = $this->themeExtender->extendTheme($theme, $jsonObject);
        $this->updateThemeList();
        return $this->outputJson($this->getThemesList()->get($id));
    }
    
    
    /**
     * @param $themeId
     *
     * @throws Exception
     */
    protected function importThemeContent($themeId): void
    {
        /**
         * @var ThemeContentImporterAdapterInterface $importer
         */
        $importer = SingletonPrototype::instance()->get(ThemeContentImporterAdapterInterface::class);
        if ($importer) {
            $importer->importContentFromTheme($themeId);
        }
    }
    
    
    /**
     * @param array $uri
     * @param       $data
     *
     * @return mixed|void
     * @throws Exception
     */
    public function delete(array $uri, $data)
    {
        if ($this->currentThemeId()) {
            $this->deleterService->deleteTheme($this->currentTheme());
        } else {
            throw new InvalidThemeIdException(['Invalid theme']);
        }

        return $this->outputJson(['success' => true]);
    }
    
    
    /**
     * @param array $uri
     * @param       $data
     *
     * @return mixed|void
     * @throws Exception
     */
    public function patch(array $uri, $data)
    {
        $data = json_decode($data, false);
        $this->themeService()->patch($this->currentThemeId(), $data);

        return $this->outputJson(['success' => true]);
    }
    
    
    /**
     * @return BasicController|void
     */
    public function __clone()
    {
    
    }
    
    
    /**
     * @param $id
     *
     * @return ThemeConfiguration
     * @throws TranslatedException
     * @throws \FileNotFoundException
     */
    protected function getTheme($id)
    {
        return $this->themeService()->getConfigurationById($id);
    }
}