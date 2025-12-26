<?php
/*--------------------------------------------------------------------------------------------------
    ControllerFactory.php 2020-01-13
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\CookieConsentPanel\Services\Purposes\Factories\PurposeDeleteServiceFactory;
use Gambio\CookieConsentPanel\Services\Purposes\Factories\PurposeReaderServiceFactory;
use Gambio\CookieConsentPanel\Services\Purposes\Factories\PurposeUpdateServiceFactory;
use Gambio\CookieConsentPanel\Services\Purposes\Factories\PurposeWriteServiceFactory;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDeleteServiceFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDeleteServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderServiceFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateServiceFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriteServiceFactoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriteServiceInterface;
use Gambio\CookieConsentPanel\Storage\CookieConsentPanelStorage;

/**
 * Class CookieConsetPanelControllerFactory
 */
class CookieConsentPanelControllerFactory
{
    /**
     * @var PurposeDeleteServiceFactoryInterface
     */
    protected $purposeDeleteFactory;
    
    /**
     * @var PurposeReaderServiceFactoryInterface
     */
    protected $purposeReaderFactory;
    
    /**
     * @var PurposeUpdateServiceFactoryInterface
     */
    protected $purposeUpdateFactory;
    
    /**
     * @var PurposeWriteServiceFactoryInterface
     */
    protected $purposeWriteFactory;
    
    /**
     * @var KeyValueCollection
     */
    protected $queryParameters;
    
    
    /**
     * @return bool|mixed
     */
    public function languageProvider() : LanguageProviderInterface
    {
        return MainFactory::create(LanguageProvider::class, StaticGXCoreLoader::getDatabaseQueryBuilder());
    }
    
    
    /**
     * @return array|bool|mixed
     */
    public function storage()
    {
        return  MainFactory::create(CookieConsentPanelStorage::class);
    }
    
    
    /**
     * @return PurposeReaderServiceFactory
     */
    protected function purposeReaderFactory(): PurposeReaderServiceFactory
    {
        if ($this->purposeReaderFactory === null) {
            $this->purposeReaderFactory = new PurposeReaderServiceFactory();
        }
        
        return $this->purposeReaderFactory;
    }
    
    
    
    /**
     * @return PurposeDeleteServiceFactoryInterface
     */
    protected function purposeDeleteFactory(): PurposeDeleteServiceFactoryInterface
    {
        if ($this->purposeDeleteFactory === null) {
            $this->purposeDeleteFactory = new PurposeDeleteServiceFactory();
        }
        
        return $this->purposeDeleteFactory;
    }
    
    
    /**
     * @return PurposeWriteServiceFactoryInterface
     */
    protected function purposeWriteFactory(): PurposeWriteServiceFactoryInterface
    {
        if ($this->purposeWriteFactory === null) {
            $this->purposeWriteFactory = new PurposeWriteServiceFactory();
        }
        
        return $this->purposeWriteFactory;
    }
    
    
    /**
     * @return PurposeUpdateServiceFactoryInterface
     */
    protected function purposeUpdateFactory(): PurposeUpdateServiceFactoryInterface
    {
        if ($this->purposeUpdateFactory === null) {
            $this->purposeUpdateFactory = new PurposeUpdateServiceFactory();
        }
        
        return $this->purposeUpdateFactory;
    }
    
    
    /**
     * @return PurposeReaderServiceInterface
     */
    public function purposeReaderService(): PurposeReaderServiceInterface
    {
        return $this->purposeReaderFactory()->service();
    }
    
    
    /**
     * @return PurposeDeleteServiceInterface
     */
    public function purposeDeleteService(): PurposeDeleteServiceInterface
    {
        return $this->purposeDeleteFactory()->service();
    }
    
    
    /**
     * @return PurposeWriteServiceInterface
     */
    public function purposeWriteService(): PurposeWriteServiceInterface
    {
        return $this->purposeWriteFactory()->service();
    }
    
    
    /**
     * @return PurposeUpdateServiceInterface
     */
    public function purposeUpdateService(): PurposeUpdateServiceInterface
    {
        return $this->purposeUpdateFactory()->service();
    }
    
    
    /**
     * @param NonEmptyStringType $title
     * @param ExistingFile       $template
     * @param KeyValueCollection $data
     *
     * @param AssetCollection    $assets
     *
     * @param KeyValueCollection $queryParameters
     *
     * @return bool|mixed
     */
    public function createResponse(
        NonEmptyStringType $title,
        ExistingFile $template,
        KeyValueCollection $data,
        AssetCollection $assets,
        KeyValueCollection $queryParameters
    ) : AdminLayoutHttpControllerResponse
    {
        $this->queryParameters = $queryParameters;
        return MainFactory::create(AdminLayoutHttpControllerResponse::class, $title, $template, $data, $assets, $this->createNavigation());
    }
    
    /**
     * @param array $collectionData
     *
     * @return array|bool|mixed
     */
    public function createCollection(array $collectionData)
    {
        return MainFactory::create('KeyValueCollection', $collectionData);
    }
    
    
    /**
     * @return ContentNavigationCollection
     */
    protected function createNavigation(): ContentNavigationCollection
    {
        $textManager = $this->languageTextManagerCookie();
        
        
        
        $nav = [
            $textManager->get_text('label_cpc_general_tab')    => $this->queryParameters->keyExists('activetab') ? 'admin.php?do=GambioCookieConsentPanelModuleCenterModule' : '',
            $textManager->get_text('label_cpc_categories_tab') => $this->isTabActive('categories') ? '' : 'admin.php?do=GambioCookieConsentPanelModuleCenterModule&activetab=categories',
            $textManager->get_text('label_cpc_purposes_tab')   => $this->isTabActive('purposes') ? '' : 'admin.php?do=GambioCookieConsentPanelModuleCenterModule&activetab=purposes',
        ];
        
        return new ContentNavigationCollection($nav);
    }
    
    
    /**
     * @param string             $tabName
     *
     * @return bool
     */
    protected function isTabActive(string $tabName): bool
    {
        return $this->queryParameters->keyExists('activetab') && $this->queryParameters->getValue('activetab') === $tabName;
    }
    
    /**
     * @return LanguageTextManager
     */
    protected function languageTextManagerCookie(): LanguageTextManager
    {
        return new LanguageTextManager('cookie_consent_panel');
    }
}