<?php
/* --------------------------------------------------------------
   ThemeService.inc.php 2020-09-14
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\GX\Services\System\ThemeSettings\ThemeSettingsService;

/**
 * Class ThemeService
 */
class ThemeService implements ThemeServiceInterface
{
    /**
     * @var ThemeRepositoryInterface
     */
    protected $repository;
    
    /**
     * @var CacheControl
     */
    protected $cacheControl;
    
    /**
     * @var ThemeContentManagerEntryStorage
     */
    private $entryStorage;
    
    /**
     * @var CI_DB_query_builder
     */
    private $queryBuilder;
    
    /**
     * @var ContentWriteService
     */
    private $contentWriteService;
    /**
     * @var RoutineLockerInterface
     */
    private $locker;
    
    
    /**
     * ThemeService constructor.
     *
     * @param ThemeRepositoryInterface        $repository
     * @param CacheControl                    $cacheControl
     * @param ThemeContentManagerEntryStorage $entryStorage
     * @param CI_DB_query_builder             $queryBuilder
     * @param ContentWriteService             $contentWriteService
     * @param RoutineLockerInterface          $locker
     */
    public function __construct(
        ThemeRepositoryInterface $repository,
        CacheControl $cacheControl,
        ThemeContentManagerEntryStorage $entryStorage,
        CI_DB_query_builder $queryBuilder,
        ContentWriteService $contentWriteService,
        RoutineLockerInterface $locker
    ) {
        $this->repository          = $repository;
        $this->cacheControl        = $cacheControl;
        $this->entryStorage        = $entryStorage;
        $this->queryBuilder        = $queryBuilder;
        $this->contentWriteService = $contentWriteService;
        $this->locker              = $locker;
    }
    
    
    /**
     * Get Available themes.
     *
     * @param \ThemeDirectoryRootInterface $source
     *
     * @return \ThemeNameCollection
     */
    public function getAvailableThemes(ThemeDirectoryRootInterface $source)
    {
        return $this->repository->getAvailableThemes($source);
    }
    
    
    /**
     * build a temporary theme.
     *
     * @param ThemeId                $themeId
     * @param ThemeSettingsInterface $settings
     *
     * @return void
     * @throws RoutineLockerException
     */
    public function buildTemporaryTheme(ThemeId $themeId, ThemeSettingsInterface $settings)
    {
        try {
            $this->locker->acquireLock();
            $this->cacheControl->clear_templates_c();
            $theme = $this->repository->getById($themeId, $settings->getSource());
            $this->repository->save($theme, $settings->getDestination());
        } catch (RoutineLockedByAnotherInstanceException $e) {
            //wait at most 20 seconds here to give a change for the other process to finish
            $this->locker->waitUntilLockIsReleasedOrTimeout(20);
        } catch (RoutineLockerException $e) {
            throw $e;
        }
        finally {
            $this->locker->releaseLock();
        }
    }
    
    
    /**
     * Activates a theme for the shop.
     *
     * @param string $themeName
     *
     * @throws Exception
     */
    public function activateTheme($themeName)
    {
        $themeId = ThemeId::create($themeName);
        
        $source      = ThemeDirectoryRoot::create(new ExistingDirectory(DIR_FS_CATALOG . 'themes'));
        $destination = ThemeDirectoryRoot::create(new ExistingDirectory(DIR_FS_CATALOG . 'public/theme'));
        $settings    = ThemeSettings::create($source, $destination);
        
        $this->buildTemporaryTheme($themeId, $settings);
        
        $themeJsonPath = SHOP_ROOT . str_replace('/',
                                                 DIRECTORY_SEPARATOR,
                                                 'themes/' . $themeId->getId() . '/theme.json');
        
        if ($themeId->getId() !== '') {
            $themeJsonStr = file_get_contents($themeJsonPath);
            $themeJson    = json_decode($themeJsonStr, false);
            
            if (isset($themeJson->contents)) {
                $themeContents = ThemeContentsParser::parse($themeJson->contents);
                $this->storeThemeContent($themeId, $themeContents);
            }
        }
        
        $this->clearCache();
        
        /** @var ThemeSettingsService $themeSettingsService */
        $themeSettingsService = StaticGXCoreLoader::getService('ThemeSettings');
        /** This service sets the current theme in the database and in the theme.json */
        $themeSettingsService->activateTheme($themeName, false);
    }
    
    
    /**
     * Clear all theme related cache files
     */
    protected function clearCache(): void
    {
        $this->cacheControl->clear_data_cache();
        $this->cacheControl->clear_content_view_cache();
        $this->cacheControl->clear_templates_c();
        $this->cacheControl->clear_template_cache();
        $this->cacheControl->clear_google_font_cache();
        $this->cacheControl->clear_css_cache();
    }
    
    
    /**
     * Stores the theme contents.
     *
     * @param ThemeId       $themeId
     * @param ThemeContents $themeContents
     *
     * @throws Exception
     */
    public function storeThemeContent(ThemeId $themeId, ThemeContents $themeContents)
    {
        $themeIdStringType = new StringType($themeId->getId());
        
        $contentManagerEntriesCreatedForTheme = $this->entryStorage->contentManagerEntriesCreatedForTheme($themeIdStringType);
        
        if ($contentManagerEntriesCreatedForTheme) {
            $this->storeOnlyNewThemeContent($themeContents);
        } else {
            $this->storeAllThemeContent($themeContents);
        }
        
        $this->entryStorage->storeContentManagerEntriesCreatedForTheme($themeIdStringType);
    }
    
    
    /**
     * Only stores theme content that has an ID and hasnt been stored before.
     *
     * @param ThemeContents $themeContents
     */
    private function storeOnlyNewThemeContent(ThemeContents $themeContents)
    {
        $missingInfoElementContent = [];
        $missingInfoPagesContent   = [];
        $missingLinkPagesContent   = [];
        
        if ($themeContents->infoElements()->count()) {
            /** @var InfoElementContent $infoElement */
            foreach ($themeContents->infoElements()->getArray() as $infoElement) {
                if ($infoElement->id() === null) {
                    continue;
                }
                
                $missingInfoElementContent[] = $infoElement;
            }
        }
        
        if ($themeContents->infoPages()->count()) {
            /** @var InfoPageContent $infoPage */
            foreach ($themeContents->infoPages()->getArray() as $infoPage) {
                if ($infoPage->id() === null) {
                    continue;
                }
                
                $missingInfoPagesContent[] = $infoPage;
            }
        }
        
        if ($themeContents->linkPages()->count()) {
            /** @var LinkPageContent $linkPage */
            foreach ($themeContents->linkPages()->getArray() as $linkPage) {
                if ($linkPage->id() === null) {
                    continue;
                }
                
                $missingLinkPagesContent[] = $linkPage;
            }
        }
        
        $this->contentWriteService->storeInfoElementContentCollection(new InfoElementContentCollection($missingInfoElementContent));
        $this->contentWriteService->storeInfoPageContentCollection(new InfoPageContentCollection($missingInfoPagesContent));
        $this->contentWriteService->storeLinkPageContentCollection(new LinkPageContentCollection($missingLinkPagesContent));
    }
    
    
    /**
     * Stores the theme content.
     *
     * @param ThemeContents $themeContents
     */
    private function storeAllThemeContent(ThemeContents $themeContents)
    {
        $this->contentWriteService->storeInfoElementContentCollection($themeContents->infoElements());
        $this->contentWriteService->storeInfoPageContentCollection($themeContents->infoPages());
        $this->contentWriteService->storeLinkPageContentCollection($themeContents->linkPages());
    }
}