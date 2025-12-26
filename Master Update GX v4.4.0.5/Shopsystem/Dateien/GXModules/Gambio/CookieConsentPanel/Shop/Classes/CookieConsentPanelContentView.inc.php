<?php

/* --------------------------------------------------------------
   CookieConsentPanelContentView.inc.php 2021-12-07
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\CookieConsentPanel\Services\Purposes\Factories\PurposeReaderServiceFactory;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderServiceInterface;
use Gambio\CookieConsentPanel\Storage\CookieConsentPanelStorage;

/**
 * Class CookieConsentPanelContentView
 */
class CookieConsentPanelContentView extends ContentView
{
    protected const DEFAULT_LANGUAGE_CODE = 'de';
    
    /**
     * @var CookieConsentManagerInterface
     */
    private $cookieManager;
    
  
    
    /**
     * @var CookieConsentPanelStorage
     */
    protected $storage;
    
    /**
     * @var PurposeReaderServiceInterface
     */
    protected $purposeReaderService;
    
    
    /**
     * CookieConsentPanelContentView constructor.
     *
     * @param CookieConsentManagerInterface $cookieManager
     *
     * @throws Exception
     */
    public function __construct(CookieConsentManagerInterface $cookieManager)
    {
        parent::__construct();
        $this->cookieManager = $cookieManager;
        
        $this->set_template_dir(DIR_FS_CATALOG . 'GXModules/Gambio/CookieConsentPanel/Shop/Html');
        $this->set_flat_assigns(true);
        $this->set_caching_enabled(false);
        $this->set_content_template('configuration.html');
        $this->set_content_data('data', $this->contentData());
    }
    
    
    /**
     * @return array
     */
    protected function contentData(): array
    {
        $result          = [];
        $contentDataKeys = [
            'label_button_advanced_settings',
            'label_button_yes',
            'label_button_yes_all',
            'label_button_only_essentials',
            'label_cpc_activate_all',
            'label_cpc_deactivate_all',
            'label_cpc_heading',
            'label_cpc_purpose_desc',
            'label_cpc_purpose_optout_confirm_cancel',
            'label_cpc_purpose_optout_confirm_heading',
            'label_cpc_purpose_optout_confirm_proceed',
            'label_cpc_purpose_optout_confirm_text',
            'label_cpc_text',
            'label_intro',
            'label_intro_heading',
            'label_nocookie_head',
            'label_nocookie_text',
            'label_poi_group_list_heading',
            'label_poi_group_list_text',
            'label_third_party',
        ];
    
        foreach ($contentDataKeys as $key) {
    
            $jsonString   = $this->storage()->get($key);
            $jsonObject   = json_decode($jsonString, false);
            
            if (!isset($jsonObject->{$this->getLanguageCodeFromSession()})) {
                
                $result[$key] = str_replace('"','&quot;',$jsonObject->{self::DEFAULT_LANGUAGE_CODE});
                continue;
            }
            
            $result[$key] = str_replace('"','&quot;',$jsonObject->{$this->getLanguageCodeFromSession()});
        }
    
        $result['localeId']       = strtolower($this->getLanguageCodeFromSession())
                                    . strtoupper($this->getLanguageCodeFromSession()) . '_01';
        
        $result['iabVendorListUrl'] = (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG;
        $result['iabVendorListUrl'] .= 'shop.php?do=CookieConsentPanelVendorListAjax/List';
        
        $result['poi_group_name'] = 'GXGROUP';
        
        $result['only_essentials_button_status'] = (bool)(new ConfigurationStorage('modules/GambioCookieConsentPanel'))->get('only_essentials_button_status') ? "true" : "false";
        
        return $result;
    }
    
    /**
     * @return CookieConsentPanelStorage
     */
    protected function storage(): CookieConsentPanelStorage
    {
        if ($this->storage === null) {
            
            $this->storage = new CookieConsentPanelStorage;
        }
        
        return $this->storage;
    }
    
    
    /**
     * @return string
     */
    protected function getLanguageCodeFromSession(): string
    {
        return $_SESSION['language_code'];
    }
}
