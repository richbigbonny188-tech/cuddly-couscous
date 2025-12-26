<?php
/* --------------------------------------------------------------
  JanolawModuleCenterModuleController.inc.php 2020-06-09
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
*/

/**
 * Class JanolawModuleCenterModuleController
 * @extends    AbstractModuleCenterModuleController
 * @category   System
 * @package    Modules
 * @subpackage Controllers
 */
class JanolawModuleCenterModuleController extends AbstractModuleCenterModuleController
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var array $configurationKeys
     */
    protected $configurationKeys = [];
    
    
    protected function _init()
    {
        $gxCoreLoader = MainFactory::create('GXCoreLoader', MainFactory::create('GXCoreLoaderSettings'));
        $this->db     = $gxCoreLoader->getDatabaseQueryBuilder();
        
        $this->configurationKeys = [
            'configuration/MODULE_GAMBIO_JANOLAW_STATUS',
            'configuration/MODULE_GAMBIO_JANOLAW_USER_ID',
            'configuration/MODULE_GAMBIO_JANOLAW_SHOP_ID',
            'configuration/MODULE_GAMBIO_JANOLAW_USE_IN_PDF'
        ];
        
        $this->redirectUrl = xtc_href_link('gm_janolaw.php');
        
        $this->pageTitle = $this->languageTextManager->get_text('janolaw_title');
    }
    
    
    /**
     * Returns an AdminLayoutHttpControllerResponse with the janolaw configuration template
     *
     * @return AdminLayoutHttpControllerResponse
     */
    public function actionConfig()
    {
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/module_center/');
        
        $versionInfo = [
            'version'   => 0,
            'multilang' => false,
        ];
        if (MODULE_GAMBIO_JANOLAW_STATUS !== 'False') {
            $janolaw     = MainFactory::create('GMJanolaw');
            $versionInfo = $janolaw->versionCheck();
        }
        
        $confs         = $this->_getConfiguration();
        $template      = 'module_center/janolaw_configuration.html';
        $data          = [
            'configuration'  => $confs,
            'info_page_link' => xtc_href_link('gm_janolaw.php'),
            'version_info'   => $versionInfo,
        ];
        $subNavigation = [
            [
                'text'   => $this->languageTextManager->get_text('info_page', 'janolaw_configuration'),
                'link'   => $data['info_page_link'] ?? '',
                'active' => false,
            ],
            [
                'text'   => $this->languageTextManager->get_text('configuration', 'janolaw_configuration'),
                'link'   => '',
                'active' => true,
            ],
        ];
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($this->pageTitle,
                                                                                  $template,
                                                                                  $data,
                                                                                  [],
                                                                                  $subNavigation);
    }
    
    
    /**
     * Save janolaw configuration
     *
     * @return RedirectHttpControllerResponse
     */
    public function actionStore()
    {
        if ($this->_getPostData('configure_contents') !== null) {
            $this->configureContents();
            isset($GLOBALS['messageStack']) or $GLOBALS['messageStack'] = new messageStack();
            $GLOBALS['messageStack']->add_session($this->languageTextManager->get_text('janolaw_contents_configured'),
                                                  'info');
        } else {
            $versioninfo_cache_file = DIR_FS_CATALOG . 'cache/janolaw-versioninfo.pdc';
            @unlink($versioninfo_cache_file);
            $this->_store($this->_getPostDataCollection());
        }
        
        $url = xtc_href_link('admin.php', 'do=JanolawModuleCenterModule/Config');
        
        return MainFactory::create('RedirectHttpControllerResponse', $url);
    }
    
    
    /**
     * configures content manager entries to use Janolaw media
     */
    protected function configureContents()
    {
        $db          = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $languages   = $this->getLanguages();
        $janolaw     = MainFactory::create('GMJanolaw');
        $versionInfo = $janolaw->versionCheck();
        
        $contentFiles = [
            2       => 'janolaw_datenschutz.php',
            3       => 'janolaw_agb.php',
            4       => 'janolaw_impressum.php',
            3889896 => 'janolaw_widerruf.php',
        ];
        foreach ($contentFiles as $content_group => $content_file) {
            $db->set('content_file', $content_file)
                ->set('content_type', 'file')
                ->where('content_group', $content_group)
                ->update('content_manager');
        }
        
        if ($versionInfo['version'] == 3) {
            $downloadFiles = [
                2       => 'datasecurity',
                3       => 'terms',
                4       => 'legaldetails',
                3889896 => 'revocation',
            ];
            
            foreach ($languages as $language_id => $language_code) {
                if ($versionInfo['multilang'] === false) {
                    $language_code = 'de';
                }
                $withdrawalFormPdf = $janolaw->get_pdf_file('model-withdrawal-form', $language_code);
                if ($withdrawalFormPdf !== false) {
                    gm_set_content('WITHDRAWAL_FORM_FILE', basename($withdrawalFormPdf), $language_id);
                } else {
                    gm_set_content('WITHDRAWAL_FORM_FILE', '', $language_id);
                }
                foreach ($downloadFiles as $content_group => $download_file) {
                    $pdfFile = $janolaw->get_pdf_file($download_file, $language_code);
                    if ($pdfFile !== false) {
                        $db->set('download_file', basename($pdfFile));
                        $db->where('content_group', $content_group);
                        $db->where('languages_id', $language_id);
                        $db->update('content_manager');
                    }
                }
            }
        }
    }
    
    
    protected function getLanguages()
    {
        $supportedLanguages = ['de', 'en', 'fr'];
        $db                 = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $languages          = [];
        foreach ($db->get('languages')->result() as $row) {
            if (in_array($row->code, $supportedLanguages)) {
                $languages[$row->languages_id] = $row->code;
            }
        }
        
        return $languages;
    }
    
    
    /**
     * Update janolaw configuration in the database
     *
     * @param KeyValueCollection $userInputCollection
     */
    protected function _store(KeyValueCollection $userInputCollection)
    {
        foreach ($userInputCollection->getArray() as $configurationKey => $configurationValue) {
            $this->db->set('value', trim($configurationValue))
                ->where('key',
                        "configuration/$configurationKey")
                ->update('gx_configurations');
        }
    }
    
    
    /**
     * Loads the janolaw configuration from the database
     *
     * @return array $janolawConfiguration
     */
    protected function _getConfiguration()
    {
        $janolawConfiguration       = [];
        $janolawConfigurationResult = $this->db->select('key, value')
            ->from('gx_configurations')
            ->where_in('key', $this->configurationKeys)
            ->get();
        foreach ($janolawConfigurationResult->result() as $row) {
            $key                        = str_replace('configuration/', '', $row->key);
            $janolawConfiguration[$key] = $row->value;
        }
        
        return $janolawConfiguration;
    }
}
