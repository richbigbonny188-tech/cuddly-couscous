<?php

/* --------------------------------------------------------------
   StyleEdit4AuthenticationController.php 2020-01-10
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Firebase\JWT\JWT;
use Gambio\StyleEdit\Core\Components\Theme\Validator;
use Gambio\StyleEdit\DependencyInjector;

/**
 * Class StyleEdit4AuthenticationController
 */
class StyleEdit4AuthenticationController extends AbstractStyleEditAuthenticationController
{
    /**
     * associative string[] for the http_build_query() function in `redirectToStyleEdit` - method
     * @var string[]
     */
    protected $urlParameters = [];
    
    
    /**
     * @param HttpContextReaderInterface     $httpContextReader
     * @param HttpResponseProcessorInterface $httpResponseProcessor
     * @param ContentViewInterface           $contentView
     *
     * @throws \Exception user has no access to styleedit
     */
    public function __construct(
        HttpContextReaderInterface $httpContextReader,
        HttpResponseProcessorInterface $httpResponseProcessor,
        ContentViewInterface $contentView
    ) {
        
        parent::__construct($httpContextReader, $httpResponseProcessor, $contentView);
        
        $this->urlParameters['language']    = $_SESSION['language_code'];
        $this->urlParameters['customer_id'] = $_SESSION['customer_id'];
        
        $shopUrl = GM_HTTP_SERVER.substr(DIR_WS_CATALOG,0,-1);
        
        $this->urlParameters['url'] = $shopUrl;
        
        if (!$this->userHasAccessToStyleEdit()) {
            throw new \Exception('The active user has no access to the styleedit');
        }
        
        if (isset($_GET['welcome'])) {
            $this->handleFirstTimeVisit();
        }
    
        if (!empty($_GET['startPageUrl'])) {
            $this->urlParameters['startPageUrl'] = $_GET['startPageUrl'];
        }
        
        $this->handleResponsiveFileManagerInstallation();
        
        $this->expertMode();
        
        $this->setStyleEditSession();
        
        $this->redirectToStyleEdit();
    }
    
    
    /**
     * @return bool
     */
    protected function userHasAccessToStyleEdit()
    {
        return isset($_SESSION['customer_id']);
    }
    
    
    /**
     *  Gets called first time a user accesses the styleedit
     *
     *  - Saves that the active user has opened styleedit for the first time.
     *  - Adds welcome parameter to the redirect url
     */
    protected function handleFirstTimeVisit(): void
    {
        include_once DIR_FS_CATALOG . 'GXModules/Gambio/StyleEdit/Api/Storage/StyleEditWelcomeStorage.php';
        
        $welcome_storage = new \Gambio\StyleEdit\Api\Storage\StyleEditWelcomeStorage;
        $welcome_storage->storeWelcomeStatusSeenForCustomer(new IdType($_SESSION['customer_id']));
        
        $this->urlParameters['welcome'] = 1;
    }
    
    
    protected function expertMode(): void
    {
        include_once DIR_FS_CATALOG . 'GXModules/Gambio/StyleEdit/Api/Storage/StyleEditExpertModeStorage.php';
        
        $expertModeStorage                 = new Gambio\StyleEdit\Api\Storage\StyleEditExpertModeStorage;
        $sessionIdType                     = new IdType($_SESSION['customer_id']);
        $this->urlParameters['expertMode'] = $expertModeStorage->expertModeActive($sessionIdType);
    }
    
    
    /**
     * Sets an encrypted value to the Session to check later if a user
     * has clicked the button in the template settings
     */
    protected function setStyleEditSession(): void
    {
        if (!isset($_SESSION['customers_status']['customers_status_id'])
            && $_SESSION['customers_status']['customers_status_id'] !== '0') {
            
            $this->redirectToFrontEnd();
        }
        
        $token = self::getTokenArray();
        
        $_SESSION['StyleEdit4Authentication'] = JWT::encode($token, self::getSecret());
    }
    
    
    /**
     * Redirects a user from the backend to the style edit.
     * @throws FileNotFoundException
     * @throws Exception
     */
    protected function redirectToStyleEdit(): void
    {
        if (!isset($this->urlParameters['welcome']) || $this->urlParameters['welcome'] !== 1) {
        
            $currentTheme = $this->getCurrentTheme();
        
            if ($currentTheme !== '') {
                $this->urlParameters['editing'] = $currentTheme;
            } else {
                $this->urlParameters['welcome'] = 1;
            }
        }
        
        $redirectUrl = get_href_link(HTTP_SERVER,
                                     HTTPS_CATALOG_SERVER,
                                     DIR_WS_CATALOG,
                                     ENABLE_SSL_CATALOG === 'true' || ENABLE_SSL_CATALOG === true,
                                     'GXModules/Gambio/StyleEdit/Build/index.php',
                                     http_build_query($this->urlParameters),
                                     'NONSSL',
                                     true,
                                     true,
                                     false,
                                     false,
                                     true);
        
        header('Location: ' . $redirectUrl);
    }
    
    
    
    /**
     * @return string[]
     */
    public static function getTokenArray(): array
    {
        return [
            'customer_id'         => trim($_SESSION['customer_id']),
            'customers_status_id' => trim($_SESSION['customers_status']['customers_status_id']),
            'customer_first_name' => trim($_SESSION['customer_first_name']),
            'customer_last_name'  => trim($_SESSION['customer_last_name']),
        ];
    }
    
    
    /**
     * @return string
     */
    public static function getSecret(): string
    {
        return MainFactory::create(StyleEdit4SecretStorage::class)->getSecret();
    }
    
    
    /**
     * Function calls themeControl to get the current theme id
     *
     * @return string current theme id
     * @throws FileNotFoundException
     */
    protected function getCurrentTheme(): string
    {
        $currentTheme       = StaticGXCoreLoader::getThemeControl()->getCurrentTheme();
        $isPreviewIdPattern = '/(^.*)\_preview$/';
        
        if (preg_match($isPreviewIdPattern, $currentTheme) === 1) {
            
            $currentTheme = preg_replace($isPreviewIdPattern, '$1', $currentTheme);
        }
        
        DependencyInjector::inject();
        
        if (!Validator::for($currentTheme)->canBeOpenedInStyleEdit4()) {
            
            return '';
        }
        
        return $currentTheme;
    }
    
    
    protected function handleResponsiveFileManagerInstallation(): void
    {
        if (!file_exists(DIR_FS_CATALOG . 'ResponsiveFilemanager')) {
            
            $this->urlParameters['noImageUpload'] = '1';
        }
    }
}
