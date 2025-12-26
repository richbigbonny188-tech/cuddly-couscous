<?php
/* --------------------------------------------------------------
   Application.inc.php 2021-04-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------

   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_top.php,v 1.273 2003/05/19); www.oscommerce.com
   (c) 2003	 nextcommerce (application_top.php,v 1.54 2003/08/25); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: application_top.php 1323 2005-10-27 17:58:08Z mz $)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Add A Quickie v1.0 Autor  Harald Ponce de Leon

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

namespace Gambio\GX;

use breadcrumb;
use Countries;
use CountrySessionWriter;
use Debugger;
use Doctrine\DBAL\Connection;
use ErrorHandler;
use ErrorPageGenerator;
use Exception;
use ExistingDirectory;
use FrontendFilenamesProvider;
use Gambio\Admin\Modules\RedirectRules\Factories\RedirectServiceFactory;
use Gambio\Core\Configuration\ConfigurationService;
use Gambio\Shop\UserNavigationHistory\Collections\UserNavigationHistory;
use Gambio\Shop\UserNavigationHistory\UserNavigationHistoryService;
use InputFilter;
use InvalidArgumentException;
use language;
use LanguageCode;
use LegacyDependencyContainer;
use LogControl;
use main;
use MainAutoloader;
use MainFactory;
use messageStack;
use product;
use PublishedThemeValidationServiceInterface;
use RequiredDirectory;
use RuntimeException;
use SecurityCheck;
use shoppingCart;
use StaticGXCoreLoader;
use StopWatch;
use StringType;
use StyleEditServiceFactory;
use ThemeDirectoryRoot;
use ThemeId;
use ThemeSettings;
use wishList;
use xtcPrice;
use Gambio\Core\Configuration\ConfigurationFinder;
use function GuzzleHttp\Psr7\parse_query;

/**
 * Class Application
 *
 * @package Gambio
 */
class Application
{
    /**
     * @var bool
     */
    protected $pageNotFound = false;
    
    /**
     * @var \TimezoneSetter
     */
    protected $timezoneSetter;
    
    /**
     * @var \LanguageProvider
     */
    protected $languageProvider;
    
    /**
     * @var string
     */
    protected $defaultLanguageCode;
    
    /**
     * @var string
     */
    protected $urlLangParam;
    /**
     * @var string|string[]|null
     */
    /**
     * @var string
     */
    protected $cPath;
    
    
    public function run()
    {
        $this->registerComposerAutoloader();
        $this->runUpdateNeededCheck();
        $this->runGProtector();
        
        self::loadConfig();
        
        $this->checkRequestUriForCorrectProtocolAndDomain();
        $this->setUpEnvironment();
        $this->runPrimalExtenders();
        $this->setUpFrontend();
        $this->checkRequestUriForCorrectLanguageCode();
        $this->handleChangeOfLanguageOrCurrencyOrCountry();
        $this->initLanguage();
        $this->updateSessionData();
        $this->initializeGlobalObjects();
        $this->runCartActions();
        $this->handlePageSpecificRequests();
        $this->runExtenders();
        $this->executeCronjobLikeScripts();
        $this->sendHeader();
    }
    
    
    public static function loadConfig()
    {
        # Set the local configuration parameters - mainly for developers - if exists else the main configure
        if (file_exists('includes/local/configure.php')) {
            require_once __DIR__ . '/../includes/local/configure.php';
        } else {
            require_once __DIR__ . '/../includes/configure.php';
        }
        
        # set the type of request (secure or not)
        $GLOBALS['request_type'] = (getenv('HTTPS') === '1' || getenv('HTTPS') === 'on') ? 'SSL' : 'NONSSL';
        
        if ($GLOBALS['request_type'] === 'SSL' || !empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            define('GM_HTTP_SERVER', HTTPS_SERVER);
        } else {
            define('GM_HTTP_SERVER', HTTP_SERVER);
        }
    }


    public static function send404HttpResponse(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('HTTP/1.0 404 Not Found');

        $configurationStorage = MainFactory::create('ConfigurationStorage', 'error_pages');
        if ($configurationStorage->get('customPageNotFound') === 'true') {
            try {
                $languageCode = new LanguageCode(new StringType($_SESSION['language_code'] ??
                                                                substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)));
            } catch (Exception $exception) {
                $languageCode = new LanguageCode(new StringType('de'));
            }

            $customErrorPageFile = ErrorPageGenerator::fileNotFoundErrorPageCacheFile($languageCode, false);
            if (!file_exists($customErrorPageFile)) {
                $customHtml = json_decode($configurationStorage->get('notFoundHtml'), true);
                ErrorPageGenerator::createPageCache($customHtml[$languageCode->asString()], $customErrorPageFile);
            }

            include $customErrorPageFile;
            xtc_db_close();
            exit;
        }

        if (file_exists(DIR_FS_CATALOG . 'error404.html')) {
            include DIR_FS_CATALOG . 'error404.html';
            xtc_db_close();
            exit;
        }

        if (file_exists(DIR_FS_CATALOG . 'error404.php')) {
            include DIR_FS_CATALOG . 'error404.php';
            xtc_db_close();
            exit;
        }
    }
    
    
    /**
     * Checks for a flag file in the cache directory and shows a "Gambio Updater need to be executed" page, if it
     * exists.
     */
    protected function runUpdateNeededCheck()
    {
        $exceptions = [
            '/login_admin.php'
        ];
        
        $isException = in_array('/' . basename($_SERVER['SCRIPT_NAME']), $exceptions);
        
        if (!file_exists(__DIR__ . '/../cache/update_needed.flag') || $isException) {
            return;
        }
        
        require_once __DIR__ . '/Extensions/Helpers/ShopOfflinePageHelper.inc.php';
        \ShopOfflinePageHelper::showShopOfflinePage();
    }
    
    
    protected function runGProtector()
    {
        require_once __DIR__ . '/../system/core/logging/LogControl.inc.php';
        require __DIR__ . '/../GProtector/start.inc.php';
    }
    
    
    protected function setUpEnvironment()
    {
        $this->setMissingServerVariables();
        $this->defineInitialConstants();
        $this->setMemoryLimit();
        $this->includeWrapperFunctions();
        $this->initGXEngine();
        $this->registerErrorHandler();
        $this->setTimezone();
        $this->startStopWatch();
        $this->registerAutoloader();
        $this->initializeGlobalDebuggerObject();
        $this->initializeGlobalPhpSelfVariable();
        $this->includeFunctions();
        $this->includeClasses();
        $this->startSession();
        $this->connectToDatabase();
        $this->defineConstantsFromDbConfigurationTable();
        $this->verifySession();
        $this->updateTimezone(defined('DATE_TIMEZONE') ? DATE_TIMEZONE : 'Europe/Berlin');
        $this->sanitizeRequestData();
        $this->startTracking();
        $this->initializeGlobalSeoBoostObject();
        $this->setXSRFPageToken();
        $this->initializeHistory();
    }
    
    
    protected function setUpFrontend()
    {
        $this->setCurrentTemplate();
        
        clearstatcache();
        
        $this->buildTemporaryTheme();
        $this->includeStyleEdit();
        $this->setUpMenuBoxesConfiguration();
        
        $this->startGzipOutputBuffer();
    }
    
    
    protected function handlePageSpecificRequests()
    {
        $this->handleProductRequest($this->getLanguageId());
        $this->handleCategoryRequest($this->getLanguageId());
        $this->handleContentRequest($this->getLanguageId());
        $this->handleManufacturerRequest();
        
        $this->resetSessionRedirectionInfo();
        $this->buildBreadcrumb($this->getLanguageId());
        $this->updateBreadcrumbSession();
        $this->handle404error();
    }
    
    
    protected function initializeGlobalObjects()
    {
        $this->initializeGlobalMainObject();
        $this->initializeGlobalXtcPriceObject();
        $this->setSessionObjects();
        $this->initializeGlobalMessageStackObject();
    }
    
    
    protected function updateSessionData()
    {
        $this->setSessionCurrency();
        $this->setSessionCountry();
        $this->setSessionCustomerStatus();
    }
    
    
    protected function setMissingServerVariables()
    {
        if (empty($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);
            $_SERVER['PATH_INFO'] = ($part = strtok($_SERVER['PATH_INFO'], '?')) === false ? '' : $part;
        }
    }
    
    
    protected function defineInitialConstants()
    {
        if (!defined('APPLICATION_RUN_MODE')) {
            define('APPLICATION_RUN_MODE', 'frontend');
        }
        
        define('PROJECT_VERSION', 'xt:Commerce v3.0.4 SP2.1');
        define('FIRST_GX2_TEMPLATE_VERSION', 2.0);
        define('PAGE_PARSE_START_TIME', microtime(true));
        define('_GM_VALID_CALL', 1);
        
        // include the list of project filenames
        require_once DIR_WS_INCLUDES . 'filenames.php';
        
        // include the list of project database tables
        require_once DIR_WS_INCLUDES . 'database_tables.php';
        
        // SQL caching dir
        define('SQL_CACHEDIR', DIR_FS_CATALOG . 'cache/');
        
        // set which precautions should be checked
        define('WARN_INSTALL_EXISTENCE', 'true');
        define('WARN_CONFIG_WRITEABLE', 'false');
        define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
        define('WARN_SESSION_AUTO_START', 'true');
        define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');
    }
    
    
    protected function setMemoryLimit($limit = 128)
    {
        require_once DIR_FS_INC . 'set_memory_limit.inc.php';
        set_memory_limit($limit);
    }
    
    
    protected function includeWrapperFunctions()
    {
        require_once DIR_FS_INC . 'htmlentities_wrapper.inc.php';
        require_once DIR_FS_INC . 'htmlspecialchars_wrapper.inc.php';
        require_once DIR_FS_INC . 'html_entity_decode_wrapper.inc.php';
        require_once DIR_FS_INC . 'parse_str_wrapper.inc.php';
        require_once DIR_FS_INC . 'strlen_wrapper.inc.php';
        require_once DIR_FS_INC . 'substr_wrapper.inc.php';
        require_once DIR_FS_INC . 'strpos_wrapper.inc.php';
        require_once DIR_FS_INC . 'strrpos_wrapper.inc.php';
        require_once DIR_FS_INC . 'strtolower_wrapper.inc.php';
        require_once DIR_FS_INC . 'strtoupper_wrapper.inc.php';
        require_once DIR_FS_INC . 'substr_count_wrapper.inc.php';
        require_once DIR_FS_INC . 'utf8_encode_wrapper.inc.php';
    }
    
    
    protected function initGXEngine()
    {
        require_once DIR_FS_CATALOG . 'system/core/logging/LogEvent.inc.php';
        require_once DIR_FS_CATALOG . 'gm/classes/ErrorHandler.php';
        require_once DIR_FS_CATALOG . 'gm/classes/FileLog.php';
        require_once DIR_FS_CATALOG . 'gm/inc/check_data_type.inc.php';
        require_once DIR_FS_CATALOG . 'gm/inc/gm_get_env_info.inc.php';
        require_once DIR_FS_CATALOG . 'system/gngp_layer_init.inc.php';
    }
    
    
    protected function registerErrorHandler()
    {
        set_error_handler([new ErrorHandler(), 'HandleError']);
        require_once DIR_FS_CATALOG . 'system/core/logging/SentryErrorHandler.inc.php';
        register_shutdown_function([new ErrorHandler(), 'shutdown']);
    }
    
    
    /**
     * @return mixed
     */
    protected function setTimezone()
    {
        $this->getTimezoneSetter()->set_date_default_timezone();
    }
    
    
    protected function getTimezoneSetter()
    {
        if ($this->timezoneSetter === null) {
            $this->timezoneSetter = MainFactory::create_object('TimezoneSetter');
        }
        
        return $this->timezoneSetter;
    }
    
    
    protected function registerAutoloader()
    {
        // custom class autoloader
        spl_autoload_register([new MainAutoloader('frontend'), 'load']);
    }
    
    
    protected function registerComposerAutoloader(): void
    {
        // Composer class autoloader.
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            throw new RuntimeException('Vendor directory is missing from the filesystem. Please install the PHP dependencies by '
                                       . 'executing the "composer install && gulp general:composer" command.');
        }
        
        if (!class_exists('\Composer\Autoload\ClassLoader', false)) {
            require_once __DIR__ . '/../vendor/autoload.php';
        }
    }
    
    
    protected function checkRequestUriForCorrectProtocolAndDomain()
    {
        // redirect to main domain to avoid duplicate content if request url contains unknown domain (e.i. non-www domain -> www domain)
        if (strpos(GM_HTTP_SERVER, '//' . $_SERVER['HTTP_HOST']) === false
            && strstr(GM_HTTP_SERVER, '//') === strstr(HTTP_SERVER, '//') // exclude SSL-proxys
        ) {
            $this->redirect(GM_HTTP_SERVER . $_SERVER['REQUEST_URI']);
        }
        
        // redirect to https page if SSL is activated for every page
        if (HTTPS_SERVER === HTTP_SERVER && ENABLE_SSL && strpos(HTTPS_SERVER, 'https') === 0
            && (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) !== 'on')
            && (!isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'https')) {
            $this->redirect(HTTPS_SERVER . $_SERVER['REQUEST_URI']);
        }
    }
    
    
    protected function initializeGlobalDebuggerObject()
    {
        $GLOBALS['coo_debugger'] = new Debugger();
    }
    
    
    protected function includeFunctions()
    {
        // Database
        require_once DIR_FS_INC . 'xtc_db_connect.inc.php';
        require_once DIR_FS_INC . 'xtc_db_close.inc.php';
        require_once DIR_FS_INC . 'xtc_db_perform.inc.php';
        require_once DIR_FS_INC . 'xtc_db_query.inc.php';
        require_once DIR_FS_INC . 'xtc_db_fetch_array.inc.php';
        require_once DIR_FS_INC . 'xtc_db_num_rows.inc.php';
        require_once DIR_FS_INC . 'xtc_db_insert_id.inc.php';
        require_once DIR_FS_INC . 'xtc_db_free_result.inc.php';
        require_once DIR_FS_INC . 'xtc_db_input.inc.php';
        require_once DIR_FS_INC . 'xtc_db_prepare_input.inc.php';
        
        // include needed functions
        require_once DIR_FS_INC . 'get_usermod.inc.php';
        require_once DIR_FS_INC . 'xtc_get_prid.inc.php';
        require_once DIR_FS_INC . 'xtc_draw_form.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_input_field.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_image_submit.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_get_prid.inc.php';
        
        // html basics
        require_once DIR_FS_INC . 'xtc_href_link.inc.php';
        require_once DIR_FS_INC . 'xtc_draw_separator.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_php_mail.inc.php';
        
        require_once DIR_FS_INC . 'xtc_product_link.inc.php';
        require_once DIR_FS_INC . 'xtc_category_link.inc.php';
        require_once DIR_FS_INC . 'xtc_manufacturer_link.inc.php';
        
        // html functions
        require_once DIR_FS_INC . 'xtc_draw_checkbox_field.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_form.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_hidden_field.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_input_field.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_password_field.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_pull_down_menu.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_radio_field.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_selection_field.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_separator.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_draw_textarea_field.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_image_button.inc.php'; // TODO delete
        
        require_once DIR_FS_INC . 'xtc_not_null.inc.php';
        require_once DIR_FS_INC . 'xtc_parse_category_path.inc.php';
        require_once DIR_FS_INC . 'xtc_get_product_path.inc.php';
        require_once DIR_FS_INC . 'xtc_get_category_path.inc.php';
        require_once DIR_FS_INC . 'xtc_get_parent_categories.inc.php';
        require_once DIR_FS_INC . 'xtc_redirect.inc.php';
        require_once DIR_FS_INC . 'xtc_get_uprid.inc.php';
        require_once DIR_FS_INC . 'xtc_get_all_get_params.inc.php';
        require_once DIR_FS_INC . 'xtc_has_product_attributes.inc.php';
        require_once DIR_FS_INC . 'xtc_image.inc.php'; // TODO delete
        require_once DIR_FS_INC . 'xtc_check_stock_attributes.inc.php';
        require_once DIR_FS_INC . 'xtc_currency_exists.inc.php';
        require_once DIR_FS_INC . 'xtc_remove_non_numeric.inc.php';
        require_once DIR_FS_INC . 'xtc_get_ip_address.inc.php';
        require_once DIR_FS_INC . 'xtc_count_cart.inc.php';
        require_once DIR_FS_INC . 'xtc_get_qty.inc.php';
        require_once DIR_FS_INC . 'xtc_get_tax_rate.inc.php';
        require_once DIR_FS_INC . 'xtc_add_tax.inc.php';
        require_once DIR_FS_INC . 'xtc_cleanName.inc.php';
        require_once DIR_FS_INC . 'xtc_calculate_tax.inc.php';
        require_once DIR_FS_INC . 'xtc_input_validation.inc.php';
        require_once DIR_FS_INC . 'fetch_email_template.inc.php';
        require_once DIR_FS_INC . 'xtc_date_raw.inc.php';
        require_once DIR_FS_INC . 'xtc_date_short.inc.php';
        
        require_once DIR_FS_CATALOG . 'gm/inc/gm_prepare_string.inc.php';
        require_once DIR_FS_CATALOG . 'gm/inc/gm_set_conf.inc.php';
        require_once DIR_FS_CATALOG . 'gm/inc/gm_get_conf.inc.php';
        require_once DIR_FS_CATALOG . 'gm/inc/gm_set_content.inc.php';
        require_once DIR_FS_CATALOG . 'gm/inc/gm_get_content.inc.php';
        require_once DIR_FS_CATALOG . 'gm/inc/gm_convert_qty.inc.php';
        require_once DIR_FS_CATALOG . 'gm/inc/gm_get_privacy_link.inc.php';
        
        require_once DIR_FS_INC . 'cookie_consent_panel_is_installed.php';
        require_once DIR_FS_INC . 'cookie_purpose_is_active.php';
        require_once DIR_FS_INC . 'cookie_purpose_is_enabled.php';
    }
    
    
    protected function includeClasses()
    {
        require_once DIR_FS_CATALOG . 'gm/modules/gm_gprint_application_top.php';
        require_once DIR_FS_CATALOG . 'gm/modules/gm_gprint_tables.php';
        require_once DIR_FS_CATALOG . 'gm/classes/GMCounter.php';
    }
    
    
    protected function connectToDatabase()
    {
        xtc_db_connect() or die('Unable to connect to database server!');
    }
    
    
    protected function defineConstantsFromDbConfigurationTable()
    {
        $query = xtc_db_query('SELECT
                                    `key`,
                                    `value`
                                FROM `gx_configurations`
                                WHERE
                                    `key` != "configuration/CURRENT_TEMPLATE" AND
                                    `key` LIKE "configuration/%"');
        
        while ($configuration = xtc_db_fetch_array($query)) {
            $key = str_replace('configuration/', '', $configuration['key']);
            if (!defined($key)) {
                define($key, $configuration['value']);
            }
        }
        
        if (gm_get_conf('SUPPRESS_INDEX_IN_URL') === 'true') {
            define('FILENAME_DEFAULT', '');
        } else {
            define('FILENAME_DEFAULT', 'index.php');
        }
    }
    
    
    protected function sanitizeRequestData()
    {
        # check GET/POST/COOKIE VARS
        require_once DIR_WS_CLASSES . 'class.inputfilter.php';
        
        $inputFilter = new InputFilter();
        $_GET        = $inputFilter->process($_GET, true);
        $_POST       = $inputFilter->process($_POST, false, ['gambio_api_xml']);
    }
    
    
    protected function startSession()
    {
        new RequiredDirectory(DIR_FS_CATALOG . 'cache' . DIRECTORY_SEPARATOR . 'sessions');
        // define how the session functions will be used
        require_once DIR_WS_FUNCTIONS . 'sessions.php';
        
        gm_set_session_parameters();
        unset($_GET[session_name()]);
        session_start();
    }
    
    
    protected function startTracking()
    {
        include DIR_WS_INCLUDES . 'tracking.php';
        
        $_SESSION['last_activity'] = time();
    }
    
    
    protected function runPrimalExtenders()
    {
        $extender = MainFactory::create_object('ApplicationTopPrimalExtenderComponent');
        $extender->set_data('GET', $_GET);
        $extender->set_data('POST', $_POST);
        $extender->proceed();
    }
    
    
    protected function setCurrentTemplate()
    {
        $templateName = StaticGXCoreLoader::getThemeControl()->getCurrentTheme();
        
        if ($templateName === '') {
            die('No default template available');
        }
        
        define('CURRENT_TEMPLATE', $templateName);
        
        $_SESSION['tpl'] = StaticGXCoreLoader::getThemeControl()->getCurrentTheme();
    }
    
    
    protected function buildTemporaryTheme()
    {
        # build template control instance
        $currentTheme = StaticGXCoreLoader::getThemeControl()->getCurrentTheme();
        
        /** @var PublishedThemeValidationServiceInterface $publishedThemeValidationService */
        $publishedThemeValidationService = StaticGXCoreLoader::getService('PublishedThemeValidation');
        
        if (!$publishedThemeValidationService->publishedThemeIsValid()) {
            
            $publishedThemeValidationService->removePublishedTheme();
        }
        
        if (!file_exists(DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getPublishedThemePath()
                         . '/theme.json')
            && StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
            
            /** @var \ThemeService $themeService */
            $themeService = StaticGXCoreLoader::getService('Theme');
            $themeId      = ThemeId::create($currentTheme);
            
            $publishedThemePath = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getPublishedThemePath();
            
            $source      = ThemeDirectoryRoot::create(new ExistingDirectory(DIR_FS_CATALOG . 'themes'));
            $destination = ThemeDirectoryRoot::create(new RequiredDirectory($publishedThemePath));
            
            $settings = ThemeSettings::create($source, $destination);
            
            $themeService->buildTemporaryTheme($themeId, $settings);
        }
        
        $GLOBALS['coo_template_control'] = MainFactory::create_object('TemplateControl', [$currentTheme], true);
    }
    
    
    protected function includeStyleEdit()
    {
        # include external StyleEdit, if available
        if ($GLOBALS['coo_template_control']->get_template_presentation_version() >= 3
            && StyleEditServiceFactory::service()->styleEditIsInstalled()
            && StyleEditServiceFactory::service()->styleEditTemplateExists()) {
            try {
                $styleName = null;
                
                if (!isset($_REQUEST['theme']) && StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
                    $_REQUEST['theme'] = 'true'; // Toggle StyleEdit theme mode on.
                }
                
                define('STYLE_EDIT_SETTINGS_ENVIRONMENT', 'shop');
                
                $GLOBALS['gmBoxesMaster'] = StyleEditServiceFactory::service()
                    ->getStyleEditReader(StaticGXCoreLoader::getThemeControl()->getCurrentTheme());
            } catch (Exception $e) {
                $logControl = LogControl::get_instance();
                $logControl->notice($e->getMessage(), 'error_handler', 'style_edit_errors');
            }
        }
    }
    
    
    protected function setUpMenuBoxesConfiguration()
    {
        $GLOBALS['coo_template_control']->reset_boxes_master();
    }
    
    
    protected function initializeGlobalSeoBoostObject()
    {
        $GLOBALS['gmSEOBoost'] = MainFactory::create_object('GMSEOBoost', [], true);
    }
    
    
    protected function startGzipOutputBuffer()
    {
        // if gzip_compression is enabled, start to buffer the output
        $httpCaching = MainFactory::create_object('HTTPCaching');
        $httpCaching->start_gzip();
    }
    
    
    protected function verifySession()
    {
        // verify the browser user agent if the feature is enabled
        if (defined('SESSION_CHECK_USER_AGENT') && SESSION_CHECK_USER_AGENT === 'True') {
            $httpUserAgent  = strtolower($_SERVER['HTTP_USER_AGENT']);
            $httpUserAgent2 = strtolower(getenv('HTTP_USER_AGENT'));
            $httpUserAgent  = $httpUserAgent === $httpUserAgent2 ? $httpUserAgent : $httpUserAgent . ';'
                                                                                    . $httpUserAgent2;
            if (!isset ($_SESSION['SESSION_USER_AGENT'])) {
                $_SESSION['SESSION_USER_AGENT'] = $httpUserAgent;
            }
            
            if ($_SESSION['SESSION_USER_AGENT'] !== $httpUserAgent) {
                session_destroy();
                xtc_redirect(xtc_href_link(FILENAME_LOGIN,
                                           'return_url=' . rawurlencode(GM_HTTP_SERVER
                                                                        . gm_get_env_info('REQUEST_URI'))));
            }
        }
        
        // verify the IP address if the feature is enabled
        if (defined('SESSION_CHECK_IP_ADDRESS') && SESSION_CHECK_IP_ADDRESS === 'True') {
            $ipAddress = xtc_get_ip_address();
            if (!isset ($_SESSION['SESSION_IP_ADDRESS'])) {
                $_SESSION['SESSION_IP_ADDRESS'] = $ipAddress;
            }
            
            if ($_SESSION['SESSION_IP_ADDRESS'] !== $ipAddress) {
                session_destroy();
                xtc_redirect(xtc_href_link(FILENAME_LOGIN,
                                           'return_url=' . rawurlencode(GM_HTTP_SERVER
                                                                        . gm_get_env_info('REQUEST_URI'))));
            }
        }
    }
    
    
    protected function runExtenders()
    {
        $extender = MainFactory::create_object('ApplicationTopExtenderComponent');
        $extender->set_data('GET', $_GET);
        $extender->set_data('POST', $_POST);
        $extender->proceed();
    }
    
    
    protected function updateCartRelatedDataInSession()
    {
        // modification for nre graduated system
        unset($_SESSION['actual_content']);
        
        xtc_count_cart();
    }
    
    
    protected function setXSRFPageToken()
    {
        if (!isset($_SESSION['coo_page_token'])) {
            $_SESSION['coo_page_token'] = MainFactory::create('PageToken',
                                                              defined('ACTIVATE_PAGE_TOKEN') ? ACTIVATE_PAGE_TOKEN
                                                                                               === 'true' : false);
        }
    }
    
    
    protected function setSessionAccountType()
    {
        if (isset($_SESSION['customer_id'])) {
            $result      = xtc_db_query('SELECT
                                                `account_type`,
                                                `customers_default_address_id`
		                                    FROM `customers`
		                                    WHERE `customers_id` = ' . (int)$_SESSION['customer_id']);
            $accountType = xtc_db_fetch_array($result);
            
            if (!isset($_SESSION['customer_country_id'])) {
                $result = xtc_db_query('SELECT `entry_country_id`
                                         FROM `address_book`
                                         WHERE
                                            `customers_id` = ' . (int)$_SESSION['customer_id'] . ' AND
                                            `address_book_id` = ' . $accountType['customers_default_address_id']);
                
                $zone                            = xtc_db_fetch_array($result);
                $_SESSION['customer_country_id'] = $zone['entry_country_id'];
            }
            
            $_SESSION['account_type'] = $accountType['account_type'];
        } else {
            $_SESSION['account_type'] = '0';
        }
    }
    
    
    /**
     * Handles custom redirects
     */
    protected function handleCustomRedirect()
    {
        $relativeUri = $_SERVER['REQUEST_URI'];
        if (strpos($_SERVER['REQUEST_URI'], DIR_WS_CATALOG) === 0) {
            $relativeUri = substr($_SERVER['REQUEST_URI'], strlen(DIR_WS_CATALOG));
        }
        $serviceFactory  = LegacyDependencyContainer::getInstance()->get(RedirectServiceFactory::class);
        $redirectService = $serviceFactory->service();
        $redirect        = $redirectService->findRedirectByRelativeUri($relativeUri);
        
        if ($redirect !== null) {
            $configurationService = LegacyDependencyContainer::getInstance()->get(ConfigurationService::class);
            $maxAge               = 0;
            if ($configurationService->has('redirect/maxAge')) {
                $maxAge = (int)$configurationService->find('redirect/maxAge')->value();
            }
            $redirectTypes = $redirectService->getValidRedirectTypes();
            header('Cache-Control: max-age=' . $maxAge);
            header('HTTP/1.1 ' . $redirect->getRedirectType() . ' ' . $redirectTypes[$redirect->getRedirectType()]);
            header('Location: ' . $redirect->getTargetUrl());
            xtc_db_close();
            exit;
        }
    }
    
    
    protected function handle404error()
    {
        if ($this->pageNotFound) {
            $this->handleCustomRedirect();
            self::send404HttpResponse();
        }
    }
    
    
    protected function getParentCategoryIds($categoryId, array $parentIds = [])
    {
        if (count($parentIds) === 0) {
            $parentIds[] = (int)$categoryId;
        }
        
        $query  = 'SELECT `parent_id` FROM `categories` WHERE `categories_id` = ' . (int)$categoryId;
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result) > 0) {
            $parentId = (int)xtc_db_fetch_array($result)['parent_id'];
            if ($parentId !== 0) {
                $parentIds[] = $parentId;
            }
            
            return $parentId === 0 ? $parentIds : $this->getParentCategoryIds($parentId, $parentIds);
        }
        
        return $parentIds;
    }
    
    
    protected function getCategoryNames(array $categoryIds)
    {
        $categoryNames = [];
        $query         = 'SELECT `categories_name`
                            FROM `categories_description`
                            WHERE
                                `categories_id` = :id AND
                                `language_id` = ' . (int)$_SESSION['languages_id'];
        
        foreach ($categoryIds as $categoryId) {
            $result          = xtc_db_query(str_replace(':id', $categoryId, $query));
            $categoryNames[] = xtc_db_fetch_array($result)['categories_name'];
        }
        
        return $categoryNames;
    }
    
    
    protected function isProductInCategory($pId, $catId)
    {
        $query  = 'SELECT *
                    FROM `products_to_categories`
                    WHERE
                        `products_id` = ' . (int)$pId . ' AND
                        `categories_id` = ' . (int)$catId;
        $result = xtc_db_query($query);
        
        return xtc_db_num_rows($result) === 1;
    }
    
    
    protected function updateBreadcrumbSession()
    {
        // creates breadcrumb history entry in session
        if (!array_key_exists('breadcrumb_history', $_SESSION)) {
            $_SESSION['breadcrumb_history'] = [];
        }
        
        // checks if the request was send by category listing, if true, set entry for breadcrumb history
        if (isset($this->cPath) && $this->cPath !== ''
            && str_replace(DIR_WS_CATALOG, '', $_SERVER['SCRIPT_NAME']) !== 'product_info.php') {
            $cPathArray                     = explode('_', $this->cPath);
            $_SESSION['breadcrumb_history'] = [
                'catId' => array_pop($cPathArray)
            ];
        }
    }
    
    
    protected function buildBreadcrumb($languageId)
    {
        $this->initializeGlobalCategoryVariables();
        $this->initializeBreadcrumb();
        $this->addStartPageToBreadcrumb();
        
        $breadcrumbGenerated = $this->addCategoriesBasedOnHistoryToBreadcrumb($languageId);
        
        if (!$breadcrumbGenerated) {
            $breadcrumbGenerated = $this->addCategoriesToBreadcrumb($languageId, $breadcrumbGenerated);
        }
        
        if (!$breadcrumbGenerated && !empty($_GET['manufacturers_id'])) {
            $this->addManufacturerToBreadcrumb();
        }
        
        if ($GLOBALS['product']->isProduct()) {
            $this->addProductToBreadcrumb();
        }
    }
    
    
    protected function handleManufacturerRequest()
    {
        if (isset($_GET['manu']) && !isset($_GET['no_boost'])) {
            $site           = explode('_', $_GET['manu']);
            $manufacturerId = $site[0];
            $manufacturerId = (int)str_replace('m', '', $manufacturerId);
            
            // old xtc SEO url is not supported anymore, so redirect to the non SEO url to avoid duplicate content
            if (!defined('SEARCH_ENGINE_FRIENDLY_URLS')
                || (defined('SEARCH_ENGINE_FRIENDLY_URLS') && SEARCH_ENGINE_FRIENDLY_URLS !== 'true')) {
                $getParams = xtc_get_all_get_params(['manu']);
                if ($getParams === '&') {
                    $getParams = '';
                }
                
                $manufacturerUrl = xtc_href_link(FILENAME_DEFAULT, $getParams . 'manufacturers_id=' . $manufacturerId);
                $manufacturerUrl = str_replace('&amp;', '&', $manufacturerUrl);
                
                $this->redirect($manufacturerUrl);
            }
            
            $_GET['manufacturers_id'] = $manufacturerId;
        }
    }
    
    
    /**
     * @param $languageId
     */
    protected function handleContentRequest($languageId)
    {
        if (empty($_GET['coID']) && strpos($GLOBALS['PHP_SELF'], '/shop_content.php') !== false) {
            $this->pageNotFound = true;
            
            return;
        }
        
        if (isset($_GET['no_boost']) || !isset($_GET['coID'])
            || strpos($GLOBALS['PHP_SELF'], '/shop_content.php') === false) {
            return;
        }
        
        $redirectionUrl = $this->getContentRedirectionUrl();
        $contentUrl     = $GLOBALS['gmSEOBoost']->get_boosted_content_url($GLOBALS['gmSEOBoost']->get_content_id_by_content_group($_GET['coID']),
                                                                          $languageId);
        
        if ($contentUrl === false) {
            $this->pageNotFound = true;
            unset($_GET['coID']);
            
            return;
        }
        
        if ($this->isContentUrlInvalid($redirectionUrl, $contentUrl)) {
            $url = xtc_href_link($contentUrl);
            
            $this->handleRedirection($url, $redirectionUrl);
        }
    }
    
    
    /**
     * @param $languageId
     */
    protected function handleCategoryRequest($languageId)
    {
        if ((!empty($_GET['cat']) || !empty($_GET['cPath'])) && !isset($_GET['no_boost'])) {
            if (!empty($_GET['cat'])) {
                $site = explode('_', $_GET['cat']);

                if ($site[0] === 'c') {
                    $this->pageNotFound = true;
                    $site[0]            = 'c0';
                }

                $categoryId    = $site[0];
                $categoryId    = str_replace('c', '', $categoryId);
                $_GET['cPath'] = xtc_get_category_path($categoryId);
            } else {
                $categoryIds = xtc_parse_category_path($_GET['cPath']);
                $categoryId  = count($categoryIds) ? end($categoryIds) : '0';
            }

            $GLOBALS['cID'] = $categoryId;
            $redirectionUrl = $this->getCategoryRedirectionUrl();

            require_once DIR_FS_INC . 'xtc_check_categories_status.inc.php';

            if (xtc_check_categories_status($categoryId) >= 1) {
                $this->pageNotFound = true;
                $_GET['cPath']  = '0';
                $GLOBALS['cID'] = '0';
            } elseif ($this->isCategoryUrlInvalid($languageId, $categoryId, $redirectionUrl)) {
                $page = '';
                
                if (isset($_GET['page']) && (int)$_GET['page'] > 1) {
                    $page = 'page=' . (int)$_GET['page'];
                }
                
                $url = xtc_href_link($GLOBALS['gmSEOBoost']->get_boosted_category_url($categoryId, $languageId), $page);
                
                $this->handleRedirection($url, $redirectionUrl);
            }
        }
    }
    
    
    /**
     * @param $languageId
     */
    protected function handleProductRequest($languageId)
    {
        require_once DIR_WS_CLASSES . 'product.php';
        
        $this->setGlobalProductVariables($languageId);
        
        if (!isset($GLOBALS['product']) || !is_object($GLOBALS['product'])) {
            $GLOBALS['product'] = new product(0, $languageId);
        } elseif (!isset($_GET['no_boost'])) {
            $redirectionUrl = $this->getProductRedirectionUrl();
            
            if ($this->isProductNotFound()) {
                $this->pageNotFound = true;
            } elseif ($this->isProductUrlNotSeoOptimized()
                      || $this->isProductUrlInvalid($languageId, $redirectionUrl)) {
                $url = xtc_href_link($GLOBALS['gmSEOBoost']->get_boosted_product_url($GLOBALS['product']->data['products_id'],
                                                                                     $GLOBALS['product']->data['products_name'],
                                                                                     $languageId));
                
                $this->handleRedirection($url, $redirectionUrl);
            }
        } elseif ($this->isProductNotFound()) {
            $this->pageNotFound = true;
        }
    }
    
    
    protected function executeCronjobLikeScripts()
    {
        require_once DIR_FS_INC . 'xtc_update_whos_online.inc.php';
        require_once DIR_FS_INC . 'xtc_activate_banners.inc.php';
        require_once DIR_FS_INC . 'xtc_expire_banners.inc.php';
        require_once DIR_FS_INC . 'xtc_expire_specials.inc.php';
        
        // include the who's online functions
        xtc_update_whos_online();
        
        // auto activate and expire banners
        xtc_activate_banners();
        xtc_expire_banners();
        xtc_expire_specials();
    }
    
    
    protected function setMissingGetParams()
    {
        if ($this->isContentRequest()) {
            $boostedName  = xtc_db_prepare_input($_GET['gm_boosted_content']);
            $_GET['coID'] = $GLOBALS['gmSEOBoost']->get_content_coID_by_boost($boostedName);
            
            if ((int)$_GET['coID'] === 0) {
                $this->pageNotFound = true;
            }
        } elseif ($this->isProductRequest()) {
            $boostedName         = xtc_db_prepare_input($_GET['gm_boosted_product']);
            $_GET['products_id'] = $GLOBALS['gmSEOBoost']->get_products_id_by_boost($boostedName);
            
            if ((int)$_GET['products_id'] === 0) {
                $this->pageNotFound = true;
            }
        } elseif ($this->isCategoryRequest() && $_GET['gm_boosted_category'] !== 'index.php') {
            $boostedName = xtc_db_prepare_input($_GET['gm_boosted_category']);
            $categoryId  = $GLOBALS['gmSEOBoost']->get_categories_id_by_boost($boostedName);
            $_GET['cat'] = 'c' . $categoryId;
            
            if ($_GET['cat'] === 'c0') {
                
                // check if invalid category is mixed up with a valid language code
                if (!in_array(strtoupper($boostedName), $this->getActiveLanguageCodes(), true)) {
                    $this->pageNotFound = true;
                }
                
                // unset cat parameter to show index page content
                unset($_GET['cat']);
            }
        }
        
        if (isset($_GET['cat']) && empty($_GET['cat'])) {
            unset($_GET['cat']);
        }
    }
    
    
    protected function runCartActions()
    {
        if (isset($_GET['action'])
            && in_array($_GET['action'],
                        [
                            'update_product',
                            'update_wishlist',
                            'add_product',
                            'wishlist_to_cart',
                            'check_gift',
                            'add_a_quickie',
                            'buy_now',
                            'cust_order',
                        ])) {
            require DIR_WS_INCLUDES . FILENAME_CART_ACTIONS;
        }
    }
    
    
    protected function initializeGlobalMessageStackObject()
    {
        // initialize the message stack for output messages
        $GLOBALS['messageStack'] = new messageStack();
    }
    
    
    protected function setSessionCustomerStatus()
    {
        // write customers status in session
        require DIR_WS_INCLUDES . 'write_customers_status.php';
        
        $this->setSessionAccountType();
    }
    
    
    protected function initializeGlobalMainObject()
    {
        $GLOBALS['main'] = new main();
    }
    
    
    protected function initializeGlobalXtcPriceObject()
    {
        $GLOBALS['xtPrice'] = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
    }
    
    
    protected function setSessionObjects()
    {
        if (!is_object($_SESSION['cart'])) {
            $_SESSION['cart'] = new shoppingCart();
        }
        
        $_SESSION['cart']->cleanup();
        
        $this->updateCartRelatedDataInSession();
        
        if (!is_object($_SESSION['wishList'])) {
            $_SESSION['wishList'] = new wishList();
        }
        
        // TODO delete lightbox code
        if (!is_object($_SESSION['lightbox'])) {
            require_once DIR_FS_CATALOG . 'gm/classes/GMLightboxControl.php';
            $_SESSION['lightbox'] = MainFactory::create('GMLightboxControl');
        }
        
        if (!is_object($_SESSION['coo_filter_manager'])) {
            $_SESSION['coo_filter_manager'] = MainFactory::create('FilterManager');
        }
    }
    
    
    protected function setSessionCountry()
    {
        if (isset($_GET['switch_country']) && is_string($_GET['switch_country'])) {
            $isoCode = strtoupper(trim($_GET['switch_country']));
            
            if ($isoCode !== '') {
                /* @var Countries $countries */
                $countries = MainFactory::create('Countries', [$_SESSION['languages_id'], true, true]);
                
                /* @var CountrySessionWriter $countrySessionWriter */
                $countrySessionWriter = MainFactory::create('CountrySessionWriter', $countries);
                $countrySessionWriter->setSessionIsoCode($isoCode);
                $countrySessionWriter->setSessionCountryIdByIsoCode($isoCode);
            }
        }
    }
    
    
    protected function setSessionCurrency()
    {
        if (isset($_GET['currency']) && xtc_currency_exists($_GET['currency'])) {
            $_SESSION['currency'] = xtc_currency_exists($_GET['currency']);
        }
        
        if (!isset($_SESSION['currency'])) {
            $_SESSION['currency'] = (USE_DEFAULT_LANGUAGE_CURRENCY === 'true'
                                     && xtc_currency_exists(LANGUAGE_CURRENCY)) ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
        }
    }
    
    
    protected function initLanguage()
    {
        $this->setSessionLanguage();
        
        // needs to be initialized after $_SESSION['languages_id'] is set
        $GLOBALS['coo_lang_file_master'] = MainFactory::create_object('LanguageTextManager', [], true);
        $coo_lang_file_master            = $GLOBALS['coo_lang_file_master'];
        
        // include the language translations
        require DIR_WS_LANGUAGES . $_SESSION['language'] . '/init.inc.php';
    }
    
    
    protected function setSessionLanguage()
    {
        $this->setMissingGetParams();
        
        $urlLangParam        = $this->getUrlLanguageCode();
        $defaultLanguageCode = $this->getDefaultLanguageCode();
        
        // set the language
        $languageCode = '';
        
        if (isset($_GET['language'])) {
            $languageCode = $_GET['language'];
        } elseif ($urlLangParam !== '' && $urlLangParam !== 'invalid') {
            $languageCode = $urlLangParam;
        } elseif ($this->isProductRequest() && !empty($_GET['products_id'])) {
            $languageData = $GLOBALS['gmSEOBoost']->get_language_data('product',
                                                                      $_GET['products_id'],
                                                                      false,
                                                                      $_GET['gm_boosted_product']);
            $languageCode = $languageData['code'];
        } elseif ($this->isCategoryRequest() && $_GET['gm_boosted_category'] !== 'index.php'
                  && !empty($_GET['cat'])) {
            $languageData = $GLOBALS['gmSEOBoost']->get_language_data('category',
                                                                      substr($_GET['cat'], 1),
                                                                      false,
                                                                      $_GET['gm_boosted_category']);
            $languageCode = $languageData['code'];
        } elseif ($this->isContentRequest() && !empty($_GET['coID'])) {
            $languageData = $GLOBALS['gmSEOBoost']->get_language_data('content',
                                                                      (int)$_GET['coID'],
                                                                      false,
                                                                      $_GET['gm_boosted_content']);
            $languageCode = $languageData['code'];
        } elseif (isset($_SESSION['language'])) {
            $languageCode = $_SESSION['language_code'];
        }
        
        if (!in_array(strtolower($languageCode), array_map('strtolower', $GLOBALS['activeLanguages']), true)) {
            $languageCode = $defaultLanguageCode;
        }
        
        $this->setSessionLanguageData(xtc_input_validation($languageCode, 'char', ''));
    }
    
    
    protected function handleChangeOfLanguageOrCurrencyOrCountry()
    {
        $urlLanguageCode                = $this->getUrlLanguageCode();
        $languageCurrencyCountryChanged = $this->hasLanguageOrCurrencyOrCountryChanged();
        
        if ($languageCurrencyCountryChanged) {
            $url = $this->getUrlWithoutLanguageCode($urlLanguageCode);
            
            if ($this->isLanguageCodeForUrlsActivated()) {
                $url = $_SESSION['language_code'] . '/' . $url;
            }
            
            $getParams = substr(xtc_get_all_get_params([
                                                           'gm_boosted_product',
                                                           'gm_boosted_category',
                                                           'gm_boosted_content',
                                                       ]),
                                0,
                                -1);
            
            $url = $this->getRedirectionUrl($url, $getParams);
            
            if ($this->isRedirectionAllowed($url)) {
                $_SESSION['last_redirect_url'] = $url;
                
                $this->redirect($url);
            }
        }
    }
    
    
    protected function checkRequestUriForCorrectLanguageCode()
    {
        if (!$this->isFrontendGetRequest()) {
            return;
        }
        
        $urlLanguageCode      = $this->getUrlLanguageCode();
        $defaultLanguageCode  = $this->getDefaultLanguageCode();
        $languageGetParameter = $this->getLanguageGetParameter();
        
        if ($this->isLanguageCodeForUrlsActivated()) {
            if ($languageGetParameter !== '' || $this->isUrlLanguageCodeInvalid($urlLanguageCode)
                || $this->isRequestUriMissingTrailingSlashAfterLanguageCode()) {
                $languageCode = $this->getLanguageCode($urlLanguageCode, $defaultLanguageCode, $languageGetParameter);
                
                $this->setSessionLanguageData($languageCode);
                
                $url = $this->getUrlWithoutLanguageCode($urlLanguageCode);
                $url = $languageCode . '/' . $url;
                $url = $this->getRedirectionUrl($url, $this->getAllGetParams());
                
                if ($this->isRedirectionAllowed($url)) {
                    $_SESSION['last_redirect_url'] = $url;
                    
                    $this->redirect($url);
                }
            } elseif (!$this->isUrlLanguageCodeInvalid($urlLanguageCode)) {
                $_GET['language'] = $urlLanguageCode;
                
                $this->setSessionLanguageData($urlLanguageCode);
            }
        } elseif ($urlLanguageCode !== ''
                  || $this->isLanguageGetParamNotMatchingSessionLanguage($languageGetParameter)) {
            
            $languageCode = $this->getLanguageCode($urlLanguageCode, $defaultLanguageCode, $languageGetParameter);
            
            $this->setSessionLanguageData($languageCode);
            
            $getParams = substr(xtc_get_all_get_params([
                                                           'gm_boosted_product',
                                                           'gm_boosted_category',
                                                           'gm_boosted_content',
                                                       ]),
                                0,
                                -1);
            
            $url = $this->getUrlWithoutLanguageCode($urlLanguageCode);
            $url = $this->getRedirectionUrl($url, $getParams);
            
            if ($this->isRedirectionAllowed($url)) {
                $_SESSION['last_redirect_url'] = $url;
                
                $this->redirect($url);
            }
        }
    }
    
    
    /**
     * Returns language parameter like 'de' on success and 'invalid' on failure.
     *
     * @return string
     */
    protected function getUrlLanguageCode()
    {
        if ($this->urlLangParam === null) {
            $this->getActiveLanguageCodes();
            
            if (DIR_WS_CATALOG === '/') {
                $searchPattern = '/^\/([a-zA-Z0-9]{2})(\/.*)?$/';
            } else {
                $searchPattern = '/^\/' . str_replace('/', '\/', substr(DIR_WS_CATALOG, 1, -1))
                                 . '\/([a-zA-Z0-9]{2})(\/.*)?$/';
            }
            
            preg_match($searchPattern, $_SERVER['REQUEST_URI'], $matches);
            $this->urlLangParam = '';
            
            if (count($matches) && in_array(strtoupper($matches[1]), $GLOBALS['activeLanguages'], true)) {
                $this->urlLangParam = strtolower($matches[1]);
            } elseif (count($matches) && !in_array(strtoupper($matches[1]), $GLOBALS['activeLanguages'], true)) {
                $this->urlLangParam = 'invalid';
            }
        }
        
        return $this->urlLangParam;
    }
    
    
    protected function startStopWatch()
    {
        StopWatch::get_instance()->add_specific_time_stamp('start', PAGE_PARSE_START_TIME);
    }
    
    
    protected function updateTimezone($timezone)
    {
        $this->getTimezoneSetter()->set_date_default_timezone($timezone);
    }
    
    
    protected function resetSessionRedirectionInfo()
    {
        if (isset($_SESSION['last_redirect_url'])) {
            unset($_SESSION['last_redirect_url']);
        }
    }
    
    
    /**
     * @param string $languageCode
     */
    protected function setSessionLanguageData($languageCode)
    {
        if ($_SESSION['language_code'] === $languageCode) {
            return;
        }
        
        $language = new language($languageCode);
        
        $queryBuilder = StaticGXCoreLoader::getDatabaseQueryBuilder();
        
        $result = $queryBuilder->select('value')
            ->from('gx_configurations')
            ->where('key', 'gm_configuration/GM_CHECK_BROWSER_LANGUAGE')
            ->get()
            ->row_array();
        
        if (!isset($_SESSION['language']) && !isset($_GET['language'])
            && $result
            && $result['value'] === '1') {
            $language->get_browser_language();
        }
        
        $_SESSION['language']         = $language->language['directory'];
        $_SESSION['languages_id']     = $language->language['id'];
        $_SESSION['language_charset'] = $language->language['language_charset'];
        $_SESSION['language_code']    = $language->language['code'];
    }
    
    
    /**
     * Returns a product url if current request belongs to the product info page, otherwise an empty string is returned.
     *
     * @return string
     */
    protected function getProductUrl()
    {
        $boostedName = xtc_db_prepare_input($_GET['gm_boosted_product']);
        $productId   = $GLOBALS['gmSEOBoost']->get_products_id_by_boost($boostedName);
        $url         = '';
        
        if (!empty($productId)) {
            $url = $GLOBALS['gmSEOBoost']->get_boosted_product_url($productId);
            
            if ($this->isLanguageCodeForUrlsActivated()) {
                $languageCodeUrlPartLength = 3;
                $url                       = substr($url, $languageCodeUrlPartLength);
            }
        } else {
            $this->pageNotFound = true;
            $this->pageNotFound = true;
        }
        
        return $url;
    }
    
    
    /**
     * Returns a category url if current request belongs to a category page, otherwise an empty string is returned.
     *
     * @return string
     */
    protected function getCategoryUrl()
    {
        $boostedName = xtc_db_prepare_input($_GET['gm_boosted_category']);
        $categoryId  = $GLOBALS['gmSEOBoost']->get_categories_id_by_boost($boostedName);
        $url         = '';
        
        if (!empty($categoryId)) {
            $url = $GLOBALS['gmSEOBoost']->get_boosted_category_url($categoryId);
            
            if ($this->isLanguageCodeForUrlsActivated()) {
                $languageCodeUrlPartLength = 3;
                $url                       = substr($url, $languageCodeUrlPartLength);
            }
        } elseif (!in_array(strtoupper($boostedName), $this->getActiveLanguageCodes(), true)) {
            $this->pageNotFound = true;
        }
        
        return $url;
    }
    
    
    /**
     * Returns a content page url if current request belongs to a content page, otherwise an empty string is returned.
     *
     * @return string
     */
    protected function getContentUrl()
    {
        $boostedName    = xtc_db_prepare_input($_GET['gm_boosted_content']);
        $contentGroupId = $GLOBALS['gmSEOBoost']->get_content_coID_by_boost($boostedName);
        $contentId      = $GLOBALS['gmSEOBoost']->get_content_id_by_content_group($contentGroupId);
        $url            = '';
        
        if (!empty($contentId)) {
            $url = $GLOBALS['gmSEOBoost']->get_boosted_content_url($contentId);
            
            if ($this->isLanguageCodeForUrlsActivated()) {
                $languageCodeUrlPartLength = 3;
                $url                       = substr($url, $languageCodeUrlPartLength);
            }
        } else {
            $this->pageNotFound = true;
        }
        
        return $url;
    }
    
    
    /**
     * Returns a request specific language code like 'de'.
     *
     * @param $urlLanguageCode
     * @param $defaultLanguageCode
     * @param $languageGetParameter
     *
     * @return mixed
     */
    protected function getLanguageCode($urlLanguageCode, $defaultLanguageCode, $languageGetParameter)
    {
        $languageCode = $languageGetParameter !== 'invalid' ? $languageGetParameter : '';
        $languageCode = $languageCode !== '' || $urlLanguageCode === 'invalid' ? $languageCode : $urlLanguageCode;
        $languageCode = $languageCode !== '' ? $languageCode : (string)$_SESSION['language_code'];
        
        if ($languageCode === '' && gm_get_conf('GM_CHECK_BROWSER_LANGUAGE') === '1') {
            $language = new language();
            $language->get_browser_language();
            $defaultLanguageCode = $language->language['code'];
        }
        
        $languageCode = $languageCode !== '' ? $languageCode : $defaultLanguageCode;
        
        return $languageCode;
    }
    
    
    /**
     * Returns all GET params of current request uri as a url query string.
     *
     * @return string
     */
    protected function getAllGetParams()
    {
        $getParams = substr(xtc_get_all_get_params([
                                                       'language',
                                                       'gm_boosted_product',
                                                       'gm_boosted_category',
                                                       'gm_boosted_content'
                                                   ]),
                            0,
                            -1);
        
        return $getParams;
    }
    
    
    /**
     * @return string
     */
    protected function getContentRedirectionUrl()
    {
        return $this->getRedirectionUrlByPage('shop_content.php');
    }
    
    
    /**
     * @return string
     */
    protected function getCategoryRedirectionUrl()
    {
        return $this->getRedirectionUrlByPage('index.php');
    }
    
    
    /**
     * @return string
     */
    protected function getProductRedirectionUrl()
    {
        return $this->getRedirectionUrlByPage('product_info.php');
    }
    
    
    /**
     * @param $page
     *
     * @return string
     */
    protected function getRedirectionUrlByPage($page)
    {
        $redirectionUrl = $_SERVER['REDIRECT_URL'] ?? '';
        $page           = '/' . $page;
        
        if (!empty($_SERVER['REDIRECT_SCRIPT_URL'])) {
            $redirectionUrl = $_SERVER['REDIRECT_SCRIPT_URL'];
        }
        
        if (isset($_SERVER['SCRIPT_URL'])
            && (strpos($redirectionUrl, $page) !== false
                || $redirectionUrl === '')) {
            $redirectionUrl = $_SERVER['SCRIPT_URL'];
        }
        
        if (isset($_SERVER['PATH_INFO'])
            && (strpos($redirectionUrl, $page) !== false
                || $redirectionUrl === '')) {
            $redirectionUrl = $_SERVER['PATH_INFO'];
        }
        
        if (isset($_SERVER['REQUEST_URI'])
            && (strpos($redirectionUrl, $page) !== false
                || $redirectionUrl === '')) {
            $redirectionUrl = $_SERVER['REQUEST_URI'];
        }
        
        if (isset($_SERVER['PHP_SELF']) && empty($redirectionUrl)) {
            $redirectionUrl = $_SERVER['PHP_SELF'];
        }
        
        $redirectionUrl = strtok($redirectionUrl, '?');
        
        if (preg_match('#[^/](/[^/]+)#', GM_HTTP_SERVER, $matches) && strpos($redirectionUrl, $matches[1]) === 0) {
            $redirectionUrl = substr($redirectionUrl, strlen($matches[1]));
        }
        
        return $redirectionUrl;
    }
    
    
    protected function initializeGlobalPhpSelfVariable()
    {
        # set php_self in the local scope
        $GLOBALS['PHP_SELF'] = gm_get_env_info('PHP_SELF');
    }
    
    
    /**
     * @return \LanguageProvider
     */
    protected function getLanguageProvider()
    {
        if ($this->languageProvider === null) {
            $this->languageProvider = MainFactory::create('LanguageProvider',
                                                          StaticGXCoreLoader::getDatabaseQueryBuilder());
        }
        
        return $this->languageProvider;
    }
    
    
    /**
     * @return string
     */
    protected function getDefaultLanguageCode()
    {
        if ($this->defaultLanguageCode === null) {
            $this->defaultLanguageCode = $this->getLanguageProvider()->getDefaultLanguageCode();
        }
        
        return $this->defaultLanguageCode;
    }
    
    
    /**
     * @return int
     */
    protected function getLanguageId()
    {
        return (int)$_SESSION['languages_id'];
    }
    
    
    /**
     * @param $url
     */
    protected function redirect($url)
    {
        if (function_exists('xtc_db_close')) {
            xtc_db_close();
        }
        
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Location: ' . $url, true, 301);
        
        exit;
    }
    
    
    protected function initializeGlobalCategoryVariables()
    {
        $GLOBALS['cPath'] = '';
        
        // calculate category path
        if (isset ($_GET['cPath'])) {
            $GLOBALS['cPath'] = xtc_input_validation($_GET['cPath'], 'cPath', '');
        } elseif (!isset($_GET['manufacturers_id']) && is_object($GLOBALS['product'])
                  && $GLOBALS['product']->isProduct()) {
            $GLOBALS['cPath'] = xtc_get_product_path($GLOBALS['actual_products_id']);
        }
        
        $GLOBALS['cPath_array'] = [];
        if (!empty($GLOBALS['cPath'])) {
            $GLOBALS['cPath_array']         = xtc_parse_category_path($GLOBALS['cPath']);
            $GLOBALS['cPath']               = implode('_', $GLOBALS['cPath_array']);
            $GLOBALS['current_category_id'] = end($GLOBALS['cPath_array']);

            require_once DIR_FS_INC . 'xtc_check_categories_status.inc.php';

            if (xtc_check_categories_status($GLOBALS['current_category_id']) >= 1) {
                $GLOBALS['cPath_array']         = [];
                $GLOBALS['cPath']               = '0';
                $GLOBALS['current_category_id'] = 0;
                $this->pageNotFound = true;
            }
        } else {
            $GLOBALS['current_category_id'] = 0;
        }
        $this->cPath = $GLOBALS['cPath'];
    }
    
    
    protected function sendHeader()
    {
        header('Content-Type: text/html; charset=' . $_SESSION['language_charset'] . '');
        
        $legacyContainer = \LegacyDependencyContainer::getInstance();
        /** @var ConfigurationFinder $finder */
        $finder = $legacyContainer->get(ConfigurationFinder::class);
        
        if ($finder->get('gm_configuration/SEND_X_FRAME_OPTIONS_SAMEORIGIN_HEADER') === 'true') {
            header('X-Frame-Options: SAMEORIGIN');
        }
    }
    
    
    /**
     * @return bool
     */
    protected function hasLanguageOrCurrencyOrCountryChanged()
    {
        if (isset($_GET['do']) && $_GET['do'] === 'Filter') {
            return false;
        }
        
        $languageCurrencyCountryChanged = false;
        
        if (isset($_POST['language'])
            && in_array(strtolower($_POST['language']), array_map('strtolower', $GLOBALS['activeLanguages']))
               === true) {
            $languageCode = xtc_input_validation($_POST['language'], 'char', '');
            $this->setSessionLanguageData($languageCode);
            $languageCurrencyCountryChanged = true;
        }
        
        if (isset($_POST['currency']) && xtc_currency_exists($_POST['currency'])) {
            $_SESSION['currency']           = xtc_currency_exists($_POST['currency']);
            $languageCurrencyCountryChanged = true;
        }
        
        if (isset($_POST['switch_country'])) {
            $isoCode = strtoupper(trim($_POST['switch_country']));
            
            if ($isoCode !== '') {
                /* @var Countries $countries */
                $countries = MainFactory::create('Countries', $_SESSION['languages_id'], true, true);
                
                /* @var CountrySessionWriter $countrySessionWriter */
                $countrySessionWriter = MainFactory::create('CountrySessionWriter', $countries);
                $countrySessionWriter->setSessionIsoCode($isoCode);
                $countrySessionWriter->setSessionCountryIdByIsoCode($isoCode);
            }
            $languageCurrencyCountryChanged = true;
        }
        
        return $languageCurrencyCountryChanged;
    }
    
    
    /**
     * @return string
     */
    protected function getConnectionType()
    {
        return GM_HTTP_SERVER === HTTPS_SERVER ? 'SSL' : 'NONSSL';
    }
    
    
    /**
     * @return string
     */
    protected function getLanguageGetParameter()
    {
        $getLangParam = '';
        
        if (isset($_GET['language'])) {
            $getLangParam = strtolower($_GET['language']);
        }
        
        if ($getLangParam !== '' && !in_array(strtoupper($getLangParam), $GLOBALS['activeLanguages'], true)) {
            $getLangParam = 'invalid';
        }
        
        return $getLangParam;
    }
    
    
    /**
     * @return bool
     */
    protected function isFrontendGetRequest()
    {
        require_once DIR_FS_CATALOG . 'GXMainComponents/Shared/FrontendFilenamesProvider.inc.php';
        
        return empty($_POST)
               && SecurityCheck::getHtaccessVersion() >= 2.1
               && in_array(basename(gm_get_env_info('SCRIPT_NAME')), FrontendFilenamesProvider::getFilenames(), true);
    }
    
    
    /**
     * @return bool
     */
    protected function isLanguageCodeForUrlsActivated()
    {
        return gm_get_conf('USE_SEO_BOOST_LANGUAGE_CODE') === 'true';
    }
    
    
    /**
     * @param $urlLanguageCode
     *
     * @return bool
     */
    protected function isUrlLanguageCodeInvalid($urlLanguageCode)
    {
        return $urlLanguageCode === '' || $urlLanguageCode === 'invalid'
               || (!empty($_SESSION['language_code'])
                   && $urlLanguageCode !== $_SESSION['language_code']);
    }
    
    
    /**
     * @return bool
     */
    protected function isProductRequest()
    {
        return !empty($_GET['gm_boosted_product']);
    }
    
    
    /**
     * @return bool
     */
    protected function isCategoryRequest()
    {
        return !empty($_GET['gm_boosted_category']);
    }
    
    
    /**
     * @return bool
     */
    protected function isContentRequest()
    {
        return !empty($_GET['gm_boosted_content']);
    }
    
    
    /**
     * @param $languageGetParameter
     *
     * @return bool
     */
    protected function isLanguageGetParamNotMatchingSessionLanguage($languageGetParameter)
    {
        return $languageGetParameter !== '' && !empty($_SESSION['language_code'])
               && $languageGetParameter !== $_SESSION['language_code'];
    }
    
    
    /**
     * @param $urlLanguageCode
     *
     * @return string
     */
    protected function getPageUrl($urlLanguageCode)
    {
        $url = $_SERVER['REQUEST_URI'];
        $url = substr($url, strlen(DIR_WS_CATALOG));
        
        if (strlen($url) === 2) {
            $url .= '/';
        }
        
        if (strpos($url, '?') !== false) {
            $url = substr($url, 0, strpos($url, '?'));
        }
        
        $urlParts = explode('/', $url);
        
        if ($urlLanguageCode !== '') {
            array_shift($urlParts);
        }
        
        $url = implode('/', $urlParts);
        
        return $url;
    }
    
    
    /**
     * @return bool
     */
    protected function isRequestUriMissingTrailingSlashAfterLanguageCode()
    {
        return strlen(substr(strtok($_SERVER['REQUEST_URI'], '?'), strlen(DIR_WS_CATALOG))) === 2;
    }
    
    
    /**
     * @return array
     */
    protected function getActiveLanguageCodes()
    {
        if (isset($GLOBALS['activeLanguages'])) {
            return $GLOBALS['activeLanguages'];
        }
        
        $languageProvider = $this->getLanguageProvider();
        
        $activeCodes                = $languageProvider->getActiveCodes();
        $GLOBALS['activeLanguages'] = [];
        
        foreach ($activeCodes as $code) {
            $GLOBALS['activeLanguages'][] = $code->asString();
        }
        
        return $GLOBALS['activeLanguages'];
    }
    
    
    /**
     * @param $urlLanguageCode
     *
     * @return string
     */
    protected function getUrlWithoutLanguageCode($urlLanguageCode)
    {
        if ($this->isProductRequest()) {
            $url = $this->getProductUrl();
        } elseif ($this->isCategoryRequest()) {
            $url = $this->getCategoryUrl();
        } elseif ($this->isContentRequest()) {
            $url = $this->getContentUrl();
        } else {
            $url = $this->getPageUrl($urlLanguageCode);
        }
        
        return $url;
    }
    
    
    /**
     * @param $url
     *
     * @return bool
     */
    protected function isRedirectionAllowed($url)
    {
        return !isset($_SESSION['last_redirect_url']) && $_SESSION['last_redirect_url'] !== $url
               && $this->pageNotFound === false;
    }
    
    
    /**
     * @param $url
     * @param $getParams
     *
     * @return string
     */
    protected function getRedirectionUrl($url, $getParams)
    {
        $url = xtc_href_link($url, $getParams, $this->getConnectionType(), true, true, false, false, true);
        
        return $url;
    }
    
    
    /**
     * @param $languageId
     * @param $redirectionUrl
     *
     * @return bool
     */
    protected function isProductUrlInvalid($languageId, $redirectionUrl)
    {
        return $this->isProductRequest()
               && $GLOBALS['gmSEOBoost']->boost_products
               && strpos($GLOBALS['PHP_SELF'], '/product_info.php') !== false
               && (strpos($redirectionUrl,
                          DIR_WS_CATALOG
                          . $GLOBALS['gmSEOBoost']->get_boosted_product_url($GLOBALS['gmSEOBoost']->get_products_id_by_boost($_GET['gm_boosted_product']),
                                                                            $_GET['gm_boosted_product'],
                                                                            $languageId)) === false
                   || strpos($redirectionUrl,
                             DIR_WS_CATALOG
                             . $GLOBALS['gmSEOBoost']->get_boosted_product_url($GLOBALS['gmSEOBoost']->get_products_id_by_boost($_GET['gm_boosted_product']),
                                                                               $_GET['gm_boosted_product'],
                                                                               $languageId)) !== 0);
    }
    
    
    /**
     * @return bool
     */
    protected function isProductUrlNotSeoOptimized()
    {
        return $GLOBALS['gmSEOBoost']->boost_products
               && empty($_GET['gm_boosted_product'])
               && strpos($GLOBALS['PHP_SELF'], '/product_info.php') !== false;
    }
    
    
    /**
     * @return bool
     */
    protected function isProductNotFound()
    {
        return $GLOBALS['product']->isProduct === false
               && strpos($GLOBALS['PHP_SELF'], '/product_info.php') !== false;
    }
    
    
    /**
     * @param $languageId
     */
    protected function setGlobalProductVariables($languageId)
    {
        if (isset ($_GET['info'])) {
            $site = explode('_', $_GET['info']);
            if (preg_match('/^p[\d]+((?:\{[\d]+\}[\d]+)+)x[\d]+$/', $site[0]) ||
                preg_match('/^p[\d]+((?:\{[\d]+\}[\d]+)+)$/', $site[0]) ||
                preg_match('/^p[\d]+x[\d]+$/', $site[0]) ||
                preg_match('/^p[\d]+$/', $site[0])
            ) {
                $pID                           = $site[0];
                $GLOBALS['actual_products_id'] = (int)str_replace('p', '', $pID);
                $GLOBALS['product']            = new product($GLOBALS['actual_products_id'], $languageId);
            } else {
                $this->pageNotFound = true;
            }
        } // also check for old 3.0.3 URLS
        elseif (isset($_GET['products_id'])) {
            $GLOBALS['actual_products_id'] = (int)$_GET['products_id'];
            $GLOBALS['product']            = new product($GLOBALS['actual_products_id'], $languageId);
        }
    }
    
    
    /**
     * @param $redirectionUrl
     */
    protected function logRedirectionLoop($redirectionUrl)
    {
        $message    = 'Redirect to ' . $redirectionUrl . ' failed';
        $logControl = LogControl::get_instance();
        $logControl->notice($message, 'error_handler', 'redirect_loops');
    }
    
    
    /**
     * @param $url
     * @param $redirectionUrl
     */
    protected function handleRedirection($url, $redirectionUrl)
    {
        if ($this->isRedirectionAllowed($url)) {
            $_SESSION['last_redirect_url'] = $url;
            
            $this->redirect($url);
        } else {
            $this->logRedirectionLoop($redirectionUrl);
        }
    }
    
    
    /**
     * @param $languageId
     * @param $categoryId
     * @param $redirectionUrl
     *
     * @return bool
     */
    protected function isCategoryUrlInvalid($languageId, $categoryId, $redirectionUrl)
    {
        return !isset($_GET['filter_fv_id'], $_GET['filter_price_min'], $_GET['filter_price_max'], $_GET['manufacturers_id'], $_GET['filter_id'], $_GET['view_mode'], $_GET['listing_sort'], $_GET['listing_count'])
               && !empty($categoryId)
               && $GLOBALS['gmSEOBoost']->boost_categories
               && $redirectionUrl !== DIR_WS_CATALOG . $GLOBALS['gmSEOBoost']->get_boosted_category_url($categoryId,
                                                                                                        $languageId);
    }
    
    
    /**
     * @param $redirectionUrl
     * @param $contentUrl
     *
     * @return bool
     */
    protected function isContentUrlInvalid($redirectionUrl, $contentUrl)
    {
        return ($GLOBALS['gmSEOBoost']->boost_content
                && $this->isContentRequest()
                && $redirectionUrl !== DIR_WS_CATALOG . $contentUrl)
               || ($GLOBALS['gmSEOBoost']->boost_content
                   && empty($_GET['gm_boosted_content'])
                   && !isset($_GET['action']));
    }
    
    
    protected function initializeBreadcrumb()
    {
        // include the breadcrumb class and start the breadcrumb trail
        require_once DIR_WS_CLASSES . 'breadcrumb.php';
        
        /** @var breadcrumb_ORIGIN $GLOBALS ['breadcrumb'] */
        $GLOBALS['breadcrumb'] = new breadcrumb;
    }
    
    
    protected function addStartPageToBreadcrumb()
    {
        $GLOBALS['breadcrumb']->add(HEADER_TITLE_TOP, xtc_href_link(FILENAME_DEFAULT));
    }
    
    
    /**
     * @param $languageId
     *
     * @return bool
     */
    protected function addCategoriesBasedOnHistoryToBreadcrumb($languageId)
    {
        // try to detect breadcrumb trail by history
        // first, we have to ensure that the breadcrumb logic is only used by product detail pages
        $breadcrumbGenerated = false;
        $script              = str_replace(DIR_WS_CATALOG, '', $_SERVER['SCRIPT_NAME']);
        if ($script === 'product_info.php'
            && array_key_exists('breadcrumb_history', $_SESSION)
            && isset($GLOBALS['actual_products_id'])
            && count($_SESSION['breadcrumb_history']) > 0
            && $this->isProductInCategory($GLOBALS['actual_products_id'], $_SESSION['breadcrumb_history']['catId'])) {
            
            // it is required that we are on the products details page and the product is linked in the category
            $categoryIds   = array_reverse($this->getParentCategoryIds($_SESSION['breadcrumb_history']['catId']));
            $categoryNames = $this->getCategoryNames($categoryIds);
            
            foreach ($categoryNames as $key => $categoryName) {
                if ($GLOBALS['gmSEOBoost']->boost_categories) {
                    $setCategoryUrl = xtc_href_link($GLOBALS['gmSEOBoost']->get_boosted_category_url($categoryIds[$key],
                                                                                                     $languageId));
                } else {
                    $setCategoryUrl = xtc_href_link(FILENAME_DEFAULT,
                                                    xtc_category_link($categoryIds[$key], $categoryName));
                }
                
                $GLOBALS['breadcrumb']->add($categoryName, $setCategoryUrl);
            }
            
            $breadcrumbGenerated = true;
        }
        
        return $breadcrumbGenerated;
    }
    
    
    /**
     * @param $languageId
     * @param $breadcrumbGenerated
     *
     * @return bool
     */
    protected function addCategoriesToBreadcrumb($languageId, $breadcrumbGenerated)
    {
        // add category names or the manufacturer name to the breadcrumb trail
        if (!$breadcrumbGenerated && isset($GLOBALS['cPath_array'])) {
            $groupCheck = '';
            
            for ($i = 0, $n = count($GLOBALS['cPath_array']); $i < $n; $i++) {
                if (GROUP_CHECK === 'true') {
                    $groupCheck = ' AND c.`group_permission_'
                                  . (int)$_SESSION['customers_status']['customers_status_id'] . '` = 1 ';
                }
                
                $result = xtc_db_query('SELECT cd.`categories_name`
                                                    FROM
                                                        `categories_description` cd,
                                                        `categories` c
                                                    WHERE
                                                        cd.`categories_id` = ' . (int)$GLOBALS['cPath_array'][$i] . ' AND
                                                        c.`categories_id` = cd.`categories_id`
                                                        ' . $groupCheck . ' AND
                                                        cd.`language_id` = ' . (int)$_SESSION['languages_id']);
                if (xtc_db_num_rows($result, true) > 0) {
                    $categories = xtc_db_fetch_array($result, true);
                    
                    if ($GLOBALS['gmSEOBoost']->boost_categories) {
                        $categoryUrl = xtc_href_link($GLOBALS['gmSEOBoost']->get_boosted_category_url($GLOBALS['cPath_array'][$i],
                                                                                                      $languageId));
                    } else {
                        $categoryUrl = xtc_href_link(FILENAME_DEFAULT,
                                                     xtc_category_link($GLOBALS['cPath_array'][$i],
                                                                       $categories['categories_name']));
                    }
                    
                    if ($i + 1 === $n && !$GLOBALS['product']->isProduct()) {
                        $GLOBALS['breadcrumb']->add($categories['categories_name']);
                    } else {
                        $GLOBALS['breadcrumb']->add($categories['categories_name'], $categoryUrl);
                    }
                    
                    $breadcrumbGenerated = true;
                } else {
                    $this->pageNotFound = true;
                    unset($_GET['cat']);
                    break;
                }
            }
        }
        
        return $breadcrumbGenerated;
    }
    
    
    protected function addManufacturerToBreadcrumb()
    {
        $result        = xtc_db_query('SELECT `manufacturers_name`
                                                FROM `manufacturers`
                                                WHERE `manufacturers_id` = ' . (int)$_GET['manufacturers_id']);
        $manufacturers = xtc_db_fetch_array($result);
        
        $GLOBALS['breadcrumb']->add($manufacturers['manufacturers_name']);
    }
    
    
    protected function addProductToBreadcrumb()
    {
        $GLOBALS['breadcrumb']->add($GLOBALS['product']->data['products_name']);
    }
    
    
    /**
     * adds a history to the session with the requested uri's and it's post/get parameters
     * currently only activated if the ACTIVATE_NAVIGATOR module is enabled
     *
     * once there are more use cases for the new history service it should always be initiated
     * not only if the ACTIVATE_NAVIGATOR module is enabled
     */
    protected function initializeHistory(): void
    {
        if (ACTIVATE_NAVIGATOR == 'true') {
            
            /** @var UserNavigationHistoryService $service */
            $service = LegacyDependencyContainer::getInstance()->get(UserNavigationHistoryService::class);
            $service->setHistory($_SESSION['history'] ?? null);
            $service->addHistoryEntry($_SERVER['REQUEST_URI'], $_GET);
            $_SESSION['history'] = $service->history();
        }
    }
}
