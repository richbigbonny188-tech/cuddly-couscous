<?php
/*--------------------------------------------------------------------------------------------------
    GambioCookieConsentPanelModuleCenterModuleController.php 2021-12-07
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\CategoryDto;
use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\PurposeReaderDto;
use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\PurposeUpdateDto;
use Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects\PurposeWriterDto;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDeleteServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeReaderServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateServiceInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriteServiceInterface;
use Gambio\CookieConsentPanel\Storage\CookieConsentPanelStorage;

/**
 * Class GambioCookieConsentPanelModuleCenterModuleController
 */
class GambioCookieConsentPanelModuleCenterModuleController extends AbstractModuleCenterModuleController
{
    /**
     * Configuration storage
     * @var CookieConsentPanelStorage
     */
    protected $configuration;
    /**
     * @var CookieConsentPanelControllerFactory
     */
    protected $factory;
    /**
     * @var LanguageProvider
     */
    protected $languageProvider;
    /**
     * @var PurposeDeleteServiceInterface
     */
    protected $purposeDeleterService;
    /**
     * @var PurposeReaderServiceInterface
     */
    protected $purposeReaderService;
    /**
     * @var PurposeUpdateServiceInterface
     */
    protected $purposeUpdaterService;
    /**
     * @var PurposeWriteServiceInterface
     */
    protected $purposeWriterService;
    
    
    /**
     * @return void
     */
    public function actionCreatePurpose()
    {
        $categoryId   = $this->_getPostData('category_id');
        $names        = $this->_getPostData('names');
        $descriptions = $this->_getPostData('descriptions');
        $status       = $this->_getPostData('status') ? : false;
        $dto          = new PurposeWriterDto($categoryId, $descriptions, $names, $status, true, '');
        $purpose_id = $this->purposeWriterService->store($dto);
        if ($categoryId > 1) {
            $_SESSION["lastInsertedPurpose"] = $purpose_id;
        }
        
        xtc_redirect(xtc_href_link('admin.php', 'do=GambioCookieConsentPanelModuleCenterModule&activetab=purposes'));
    }
    
    
    /**
     * Return the default page
     * @return AdminLayoutHttpControllerResponse Layout response
     * @throws Exception
     */
    public function actionDefault()
    {
        $page = $this->_getQueryParameter('activetab') ? : 'general';
        
        return $this->loadPage($page);
    }
    
    
    /**
     * @param string $activeTab
     *
     * @return AdminLayoutHttpControllerResponse
     * @throws Exception
     */
    public function loadPage(string $activeTab): AdminLayoutHttpControllerResponse
    {
        $purposes   = $this->mapPurposes($this->purposeReaderService->allPurposes());
        $categories = $this->mapCategories($this->purposeReaderService->categories($_SESSION['languages_id']));
        $title      = new NonEmptyStringType($this->pageTitle);
        $template   = $this->getTemplateFile('cookie_consent_panel_configuration.html');
        
        $collectionData = [
            'pageToken'                    => $_SESSION['coo_page_token']->generate_token(),
            'configuration'                => $this->getGeneralConfiguration(),
            'activeTab'                    => $activeTab,
            'purposes'                     => $purposes,
            'categories'                   => $categories,
            'activeLanguageCode'           => strtolower($_SESSION['language_code']),
            'activeLanguageId'             => strtolower($_SESSION['languages_id']),
            'languages'                    => $this->getLanguageData(),
            'action_save_configuration'    => xtc_href_link('admin.php',
                                                            'do=GambioCookieConsentPanelModuleCenterModule/Save'),
            'action_create_purpose'        => xtc_href_link('admin.php',
                                                            'do=GambioCookieConsentPanelModuleCenterModule/createPurpose'),
            'action_update_purpose'        => xtc_href_link('admin.php',
                                                            'do=GambioCookieConsentPanelModuleCenterModule/updatePurpose'),
            'action_update_purpose_status' => xtc_href_link('admin.php',
                                                            'do=GambioCookieConsentPanelModuleCenterModule/updatePurposeStatus'),
            'action_delete_purpose'        => xtc_href_link('admin.php',
                                                            'do=GambioCookieConsentPanelModuleCenterModule/deletePurpose'),
            'action_save_general'          => xtc_href_link('admin.php',
                                                            'do=GambioCookieConsentPanelModuleCenterModule/saveGeneral'),
        ];
        
        if (isset($_SESSION['lastInsertedPurpose'])) {
            $collectionData['lastInsertedPurpose'] = $_SESSION['lastInsertedPurpose'];
            unset($_SESSION['lastInsertedPurpose']);
        } else {
            $collectionData['lastInsertedPurpose'] = false;
        }
        
        $collectionData = $this->appendConfigurationStorageVariablesToDataCollection($collectionData);
        
        $data = $this->factory->createCollection($collectionData);
        
        $assetsBase = '../GXModules/Gambio/CookieConsentPanel';
        $assets     = new AssetCollection();
        $assets->add(new Asset("${assetsBase}/Admin/Styles/cookieConsentPanel.css"));
        
        return $this->factory->createResponse($title, $template, $data, $assets, $this->_getQueryParametersCollection());
    }
    
    
    /**
     * @param PurposeInterface[] $purposes
     *
     * @return array
     */
    protected function mapPurposes($purposes)
    {
        $result = [];
        /**
         * $purpose PurposeInterface
         */
        foreach ($purposes as $purpose) {
            $result[] = new PurposeReaderDto($purpose->category()->id(),
                                             [$_SESSION['languages_id'] => $purpose->category()->name()],
                                             $purpose->description()->value(),
                                             $purpose->name()->value(),
                                             $purpose->status()->isActive(),
                                             $purpose->deletable()->value(),
                                             $purpose->alias()->value(),
                                             $purpose->id()->value());
        }
        
        return $result;
    }
    
    
    /**
     * @param CategoryInterface[] $categories
     *
     * @return CategoryInterface[]
     */
    protected function mapCategories(array $categories)
    {
        $result = [];
        /**
         * $purpose CategoryInterface
         */
        foreach ($categories as $category) {
            $result[] = new CategoryDto($category->name(), $category->id());
        }
        
        return $result;
    }
    
    
    /**
     * @return array
     */
    protected function getGeneralConfiguration()
    {
        $languages = $this->getLanguageData();
        
        $result = [
            'label_intro_heading'                      => null,
            'label_button_advanced_settings'           => null,
            'label_button_yes'                         => null,
            'label_button_yes_all'                     => null,
            'label_button_only_essentials'             => null,
            'label_cpc_activate_all'                   => null,
            'label_cpc_deactivate_all'                 => null,
            'label_cpc_heading'                        => null,
            'label_intro'                              => null,
            'label_nocookie_head'                      => null,
            'label_nocookie_text'                      => null,
        ];
        
        $configurations = $this->configuration->get_all();
        foreach ($result as $key => $value) {
            $data = isset($configurations[$key]) ? json_decode($configurations[$key], true) : [];
            
            $newValue = [];
            foreach ($languages as $lang) {
                $newValue[$lang['code']] = isset($data[$lang['code']]) ? $data[$lang['code']] : null;
            }
            
            $result[$key] = $newValue;
        }
        
        return $result;
    }
    
    
    /**
     * @return array
     */
    protected function getLanguageData(): array
    {
        $result = [];
        foreach ($this->languageProvider->getAdminCodes()->getArray() as $languageCode) {
            $result[] = [
                'code' => strtolower($languageCode->asString()),
                'icon' => $this->languageProvider->getIconFilenameByCode($languageCode),
                'id'   => $this->languageProvider->getIdByCode($languageCode)
            ];
        }
        
        return $result;
    }
    
    
    /**
     * @param array $data
     *
     * @return array
     */
    protected function appendConfigurationStorageVariablesToDataCollection(array $data): array
    {
        $all = $this->configuration->get_all();
        
        $data['active'] = $all['active'];
        $data['only_essentials_button_status'] = $all['only_essentials_button_status'];
        unset($all['active']);
        unset($all['only_essentials_button_status']);
        
        foreach ($all as $key => $langJson) {
            
            $langObj = json_decode($langJson);
            
            foreach ($langObj as $languageCode => $value) {
                
                $data[$key . '_' . $languageCode] = $value;
            }
        }
        
        return $data;
    }
    
    
    /**
     * @return void
     */
    public function actionDeletePurpose()
    {
        $purposeId = (int)$this->_getPostData('id');
        $this->purposeDeleterService->deleteByPurposeId($purposeId);
        xtc_redirect(xtc_href_link('admin.php', 'do=GambioCookieConsentPanelModuleCenterModule&activetab=purposes'));
    }
    
    
    /**
     * @return void
     */
    public function actionSaveGeneral()
    {
        $generalConfiguration          = $this->_getPostData('general');
        $active                        = $this->_getPostData('active') ? : false;
        $only_essentials_button_status = $this->_getPostData('only_essentials_button_status') ? : false;
        $this->configuration->set('active', $active);
        $this->configuration->set('only_essentials_button_status', $only_essentials_button_status);
        
        foreach ($generalConfiguration as $key => $value) {
            $this->configuration->set($key, json_encode($value));
        }
        xtc_redirect(xtc_href_link('admin.php', 'do=GambioCookieConsentPanelModuleCenterModule'));
    }
    
    
    /**
     * @return void
     */
    public function actionUpdatePurpose()
    {
        if ($this->isLegacyShop() === false) {

            $id = $this->_getPostData('id');
            $categoryId = $this->_getPostData('category_id');
            $names = $this->_getPostData('names');
            $descriptions = $this->_getPostData('descriptions');
            $status = $this->_getPostData('status') ?: false;
            $alias = $this->_getPostData('alias') ?: null;
            $dto = new PurposeUpdateDto($categoryId, $descriptions, $names, $status, $alias, $id);
            $this->purposeUpdaterService->update($dto);
            xtc_redirect(xtc_href_link('admin.php', 'do=GambioCookieConsentPanelModuleCenterModule&activetab=purposes'));
        } else {

            $id           = $this->_getPostData('id');
            $categoryId   = $this->_getPostData('category_id');
            $names        = $this->_getPostData('names');
            $descriptions = $this->_getPostData('descriptions');
            $status       = $this->_getPostData('status') ? : false;
            $dto          = new PurposeUpdateDto($categoryId, $descriptions, $names, $status, null, '', $id);
            $this->purposeUpdaterService->update($dto);
            xtc_redirect(xtc_href_link('admin.php', 'do=GambioCookieConsentPanelModuleCenterModule&activetab=purposes'));
        }
    }

    /**
     * Determine if the shop version is lower than GX 4.1.0.0
     *
     * @return bool
     */
    protected function isLegacyShop(): bool
    {
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();

        $tableQuery            = 'SHOW TABLES LIKE "gx_configurations";';
        $gxConfigurationExists = count($db->query($tableQuery)->result_array()) === 1;

        return $gxConfigurationExists === false;
    }
    
    /**
     * @return void
     */
    public function actionUpdatePurposeStatus()
    {
        $id      = (int)$this->_getQueryParameter('id');
        $status  = (bool)$this->_getQueryParameter('status');
        $purpose = $this->purposeUpdaterService->updateStatus($id, $status);
        xtc_redirect(xtc_href_link('admin.php', 'do=GambioCookieConsentPanelModuleCenterModule&activetab=purposes'));
    }
    
    
    /**
     * @inheritDoc
     */
    protected function _init()
    {
        $this->pageTitle = $this->languageTextManager->get_text('title', 'cookie_consent_panel');
        
        $this->factory               = new CookieConsentPanelControllerFactory();
        $this->configuration         = $this->factory->storage();
        $this->purposeReaderService  = $this->factory->purposeReaderService();
        $this->purposeDeleterService = $this->factory->purposeDeleteService();
        $this->purposeWriterService  = $this->factory->purposeWriteService();
        $this->purposeUpdaterService = $this->factory->purposeUpdateService();
        $this->languageProvider      = $this->factory->languageProvider();
    }
}
