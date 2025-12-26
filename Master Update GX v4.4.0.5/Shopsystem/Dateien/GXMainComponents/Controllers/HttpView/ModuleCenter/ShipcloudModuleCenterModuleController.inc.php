<?php
/* --------------------------------------------------------------
	ShipcloudModuleCenterModuleController.inc.php 2020-09-23
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2020 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------
*/

/**
 * Controller for shipcloud configuration
 *
 * @extends    AbstractModuleCenterModuleController
 * @category   System
 * @package    Modules
 * @subpackage Controllers
 */
class ShipcloudModuleCenterModuleController extends AbstractModuleCenterModuleController
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var ShipcloudText
     */
    protected $shipcloudText;
    
    /**
     * @var ShipcloudConfigurationStorage
     */
    protected $shipcloudConfigurationStorage;
    
    
    protected function _init()
    {
        $this->shipcloudText                 = MainFactory::create('ShipcloudText');
        $this->shipcloudConfigurationStorage = MainFactory::create('ShipcloudConfigurationStorage');
        $this->pageTitle                     = $this->shipcloudText->get_text('configuration_heading');
        $this->db                            = StaticGXCoreLoader::getDatabaseQueryBuilder();
    }
    
    
    public function actionDefault()
    {
        $infoUrl      = $this->shipcloudConfigurationStorage->get('info-url');
        $unconfigured = ('' === $this->shipcloudConfigurationStorage->get('api-key/live')
                         && '' === $this->shipcloudConfigurationStorage->get('api-key/sandbox'));
        if ($unconfigured === true && !empty($infoUrl)) {
            $response = MainFactory::create('RedirectHttpControllerResponse',
                                            xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Info'));
        } else {
            $response = MainFactory::create('RedirectHttpControllerResponse',
                                            xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Configuration'));
        }
        
        return $response;
    }
    
    
    public function actionInfo()
    {
        $formdata = [
            'tab_urls' => [
                'info'              => xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Info'),
                'default'           => xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Configuration'),
                'package_templates' => xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/PackageTemplates'),
            ],
            'info_url' => $this->shipcloudConfigurationStorage->get('info-url'),
        ];
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/module_center/');
        $template      = 'module_center/shipcloud_info.html';
        $subNavigation = [
            [
                'text'   => $this->shipcloudText->get_text('config_tab_info'),
                'link'   => $formdata['tab_urls']['info'] ?? '',
                'active' => true,
            ],
            [
                'text'   => $this->shipcloudText->get_text('config_tab_default'),
                'link'   => $formdata['tab_urls']['default'] ?? '',
                'active' => false,
            ],
            [
                'text'   => $this->shipcloudText->get_text('config_tab_package_templates'),
                'link'   => $formdata['tab_urls']['package_templates'] ?? '',
                'active' => false,
            ],
        ];
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($this->pageTitle,
                                                                                  $template,
                                                                                  $formdata,
                                                                                  [],
                                                                                  $subNavigation);
    }
    
    
    public function actionConfiguration()
    {
        $userConfigurationService = StaticGXCoreLoader::getService('UserConfiguration');
        $userId                   = new IdType((int)$_SESSION['customer_id']);
        
        if ($this->shipcloudConfigurationStorage->get('mode') == 'sandbox') {
            $GLOBALS['messageStack']->add($this->shipcloudText->get_text('warning_sandbox'), 'warning');
        }
        
        $parcelServiceReader = MainFactory::create('ParcelServiceReader');
        $parcelServices      = $parcelServiceReader->getAllParcelServices();
        
        $formdata = [
            'page_token'                    => $_SESSION['coo_page_token']->generate_token(),
            'form_action'                   => xtc_href_link('admin.php',
                                                             'do=ShipcloudModuleCenterModule/SaveConfiguration'),
            'register_webhook_action'       => xtc_href_link('admin.php',
                                                             'do=ShipcloudModuleCenterModule/RegisterWebhook'),
            'delete_webhook_action'         => xtc_href_link('admin.php',
                                                             'do=ShipcloudModuleCenterModule/DeleteWebhook'),
            'orderstatus_autoconfig_action' => xtc_href_link('admin.php',
                                                             'do=ShipcloudModuleCenterModule/OrderstatusAutoconfiguration'),
            'user_id'                       => $userId,
            'collapsed'                     => [
                'credentials'   => $userConfigurationService->getUserConfiguration($userId,
                                                                                   'shipcloud_config_credentials_collapse'),
                'misc_settings' => $userConfigurationService->getUserConfiguration($userId,
                                                                                   'shipcloud_config_misc_settings_collapse'),
            ],
            'configuration'                 => [
                'mode'                                                    => $this->shipcloudConfigurationStorage->get('mode'),
                'api_key_sandbox'                                         => $this->shipcloudConfigurationStorage->get('api-key/sandbox'),
                'api_key_live'                                            => $this->shipcloudConfigurationStorage->get('api-key/live'),
                'debug_logging'                                           => $this->shipcloudConfigurationStorage->get('debug_logging'),
                'from_company'                                            => $this->shipcloudConfigurationStorage->get('from/company'),
                'from_first_name'                                         => $this->shipcloudConfigurationStorage->get('from/first_name'),
                'from_last_name'                                          => $this->shipcloudConfigurationStorage->get('from/last_name'),
                'from_street'                                             => $this->shipcloudConfigurationStorage->get('from/street'),
                'from_street_no'                                          => $this->shipcloudConfigurationStorage->get('from/street_no'),
                'from_city'                                               => $this->shipcloudConfigurationStorage->get('from/city'),
                'from_zip_code'                                           => $this->shipcloudConfigurationStorage->get('from/zip_code'),
                'from_country'                                            => $this->shipcloudConfigurationStorage->get('from/country'),
                'from_phone'                                              => $this->shipcloudConfigurationStorage->get('from/phone'),
                'cod_bank_account_holder'                                 => $this->shipcloudConfigurationStorage->get('cod-account/bank_account_holder'),
                'cod_bank_name'                                           => $this->shipcloudConfigurationStorage->get('cod-account/bank_name'),
                'cod_bank_account_number'                                 => $this->shipcloudConfigurationStorage->get('cod-account/bank_account_number'),
                'cod_bank_code'                                           => $this->shipcloudConfigurationStorage->get('cod-account/bank_code'),
                'packages'                                                => $this->shipcloudConfigurationStorage->get_all_tree('packages'),
                'parcel_service_id'                                       => $this->shipcloudConfigurationStorage->get('parcel_service_id'),
                'order_status_after_label'                                => $this->shipcloudConfigurationStorage->get('order_status_after_label'),
                'notify_customer'                                         => $this->shipcloudConfigurationStorage->get('notify_customer'),
                'api_timeout'                                             => $this->shipcloudConfigurationStorage->get('api-timeout'),
                'declared_value_min'                                      => $this->shipcloudConfigurationStorage->get('declared_value/minimum'),
                'declared_value_max'                                      => $this->shipcloudConfigurationStorage->get('declared_value/maximum'),
                'prefill_description'                                     => $this->shipcloudConfigurationStorage->get('prefill_description'),
                'prefill_email'                                           => $this->shipcloudConfigurationStorage->get('prefill_email'),
                'prefill_phone'                                           => $this->shipcloudConfigurationStorage->get('prefill_phone'),
                'webhook_id'                                              => $this->shipcloudConfigurationStorage->get('webhook/id'),
                'webhook_order_status_tracking_label_created'             => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_label_created'),
                'webhook_order_status_tracking_picked_up'                 => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_picked_up'),
                'webhook_order_status_tracking_transit'                   => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_transit'),
                'webhook_order_status_tracking_out_for_delivery'          => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_out_for_delivery'),
                'webhook_order_status_tracking_delivered'                 => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_delivered'),
                'webhook_order_status_tracking_awaits_pickup_by_receiver' => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_awaits_pickup_by_receiver'),
                'webhook_order_status_tracking_canceled'                  => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_canceled'),
                'webhook_order_status_tracking_delayed'                   => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_delayed'),
                'webhook_order_status_tracking_exception'                 => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_exception'),
                'webhook_order_status_tracking_not_delivered'             => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_not_delivered'),
                'webhook_order_status_tracking_destroyed'                 => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_destroyed'),
                'webhook_order_status_tracking_notification'              => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_notification'),
                'webhook_order_status_tracking_unknown'                   => $this->shipcloudConfigurationStorage->get('webhook/order_status_tracking_unknown'),
                'cargo_intl_advance_notice'                               => $this->shipcloudConfigurationStorage->get('additional_services/cargo_intl_advance_notice'),
                'dhl_advance_notice'                                      => $this->shipcloudConfigurationStorage->get('additional_services/dhl_advance_notice'),
                'dpd-predict'                                             => $this->shipcloudConfigurationStorage->get('additional_services/dpd-predict'),
                'gls-flexdelivery'                                        => $this->shipcloudConfigurationStorage->get('additional_services/gls-flexdelivery'),
                'hermes_advance_notice'                                   => $this->shipcloudConfigurationStorage->get('additional_services/hermes_advance_notice'),
                'dhl_gogreen'                                             => $this->shipcloudConfigurationStorage->get('additional_services/dhl_gogreen'),
                'dhl_premium_international'                               => $this->shipcloudConfigurationStorage->get('additional_services/dhl_premium_international'),
            ],
            'boarding_url'                  => $this->shipcloudConfigurationStorage->get('boarding_url'),
            'parcel_services'               => $parcelServices,
            'preselected_carriers'          => $this->shipcloudConfigurationStorage->get_all_tree('preselected_carriers'),
            'checked_carriers'              => $this->shipcloudConfigurationStorage->get_all_tree('checked_carriers'),
            'orders_statuses'               => $this->getOrdersStatuses(),
            'tab_urls'                      => [
                'default'           => xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Configuration'),
                'package_templates' => xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/PackageTemplates'),
            ],
        ];
        
        $infoUrl = $this->shipcloudConfigurationStorage->get('info-url');
        if (!empty($infoUrl)) {
            $formdata['tab_urls']['info'] = xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Info');
        }
        
        $carriersCache        = MainFactory::create('ShipcloudCarriersCache');
        $formdata['carriers'] = $carriersCache->getCarriers();
        
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/module_center/');
        $template      = 'module_center/shipcloud_configuration.html';
        $subNavigation = [
            [
                'text'   => $this->shipcloudText->get_text('config_tab_info'),
                'link'   => $formdata['tab_urls']['info'] ?? '',
                'active' => false,
            ],
            [
                'text'   => $this->shipcloudText->get_text('config_tab_default'),
                'link'   => $formdata['tab_urls']['default'] ?? '',
                'active' => true,
            ],
            [
                'text'   => $this->shipcloudText->get_text('config_tab_package_templates'),
                'link'   => $formdata['tab_urls']['package_templates'] ?? '',
                'active' => false,
            ],
        ];
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($this->shipcloudText->get_text('configuration_heading'),
                                                                                  $template,
                                                                                  $formdata,
                                                                                  [],
                                                                                  $subNavigation);
    }
    
    
    /**
     * Displays package templates configuration
     * @return AdminLayoutHttpControllerResponse
     */
    public function actionPackageTemplates()
    {
        $packages = $this->shipcloudConfigurationStorage->get_all_tree('packages');
        if (!empty($packages)) {
            foreach ($packages['packages'] as $templateId => $packageTemplate) {
                $packages['packages'][$templateId]['type_name'] = $this->shipcloudText->get_text('package_type_'
                                                                                                 . $packageTemplate['type']);
            }
        }
    
        $formdata = [
            'configuration'                       => [
                'packages'        => $packages,
                'default_package' => $this->shipcloudConfigurationStorage->get('default_package'),
            ],
            'tab_urls'                            => [
                'default'           => xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Configuration'),
                'package_templates' => xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/PackageTemplates'),
            ],
            'save_package_templates_action'       => xtc_href_link('admin.php',
                                                                   'do=ShipcloudModuleCenterModule/SavePackageTemplates'),
            'set_default_package_template_action' => xtc_href_link('admin.php',
                                                                   'do=ShipcloudModuleCenterModule/SetDefaultPackageTemplate'),
            'delete_package_template_action'      => xtc_href_link('admin.php',
                                                                   'do=ShipcloudModuleCenterModule/DeletePackageTemplate'),
        ];
    
        $infoUrl = $this->shipcloudConfigurationStorage->get('info-url');
        if (!empty($infoUrl)) {
            $formdata['tab_urls']['info'] = xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Info');
        }
        
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/module_center/');
        $template      = 'module_center/shipcloud_configuration_package_templates.html';
        $subNavigation = [
            [
                'text'   => $this->shipcloudText->get_text('config_tab_info'),
                'link'   => $formdata['tab_urls']['info'] ?? '',
                'active' => false,
            ],
            [
                'text'   => $this->shipcloudText->get_text('config_tab_default'),
                'link'   => $formdata['tab_urls']['default'] ?? '',
                'active' => false,
            ],
            [
                'text'   => $this->shipcloudText->get_text('config_tab_package_templates'),
                'link'   => $formdata['tab_urls']['package_templates'] ?? '',
                'active' => true,
            ],
        ];
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($this->shipcloudText->get_text('configuration_heading'),
                                                                                  $template,
                                                                                  $formdata,
                                                                                  [],
                                                                                  $subNavigation);
    }
    
    
    /**
     * Sets default package template
     *
     * @return mixed|RedirectHttpControllerResponse
     * @throws Exception
     */
    public function actionSetDefaultPackageTemplate()
    {
        $templateId = (int)$this->_getPostData('templateId');
        if (empty($templateId)) {
            throw new \RuntimeException('no template id');
        }
        
        $this->shipcloudConfigurationStorage->set('default_package', $templateId);
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php',
                                                 'do=ShipcloudModuleCenterModule/PackageTemplates'));
    }
    
    /**
     * Returns HTML for package template ConfigurationBox
     * @return string
     */
    protected function _getConfigurationBox()
    {
        $heading = '';
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/module_center/');
        $contents       = $this->_render('shipcloud_package_template_configuration_box.html', []);
        $formAttributes = [];
        $buttons        = '<div class="button-set detail-buttons"><button class="btn delete-package-template" onClick="this.blur(); return false;">'
                          . BUTTON_DELETE . '</button>'
                          . '<button class="btn btn-primary edit-package-template" onClick="this.blur(); return false;">'
                          . BUTTON_EDIT . '</button></div>'
                          . '<div class="button-set form-data-buttons hidden"><button class="btn btn-cancel cancel-package-template" onClick="this.blur(); return false;">'
                          . BUTTON_CANCEL . '</button>'
                          . '<button form="configuration-box-form" class="btn btn-primary save-package-template" type="submit">'
                          . BUTTON_SAVE . '</button></div>'
                          . '<div class="button-set create-form-data-buttons hidden"><button form="configuration-box-form" class="btn btn-primary save-package-template" type="submit">'
                          . BUTTON_SAVE . '</button></div>'
                          . '<div class="button-set confirm-delete-buttons hidden"><button class="btn btn-primary confirm-delete-package-template">'
                          . BUTTON_DELETE . '</button>'
                          . '<button class="btn btn-cancel cancel-package-template" onClick="this.blur(); return false;">'
                          . BUTTON_CANCEL . '</button></div>';
        $formIsEditable = '';
        $formAction     = xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/SavePackageTemplates');
        
        $configurationBoxContentView = MainFactory::create_object('ConfigurationBoxContentView');
        $configurationBoxContentView->set_content_data('heading', $heading);
        $configurationBoxContentView->set_content_data('form', $contents);
        $configurationBoxContentView->setFormAttributes($formAttributes);
        $configurationBoxContentView->set_content_data('buttons', $buttons);
        $configurationBoxContentView->setFormEditable($formIsEditable);
        $configurationBoxContentView->setFormAction($formAction);
        $configurationBox = $configurationBoxContentView->get_html();
        
        return $configurationBox;
    }
    
    
    /**
     * Returns configuration of a package template as identified by the templateId GET parameter
     * @return JsonHttpControllerResponse
     */
    public function actionGetPackageTemplate()
    {
        $templateId                    = (int)$this->_getQueryParameter('templateId');
        $packageTemplate               = $this->_getPackageTemplateData($templateId);
        $packageTemplate['is_default'] = $templateId == $this->shipcloudConfigurationStorage->get('default_package');
        
        return MainFactory::create('JsonHttpControllerResponse', $packageTemplate);
    }
    
    
    /**
     * Deletes a package template as identified by the templateId GET parameter and redirects back to package template
     * configuration
     * @return RedirectHttpControllerResponse
     */
    public function actionDeletePackageTemplate()
    {
        $templateId = (int)$this->_getQueryParameter('templateId');
        if (empty($templateId)) {
            $templateId = (int)$this->_getPostData('templateId');
            if (empty($templateId)) {
                throw new \RuntimeException('no template id');
            }
        }
        
        $this->shipcloudConfigurationStorage->delete_all('packages/' . $templateId);
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php',
                                                 'do=ShipcloudModuleCenterModule/PackageTemplates'));
    }
    
    
    /**
     * Returns template configuration
     *
     * @param int
     *
     * @return array
     */
    protected function _getPackageTemplateData($templateId)
    {
        $packageTemplate                                           = $this->shipcloudConfigurationStorage->get_all('packages/'
                                                                                                                   . $templateId);
        $packageTemplate['packages/' . $templateId . '/type']      = $packageTemplate['packages/' . $templateId
                                                                                      . '/type'] ? : 'parcel';
        $packageTemplate['packages/' . $templateId . '/type_name'] = $this->shipcloudText->get_text('package_type_'
                                                                                                    . $packageTemplate['packages/'
                                                                                                                       . $templateId
                                                                                                                       . '/type']);
        
        return $packageTemplate;
    }
    
    
    /**
     * Retrieves a array of order statuses (ids and names as per current session language)
     * @return array
     */
    protected function getOrdersStatuses()
    {
        $this->db->where(['language_id' => $_SESSION['languages_id']]);
        $this->db->order_by('orders_status_name ASC');
        $orders_statuses_query = $this->db->get('orders_status');
        $orders_statuses       = $orders_statuses_query->result();
        
        return $orders_statuses;
    }
    
    
    /**
     * saves package template configuration
     * @return RedirectHttpControllerResponse
     * @throws Exception
     */
    public function actionSavePackageTemplates()
    {
        $package = $this->_getPostData('package');
        if (empty($package['id'])) {
            $package['id']    = $this->shipcloudConfigurationStorage->getMaximumPackageTemplateId() + 1;
            $packageTemplates = $this->shipcloudConfigurationStorage->get_all_tree('packages');
            if (empty($packageTemplates)) {
                $this->shipcloudConfigurationStorage->set('default_package', (int)$package['id']);
            }
        }
        $this->shipcloudConfigurationStorage->set('packages/' . (int)$package['id'] . '/name', $package['name']);
        $this->shipcloudConfigurationStorage->set('packages/' . (int)$package['id'] . '/weight', $package['weight']);
        $this->shipcloudConfigurationStorage->set('packages/' . (int)$package['id'] . '/length', $package['length']);
        $this->shipcloudConfigurationStorage->set('packages/' . (int)$package['id'] . '/width', $package['width']);
        $this->shipcloudConfigurationStorage->set('packages/' . (int)$package['id'] . '/height', $package['height']);
        $this->shipcloudConfigurationStorage->set('packages/' . (int)$package['id'] . '/type', $package['type']);
        
        $defaultPackageTemplate = $this->_getPostData('default_template');
        if ($defaultPackageTemplate !== null) {
            $this->shipcloudConfigurationStorage->set('default_package', (int)$defaultPackageTemplate);
        }
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php',
                                                 'do=ShipcloudModuleCenterModule/PackageTemplates'));
    }
    
    
    /**
     * saves configuration values
     * @return RedirectHttpControllerResponse
     * @throws Exception
     */
    public function actionSaveConfiguration()
    {
        $unconfigured = ('' === $this->shipcloudConfigurationStorage->get('api-key/live')
                         && '' === $this->shipcloudConfigurationStorage->get('api-key/sandbox'));
        
        $newConfiguration = $this->_getPostData('configuration');
        
        foreach ($newConfiguration as $key => $value) {
            $this->shipcloudConfigurationStorage->set($key, $value);
        }
        $preselectionCarriers = $this->_getPostData('preselected_carriers') ? : [];
        $checkedCarriers      = $this->_getPostData('checked_carriers') ? : [];
        $carriers             = $this->shipcloudConfigurationStorage->getCarriers(false);
        if (!empty($preselectionCarriers)) {
            foreach ($carriers as $carrier) {
                $carrier_selected = in_array($carrier, $preselectionCarriers);
                $this->shipcloudConfigurationStorage->set('preselected_carriers/' . $carrier,
                                                          $carrier_selected ? '1' : '0');
                $carrier_checked = in_array($carrier, $checkedCarriers);
                $this->shipcloudConfigurationStorage->set('checked_carriers/' . $carrier, $carrier_checked ? '1' : '0');
            }
        }
        $GLOBALS['messageStack']->add_session($this->shipcloudText->get_text('configuration_saved'), 'info');
        if ($unconfigured) {
            $GLOBALS['messageStack']->add_session($this->shipcloudText->get_text('please_check_carriers'), 'info');
        }
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Configuration'));
    }
    
    
    /**
     * Creates default set of order statuses for webhook notifications
     *
     * @return bool|RedirectHttpControllerResponse
     */
    public function actionOrderstatusAutoconfiguration()
    {
        $this->makeDefaultOrderStatusConfiguration();
        $GLOBALS['messageStack']->add_session($this->shipcloudText->get_text('configured_order_statuses'), 'info');
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Configuration'));
    }
    
    
    /**
     * Registers a webhook with Shipcloud
     *
     * @return bool|RedirectHttpControllerResponse
     * @throws Exception
     */
    public function actionRegisterWebhook()
    {
        $webhookEndpoint = xtc_catalog_href_link('shop.php',
                                                 'do=ShipcloudWebhook',
                                                 'SSL',
                                                 false,
                                                 false,
                                                 false,
                                                 false,
                                                 true);
        $restService     = MainFactory::create('ShipcloudRestService');
        $restRequest     = MainFactory::create('ShipcloudRestRequest',
                                               'POST',
                                               '/v1/webhooks',
                                               [
                                                   'url'         => $webhookEndpoint,
                                                   'event_types' => ['*']
                                               ]);
        $response        = $restService->performRequest($restRequest);
        if ($response->getResponseCode() === 200) {
            $webhookId = $response->getResponseObject()->id;
            $this->shipcloudConfigurationStorage->set('webhook/id', $webhookId);
            $GLOBALS['messageStack']->add_session($this->shipcloudText->get_text('webhook_registered') . ' '
                                                  . $webhookId,
                                                  'info');
        } else {
            if (isset($response->getResponseObject()->errors)) {
                $errors = '<br>' . implode('<br>', $response->getResponseObject()->errors);
            } else {
                $errors = '';
            }
            
            $GLOBALS['messageStack']->add_session($this->shipcloudText->get_text('could_not_register_webhook')
                                                  . $errors,
                                                  'error');
        }
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Configuration'));
    }
    
    
    /**
     * Deletes a webhook
     *
     * @return bool|RedirectHttpControllerResponse
     * @throws Exception
     */
    public function actionDeleteWebhook()
    {
        $restService = MainFactory::create('ShipcloudRestService');
        $restRequest = MainFactory::create('ShipcloudRestRequest',
                                           'DELETE',
                                           '/v1/webhooks/' . $this->shipcloudConfigurationStorage->get('webhook/id'));
        $response    = $restService->performRequest($restRequest);
        if ($response->getResponseCode() === 204) {
            $this->shipcloudConfigurationStorage->set('webhook/id', '');
            $GLOBALS['messageStack']->add_session($this->shipcloudText->get_text('webhook_deleted'), 'info');
        } else {
            $GLOBALS['messageStack']->add_session($this->shipcloudText->get_text('could_not_delete_webhook'), 'error');
        }
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule/Configuration'));
    }
    
    
    protected function makeDefaultOrderStatusConfiguration()
    {
        $defaultOrderStatuses = [
            'awaits_pickup_by_receiver' => [
                'names' => [
                    'de' => 'Bereit zur Abholung',
                    'en' => 'Awaits Pickup',
                ],
                'color' => '00FFFF',
            ],
            'canceled'                  => [
                'names' => [
                    'de' => 'Versand storniert',
                    'en' => 'Shipment cancelled',
                ],
                'color' => '0080FF',
            ],
            'delayed'                   => [
                'names' => [
                    'de' => 'Zustellung verzögert',
                    'en' => 'Delivery delayed',
                ],
                'color' => 'FF00FF',
            ],
            'delivered'                 => [
                'names' => [
                    'de' => 'Zugestellt',
                    'en' => 'Delivered',
                ],
                'color' => 'FF0080',
            ],
            'destroyed'                 => [
                'names' => [
                    'de' => 'Zerstört',
                    'en' => 'Destroyed',
                ],
                'color' => 'FFFF00',
            ],
            'exception'                 => [
                'names' => [
                    'de' => 'Problem bei Zustellung',
                    'en' => 'Problem with shipment',
                ],
                'color' => '80FF00',
            ],
            'label_created'             => [
                'names' => [
                    'de' => 'Versandlabel erzeugt',
                    'en' => 'Label created',
                ],
                'color' => '009999',
            ],
            'not_delivered'             => [
                'names' => [
                    'de' => 'Nicht zugestellt',
                    'en' => 'Not delivered',
                ],
                'color' => '004D99',
            ],
            'notification'              => [
                'names' => [
                    'de' => 'Zustellerhinweis',
                    'en' => 'Carrier internal notification',
                ],
                'color' => '990099',
            ],
            'out_for_delivery'          => [
                'names' => [
                    'de' => 'In Zustellung',
                    'en' => 'Out for delivery',
                ],
                'color' => '99004D',
            ],
            'picked_up'                 => [
                'names' => [
                    'de' => 'An Dienstleister übergeben',
                    'en' => 'Picked up by carrier',
                ],
                'color' => '999900',
            ],
            'transit'                   => [
                'names' => [
                    'de' => 'Wird transportiert',
                    'en' => 'Shipment in transit',
                ],
                'color' => '4D9900',
            ],
            'unknown'                   => [
                'names' => [
                    'de' => 'Versandstatus unbekannt',
                    'en' => 'Status unknown',
                ],
                'color' => '73E2E2',
            ],
        ];
        
        /** @var OrderStatusService $orderStatusService */
        $orderStatusService = StaticGXCoreLoader::getService('OrderStatus');
        $orderStatuses      = $orderStatusService->findAll();
        foreach ($defaultOrderStatuses as $label => $orderStatusParams) {
            /** @var OrderStatus $existingOrderStatus */
            foreach ($orderStatuses as $existingOrderStatus) {
                foreach (['de', 'en'] as $languageCode) {
                    if ($existingOrderStatus->getName(new LanguageCode(new StringType($languageCode)))
                        === $orderStatusParams['names'][$languageCode]) {
                        
                        continue;
                    }
                }
            }
            /** @var OrderStatus $orderStatus */
            $orderStatus = MainFactory::create('OrderStatus');
            foreach ($orderStatusParams['names'] as $languageCode => $name) {
                $orderStatus->setName(new LanguageCode(new StringType($languageCode)), new StringType($name));
            }
            $orderStatus->setColor(new StringType($orderStatusParams['color']));
            $newOrderStatusId = $orderStatusService->create($orderStatus);
            $this->shipcloudConfigurationStorage->set('webhook/order_status_tracking_' . $label, $newOrderStatusId);
        }
    }
    
}
