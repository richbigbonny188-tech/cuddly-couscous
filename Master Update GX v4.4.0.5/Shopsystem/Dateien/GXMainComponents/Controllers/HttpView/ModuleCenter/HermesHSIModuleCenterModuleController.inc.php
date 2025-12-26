<?php
/* --------------------------------------------------------------
   HermesHSIModuleCenterModuleController.inc.php 2021-03-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/
declare(strict_types=1);

class HermesHSIModuleCenterModuleController extends AbstractModuleCenterModuleController
{
    /**
     * @var bool|HermesHSIConfigurationStorage
     */
    protected $configurationStorage;
    
    /** @var HermesHSILogger */
    protected $logger;
    
    
    /**
     * @return AdminLayoutHttpControllerResponse|bool|mixed|RedirectHttpControllerResponse
     * @throws Exception
     */
    public function actionDefault()
    {
        $title    = new NonEmptyStringType($this->languageTextManager->get_text('hermeshsi_title'));
        $template = $this->getTemplateFile('hermeshsi_configuration.html');
        
        /** @var OrderStatusService $orderStatusService */
        $orderStatusService    = StaticGXCoreLoader::getService('OrderStatus');
        $orderStatusCollection = $orderStatusService->findAll();

        $parcelServiceCollection = $this->getParcelServices();
    
        /** @var CountryService $countryService */
        $countryService = StaticGXCoreLoader::getService('Country');
        $shopCountry    = $countryService->getCountryById(new IdType(STORE_COUNTRY));
        $locale         = $_SESSION['language_code'] . '_' . $shopCountry->getIso2();
        $languageCode   = MainFactory::create('LanguageCode', new StringType($_SESSION['language_code']));
        $countries      = array_merge(HermesHSICountriesHelper::getCountries(true, $locale),
                                      HermesHSICountriesHelper::getCountries(false, $locale));
        
        $data = MainFactory::create('KeyValueCollection',
                                    [
                                        'pageToken'                 => $_SESSION['coo_page_token']->generate_token(),
                                        'action_save_configuration' => xtc_href_link('admin.php',
                                                                                     'do=HermesHSIModuleCenterModule/SaveConfiguration'),
                                        'action_authenticate'       => xtc_href_link('admin.php',
                                                                                     'do=HermesHSIModuleCenterModule/Authenticate'),
                                        'configuration'             => $this->configurationStorage->get_all(),
                                        'languageCode'              => $languageCode,
                                        'orderStatusCollection'     => $orderStatusCollection,
                                        'parcelServiceCollection'   => $parcelServiceCollection,
                                        'countries'                 => $countries,
                                    ]);
        
        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, $data);
    }
    
    
    /**
     * Workaround for an often-occurring misconfiguration where an empty parcel service URL would throw an InvalidArgumentException
     *
     * @return ParcelServiceCollection
     * @throws ParcelServiceCollectionNotFoundException
     */
    private function getParcelServices(): ParcelServiceCollection
    {
        try {
            $parcelServiceReadService = ParcelServiceServiceFactory::readService();
            $parcelServiceCollection  = $parcelServiceReadService->getAll();
        } catch (InvalidArgumentException $e) {
            /** @var CI_DB_query_builder $db */
            $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
            /** @var CI_DB_result $result */
            $parcelServicesRows = $db->get('parcel_services')->result_array();
            $parcelServices     = [];
            foreach ($parcelServicesRows as $row) {
                $parcelServiceDescriptionsRows = $db->get_where('parcel_services_description',
                                                                'parcel_service_id = ' . (int)$row['parcel_service_id'])
                    ->result_array();
                $parcelServiceDescriptions     = [];
                foreach ($parcelServiceDescriptionsRows as $descriptionsRow) {
                    $url = $descriptionsRow['url'];
                    if ($url === '') {
                        $url = 'https://www.example.org/';
                    }
                    $parcelServiceDescriptions[] = ParcelServiceDescription::create(ParcelServiceDescriptionId::create(ParcelServiceId::create($row['parcel_service_id']),
                                                                                                                       $descriptionsRow['language_id']),
                                                                                    $url,
                                                                                    $descriptionsRow['comment']);
                }
                $parcelServices[] = GXParcelService::create(ParcelServiceId::create((int)$row['parcel_service_id']),
                                                            $row['name'],
                                                            $row['default'],
                                                            ParcelServiceDescriptionCollection::collect($parcelServiceDescriptions));
            }
            $parcelServiceCollection = ParcelServiceCollection::collect($parcelServices);
        }
        return $parcelServiceCollection;
    }
    
    public function actionSaveConfiguration()
    {
        $this->_validatePageToken();
        $this->logger->info('saving configuration');
        $newConfiguration                = $this->_getPostDataCollection()->getArray()['configuration'];
        $newConfiguration['apiUser']     = strip_tags(trim($newConfiguration['apiUser']));
        $newConfiguration['apiPassword'] = strip_tags(trim($newConfiguration['apiPassword']));
        
        $hermesHSIService = MainFactory::create('HermesHSIService', $this->configurationStorage);
        $hermesHSIService->setLogger($this->logger);
        
        if ((bool)$newConfiguration['testMode'] !== (bool)$this->configurationStorage->get('testMode')) {
            $hermesHSIService->markAccessTokenCachesAsDirty();
            $this->configurationStorage->set('testMode', $newConfiguration['testMode']);
        }
        
        // check credentials
        try {
            $accessTokenResponse = $hermesHSIService->authenticateUser($newConfiguration['apiUser'],
                                                                       $newConfiguration['apiPassword']);
            $this->logger->info('credentials are valid');
        } catch (HermesHSIAuthenticationFailedException $e) {
            $this->logger->info('credentials could not be verified, storing empty credentials');
            $GLOBALS['messageStack']->add_session($this->languageTextManager->get_text('hermeshsi_credentials_invalid'));
            $newConfiguration['apiUser']     = '';
            $newConfiguration['apiPassword'] = '';
        }
        
        // store configuration
        foreach ($this->configurationStorage->get_all() as $key => $value) {
            if (array_key_exists($key, $newConfiguration)) {
                $this->configurationStorage->set($key, $newConfiguration[$key]);
            }
        }
        $GLOBALS['messageStack']->add_session($this->languageTextManager->get_text('configuration_saved'), 'info');
        $this->logger->info('configuration saved');
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php', 'do=HermesHSIModuleCenterModule'));
    }
    
    
    /**
     * Initialize the module e.g. set title, description, sort order etc.
     *
     * Function will be called in the constructor
     */
    protected function _init(): void
    {
        $this->pageTitle            = $this->languageTextManager->get_text('hermeshsi_title');
        $this->configurationStorage = MainFactory::create('HermesHSIConfigurationStorage');
        $this->logger               = new HermesHSILogger();
    }
}
