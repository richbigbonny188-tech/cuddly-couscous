<?php
/*--------------------------------------------------------------------------------------------------
    MapWidgetOutputCommand.php 2020-11-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use GXModules\Gambio\CookieConsentPanel\Shop\Classes\DTO\CookieConsentGoogleMapsPurposeDTO;
use GXModules\Gambio\StyleEdit\Adapters\Interfaces\ConfigurationAdapterInterface;
use GXModules\Gambio\StyleEdit\Adapters\Interfaces\TextManagerAdapterInterface;

class MapWidgetOutputCommand
{
    
    /**
     * @var int
     */
    protected $cookieConsentPurposeId;
    
    
    /**
     * @var string
     */
    protected $googleApiKey;
    
    
    /**
     * @var MapWidgetCommandConfiguration
     */
    protected $configuration;
    
    
    /**
     * @var CookieConsentPurposeReaderServiceInterface
     */
    protected $consentPurposeReaderService;
    
    
    /**
     * @var TextManagerAdapterInterface
     */
    protected $textManagerAdapter;
    
    
    /**
     * @var ConfigurationAdapterInterface
     */
    protected $configurationAdapter;
    
    
    public function __construct(
        MapWidgetCommandConfiguration $configuration,
        CookieConsentPurposeReaderServiceInterface $consentPurposeReaderService,
        TextManagerAdapterInterface $textManagerAdapter,
        ConfigurationAdapterInterface $configurationAdapter
    ) {
        $this->configuration = $configuration;
        $this->consentPurposeReaderService = $consentPurposeReaderService;
        $this->textManagerAdapter = $textManagerAdapter;
        $this->configurationAdapter = $configurationAdapter;
    
        $this->contentView = MainFactory::create(GoogleMapsWidgetThemeContentView::class);
    }
    
    
    /**
     * @return string
     */
    public function execute()
    {
        $this->contentView->set_map_widget_command_configuration($this->configuration);
        $this->contentView->set_text_manager_adapter($this->textManagerAdapter);
        $this->contentView->set_google_maps_purpose_id($this->getCookieConsentPurposeId());
        
        $this->contentView->set_show_activate_cookie_purpose_warning(
            ($this->configuration->isPreview() && $this->showActivateCookiePurposeWarning())
        );
        $this->contentView->set_show_cookie_consent_message($this->showCookieConsentMessage());
        $this->contentView->set_google_api_key($this->googleApiKey());
    
        return $this->contentView->get_html();
    }
    
    /**
     * @return bool
     */
    protected function showCookieConsentMessage()
    {
        if (cookie_consent_panel_is_installed()) {
            $purposeID = $this->getCookieConsentPurposeId();
            
            return cookie_purpose_is_active($purposeID) && !cookie_purpose_is_enabled($purposeID);
        }
        
        return false;
    }
    
    
    /**
     * @return bool
     */
    protected function showActivateCookiePurposeWarning()
    {
        if (cookie_consent_panel_is_installed()) {
            $purposeID = $this->getCookieConsentPurposeId();
            
            return !cookie_purpose_is_active($purposeID);
        }
        
        return false;
    }
    
    
    /**
     * @return int
     */
    public function getCookieConsentPurposeId(): int
    {
        if (!$this->cookieConsentPurposeId) {
            $this->cookieConsentPurposeId = $this->consentPurposeReaderService
                ->getCookieConsentPurposeBy(new CookieConsentGoogleMapsPurposeDTO())
                ->purposeCode();
        }
        
        return $this->cookieConsentPurposeId;
    }
    
    
    /**
     * @return string
     */
    protected function googleApiKey(): string
    {
        if (!$this->googleApiKey) {
            $this->googleApiKey = $this->configurationAdapter->get('GOOGLE_API_KEY')->value();
        }
    
        return $this->googleApiKey;
    }
    
}