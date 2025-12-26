<?php
/*--------------------------------------------------------------------------------------------------
    GoogleMapsWidgetThemeContentView.inc.php 2020-11-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use GXModules\Gambio\StyleEdit\Adapters\Interfaces\TextManagerAdapterInterface;

/**
 * Class GoogleMapsWidgetThemeContentView
 */
class GoogleMapsWidgetThemeContentView extends ThemeContentView
{
    const MAP_WIDGET_PHRASE_TEXT_SECTION = 'mapWidget';
    
    /**
     * @var int
     */
    protected $purposeId;
    
    /**
     * @var MapWidgetCommandConfiguration
     */
    protected $mapWidgetCommandConfiguration;
    
    /**
     * @var TextManagerAdapterInterface
     */
    protected $textManagerAdapter;
    
    
    /**
     * GoogleMapsWidgetThemeContentView constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $tpl_dir = DIR_FS_CATALOG . 'GXModules/Gambio/Widgets/Map/Shop/Html';
        $this->set_template_dir($tpl_dir);
        
        $this->set_content_data('tpl_dir', "{$tpl_dir}/");
        $this->set_content_template('google_maps_widget.html');
        $this->set_flat_assigns(true);
        $this->set_caching_enabled(false);
    }
    
    
    /**
     * @inheritDoc
     */
    public function prepare_data()
    {
        $this->set_content_data('widgetId', $this->mapWidgetCommandConfiguration->widgetId());
        $this->set_content_data('width', $this->mapWidgetCommandConfiguration->width());
        $this->set_content_data('height', $this->mapWidgetCommandConfiguration->height());
        $this->set_content_data('mapsConfig', json_encode($this->mapWidgetCommandConfiguration->mapConfiguration()));
        $this->set_content_data('isPreview', $this->mapWidgetCommandConfiguration->isPreview());
    }
    
    
    /**
     * @param $purposeId
     */
    public function set_google_maps_purpose_id($purposeId)
    {
        $this->set_content_data('googleMapsPurposeId', $purposeId);
    }
    
    
    /**
     * @param $showCookieConsent
     */
    public function set_show_cookie_consent_message($showCookieConsent)
    {
        if ($showCookieConsent) {
            $this->setConsentMessageTranslation();
        }
        
        $this->set_content_data('showCookieConsentMessage', $showCookieConsent);
    }
    
    
    /**
     * @param $showActivateCookiePurposeWarning
     */
    public function set_show_activate_cookie_purpose_warning($showActivateCookiePurposeWarning)
    {
        if ($showActivateCookiePurposeWarning) {
            $this->setConsentWarningTranslation();
        }
        
        $this->set_content_data('showActivateCookiePurposeWarning', $showActivateCookiePurposeWarning);
    }
    
    
    /**
     * @param MapWidgetCommandConfiguration $mapWidgetCommandConfiguration
     */
    public function set_map_widget_command_configuration(MapWidgetCommandConfiguration $mapWidgetCommandConfiguration)
    {
        $this->mapWidgetCommandConfiguration = $mapWidgetCommandConfiguration;
    }
    
    
    /**
     * @param TextManagerAdapterInterface $textManagerAdapter
     */
    public function set_text_manager_adapter(TextManagerAdapterInterface $textManagerAdapter)
    {
        $this->textManagerAdapter = $textManagerAdapter;
    }
    
    
    /**
     * @param $apiKey
     */
    public function set_google_api_key($apiKey)
    {
        $this->set_content_data('apiKey', $apiKey);
    }
    
    
    protected function setConsentMessageTranslation()
    {
        $this->set_content_data('consentTxt',
                                $this->getLanguageTexts(['consent.description', 'consent.button']));
    }
    
    
    protected function setConsentWarningTranslation()
    {
        $this->set_content_data('warningTxt',
                                $this->getLanguageTexts(['consent.warning']));
    }
    
    
    /**
     * @param array $ids
     *
     * @return array
     */
    protected function getLanguageTexts(array $ids)
    {
        $translations = [];
        $languageId = $this->mapWidgetCommandConfiguration->isPreview() ?
            $this->mapWidgetCommandConfiguration->languageId() :
            null;
        
        foreach ($ids as $id) {
            $translations[$id] = $this->textManagerAdapter
                ->getPhraseText($id, self::MAP_WIDGET_PHRASE_TEXT_SECTION, $languageId);
        }
        
        return $translations;
    }
}