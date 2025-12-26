<?php
/*--------------------------------------------------------------------------------------------------
    GambioCookieConsentPanelModuleCenterModule.php 2021-12-07
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\CookieConsentPanel\Storage\CookieConsentPanelStorage;

/**
 * Class GambioCookieConsentPanelModuleCenterModule
 */
class GambioCookieConsentPanelModuleCenterModule  extends AbstractModuleCenterModule
{
    /**
     * @var CookieConsentPanelStorage
     */
    protected $storage;
    
    
    /**
     * @inheritDoc
     */
    protected function _init()
    {
        $this->title       = $this->languageTextManager->get_text('title', 'cookie_consent_panel');
        $this->description = $this->languageTextManager->get_text('description', 'cookie_consent_panel');
        $this->sortOrder   = 28476;
    }
    
    
    /**
     *
     */
    public function install()
    {
        $tableQuery            = 'SHOW TABLES LIKE "gx_configurations";';
        $gxConfigurationExists = count($this->db->query($tableQuery)->result_array()) === 1;
        $table                 = $gxConfigurationExists ? "`gx_configurations`" : "`configuration_storage`";

        $insertQuery = 'INSERT INTO ' . $table . ' (`key`, `value`) VALUES
            (\'modules/GambioCookieConsentPanel/label_button_advanced_settings\', \'{\"de\":\"Weitere Informationen\",\"en\":\"More information\"}\'),
            (\'modules/GambioCookieConsentPanel/label_button_yes\', \'{\"de\":\"Speichern\",\"en\":\"Save\"}\'),
            (\'modules/GambioCookieConsentPanel/active\', \'1\'),
            (\'modules/GambioCookieConsentPanel/only_essentials_button_status\', \'1\'),
            (\'modules/GambioCookieConsentPanel/label_button_yes_all\', \'{\"de\":\"Alle Akzeptieren\",\"en\":\"Accept all\"}\'),
            (\'modules/GambioCookieConsentPanel/label_button_only_essentials\', \'{\"de\":\"Nur Notwendige\",\"en\":\"Only Essentials\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_activate_all\', \'{\"de\":\"Alle aktivieren\",\"en\":\"Activate all\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_01_desc\', \'{\"de\":\"Technisch notwendige Cookies tragen dazu bei, grundlegende Funktionalitäten der Website zu gewährleisten.\",\"en\":\"Technically essential Cookies contribute to ensuring the basic functionalities of the website.\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_01_text\', \'{\"de\":\"Notwendig\",\"en\":\"Essential\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_02_desc\', \'{\"de\":\"Funktionale Cookies helfen, das Benutzererlebnis noch angenehmer zu machen.\",\"en\":\"Functional Cookies help make the user experience more pleasant.\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_02_text\', \'{\"de\":\"Funktional\",\"en\":\"Functional\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_03_desc\', \'{\"de\":\"Statistik Cookies helfen uns zu verstehen, wie unsere Website verwendet wird und wie wir sie verbessern können.\",\"en\":\"Statistical Cookies help us understand how our website is being used and how we can improve it.\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_03_text\', \'{\"de\":\"Statistiken\",\"en\":\"Statistics\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_04_desc\', \'{\"de\":\"Marketing Cookies helfen dabei, unsere Werbemaßnahmen auf die individuellen Interessen unserer Besucher abzustimmen.\",\"en\":\"Marketing Cookies help us adjust our marketing measures to the individual interests of our visitors. \"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_04_text\', \'{\"de\":\"Marketing\",\"en\":\"Marketing\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_05_desc\', \'{\"de\":\"\",\"en\":\"\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_category_05_text\', \'{\"de\":\"Sonstiges\",\"en\":\"Miscellaneous\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_deactivate_all\', \'{\"de\":\"Alle deaktivieren\",\"en\":\"Deactivate all\"}\'),
            (\'modules/GambioCookieConsentPanel/label_cpc_heading\', \'{\"de\":\"Cookie Einstellungen\",\"en\":\"Cookie settings\"}\'),
            (\'modules/GambioCookieConsentPanel/label_intro\', \'{\"de\":\"Wir verwenden Cookies und ähnliche Technologien, auch von Drittanbietern, um die ordentliche Funktionsweise der Website zu gewährleisten, die Nutzung unseres Angebotes zu analysieren und Ihnen ein bestmögliches Einkaufserlebnis bieten zu können. Weitere Informationen finden Sie in unserer <a href=\'\'shop_content.php?coID=2\'\'>Datenschutzerklärung</a>.\",\"en\":\"We use Cookies and other technologies, also from third-party suppliers, to ensure the basic functionalities and analyze the usage of our website in order to provide you with the best shopping experience possible. You can find more information in our <a href=\'\'shop_content.php?coID=2\'\'>Privacy Notice</a>.\"}\'),
            (\'modules/GambioCookieConsentPanel/label_intro_heading\', \'{\"de\":\"Diese Webseite verwendet Cookies und andere Technologien\",\"en\":\"This website uses Cookies and other technologies.\"}\'),
            (\'modules/GambioCookieConsentPanel/label_nocookie_head\', \'{\"de\":\"Keine Cookies erlaubt.\",\"en\":\"No Cookies allowed.\"}\'),
            (\'modules/GambioCookieConsentPanel/label_nocookie_text\', \'{\"de\":\"Bitte aktivieren Sie Cookies in den Einstellungen Ihres Browsers.\",\"en\":\"Please activate Cookies in the settings of your browser. \"}\')';
        
        $this->db->query($insertQuery);
    
        $coo_phrase_cache_builder = MainFactory::create_object('PhraseCacheBuilder', array());
        $coo_phrase_cache_builder->build();
        
        parent::install();
    }
    
    public function uninstall()
    {
        parent::uninstall();
        
        $this->storage()->delete_all();
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
}
