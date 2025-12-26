<?php
/* --------------------------------------------------------------
   ApiKeysController.inc.php 2019-08-09
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class ApiKeysController extends AdminHttpViewController
{
    /** @var \LanguageTextManager */
    protected $text;
    
    
    public function proceed(HttpContextInterface $httpContext)
    {
        AdminMenuControl::connect_with_page('admin.php?do=AdminAccess');
        $this->text = MainFactory::create('LanguageTextManager', 'apikeys', $_SESSION['languages_id']);
        parent::proceed($httpContext);
    }
    
    
    public function actionDefault()
    {
        $customerReadService = StaticGXCoreLoader::getService('CustomerRead');
        $admins              = $customerReadService->filterCustomers(['customers_status' => DEFAULT_CUSTOMERS_STATUS_ID_ADMIN]);
        
        $title          = new NonEmptyStringType($this->text->get_text('configuration_heading'));
        $template       = new ExistingFile(new NonEmptyStringType(DIR_FS_ADMIN . '/html/content/apikeys.html'));
        $data           = [
            'pageToken'                  => $_SESSION['coo_page_token']->generate_token(),
            'create_key_form_action'     => xtc_href_link('admin.php', 'do=ApiKeys/CreateToken'),
            'delete_expired_form_action' => xtc_href_link('admin.php', 'do=ApiKeys/DeleteExpiredTokens'),
            'admins'                     => $admins,
            'tokens'                     => $this->getTokenList(),
        ];
        $dataCollection = MainFactory::create('KeyValueCollection', $data);
        
        $assets = MainFactory::create('AssetCollection');
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $template,
                                   $dataCollection,
                                   $assets,
                                   $this->_createContentNavigation('manageApiKeys'));
    }
    
    
    public function actionCreateToken()
    {
        $this->_validatePageToken();
        
        $customerId = MainFactory::create('IdType', (int)$this->_getPostData('customerId'));
        /** @var \CustomerReadService $customerReadService */
        $customerReadService = StaticGXCoreLoader::getService('CustomerRead');
        $customer            = $customerReadService->getCustomerById($customerId);
        
        $expirationNumber = (int)$this->_getPostData('expiration_number');
        $expirationFactor = (int)$this->_getPostData('expiration_factor');
        $expirationOffset = $expirationNumber * $expirationFactor;
        if ($expirationOffset <= 0) {
            throw new InvalidArgumentException('Expiration invalid');
        }
        
        $payloadData = [
            'iss'         => HTTPS_CATALOG_SERVER,
            'exp'         => time() + $expirationOffset,
            'iat'         => time(),
            'customer_id' => $customerId->asInt(),
        ];
        
        $headers = MainFactory::create('KeyValueCollection', ['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = MainFactory::create('KeyValueCollection', $payloadData);
        $secret  = MainFactory::create('NonEmptyStringType', JsonWebTokenSecretProvider::getSecret());
        
        /** @var \JsonWebToken $jwt */
        $jwt = MainFactory::create('JsonWebToken', $headers, $payload);
        $jwt->setIncludeSecretInSignaturePayload(false);
        $jwt->setUseRawHmacForSignature(true);
        $jwt->setSecret($secret);
        $token = (string)$jwt;
        
        $this->storeToken($payloadData, $token);
        
        $GLOBALS['messageStack']->add_session($this->text->get_text('token_created'), 'info');
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php', 'do=ApiKeys'));
    }
    
    
    public function actionDeleteExpiredTokens()
    {
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->where('exp < ' . time())->delete('api_tokens');
        
        $GLOBALS['messageStack']->add_session($this->text->get_text('expired_tokens_deleted'), 'info');
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php', 'do=ApiKeys'));
    }
    
    
    protected function storeToken($payloadData, $token)
    {
        $row       = array_merge($payloadData, ['token' => $token]);
        $dbColumns = ['iss', 'exp', 'iat', 'customer_id', 'token'];
        $row       = array_filter($row,
            function ($colName) use ($dbColumns) {
                return in_array($colName, $dbColumns, true);
            },
                                  ARRAY_FILTER_USE_KEY);
        $db        = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->insert('api_tokens', $row);
    }
    
    
    protected function getTokenList()
    {
        $db     = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $tokens = $db->order_by('api_tokens_id', 'desc')->get('api_tokens')->result_array();
        /** @var \CustomerReadService $customerReadService */
        $customerReadService = StaticGXCoreLoader::getService('CustomerRead');
        foreach ($tokens as $index => $token) {
            $tokens[$index]['exp_formatted'] = (new DateTime('@' . $token['exp']))->format(PHP_DATE_TIME_FORMAT);
            $tokens[$index]['iat_formatted'] = (new DateTime('@' . $token['iat']))->format(PHP_DATE_TIME_FORMAT);
            $tokens[$index]['valid']         = (int)$tokens[$index]['exp'] > time();
            try {
                $customer                        = $customerReadService->getCustomerById(new IdType((int)$token['customer_id']));
                $tokens[$index]['customer_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
            } catch (InvalidArgumentException $e) {
                $tokens[$index]['customer_name'] = $this->text->get_text('deleted_customer');
                $tokens[$index]['valid']         = false;
            }
        }
        
        return $tokens;
    }
    
    
    /**
     * Creates the content navigation object for the admin access pages.
     *
     * @param string $currentSection Defines the current navigation item.
     *
     * @return \ContentNavigationCollection
     */
    protected function _createContentNavigation($currentSection = '')
    {
        $subNavigationItems = [
            'manageAdmins'  => [
                'title' => new StringType($this->text->get_text('sub_navigation_admins')),
                'url'   => new StringType('admin.php?do=AdminAccess/manageAdmins'),
            ],
            'manageRoles'   => [
                'title' => new StringType($this->text->get_text('sub_navigation_roles')),
                'url'   => new StringType('admin.php?do=AdminAccess/manageRoles'),
            ],
            'manageApiKeys' => [
                'title' => new StringType($this->text->get_text('sub_navigation_api_keys')),
                'url'   => new StringType('admin.php?do=ApiKeys'),
            ],
        ];
        
        $contentNavigation = MainFactory::create('ContentNavigationCollection', []);
        
        foreach ($subNavigationItems as $itemName => $subNavigationItem) {
            $contentNavigation->add($subNavigationItem['title'],
                                    $subNavigationItem['url'],
                                    new BoolType($currentSection === $itemName));
        }
        
        return $contentNavigation;
    }
}
