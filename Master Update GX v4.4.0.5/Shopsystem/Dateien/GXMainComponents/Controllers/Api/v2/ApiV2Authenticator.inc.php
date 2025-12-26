<?php
/* --------------------------------------------------------------
   ApiV2Authenticator.inc.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\PermissionService;
use Gambio\Core\AdminAccess\Role\PermissionAction;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class ApiV2Authenticator
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Response
     */
    protected $response;
    
    /**
     * @var string
     */
    protected $method;
    
    /**
     * @var array
     */
    protected $uri;
    
    
    /**
     * ApiV2Authenticator constructor.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $uri
     */
    public function __construct(Request $request, Response $response, array $uri)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->method   = strtoupper($request->getMethod());
        $this->uri      = $uri;
    }
    
    
    /**
     * Authorize request with HTTP Basic Authorization
     *
     * Call this method in every API operation that needs to be authorized with the HTTP Basic
     * Authorization technique.
     *
     * @link http://php.net/manual/en/features.http-auth.php
     *
     * Not available to child-controllers (private method).
     *
     * @param string $controllerName Name of the parent controller for this api call.
     *
     * @throws HttpApiV2Exception If request does not provide the "Authorization" header or if the
     *                            credentials are invalid.
     *
     * @throws InvalidArgumentException If the username or password values are invalid.
     * @throws JsonWebTokenException If a JWT supplied via “Authorization: Bearer” is found to be invalid
     */
    public function authorize($controllerName)
    {
        if (!empty($_SERVER['HTTP_AUTHORIZATION']) && strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer') !== false) {
            $this->authorizeBearer($controllerName);
        } else {
            $this->authorizeBasicAuth($controllerName);
        }
    }
    
    
    /**
     * Authorizes requests by Basic Auth.
     *
     * @param $controllerName
     *
     * @throws HttpApiV2Exception
     */
    protected function authorizeBasicAuth($controllerName)
    {
        if (empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW'])
            && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']] = explode(':',
                                                                           base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'],
                                                                                                6)));
        } elseif (empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW'])
                  && !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']] = explode(':',
                                                                           base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'],
                                                                                                6)));
        }
        
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->response = $this->response->withHeader('WWW-Authenticate', 'Basic realm="Gambio GX3 APIv2 Login"');
            throw new HttpApiV2Exception('Unauthorized', 401);
        }
        
        $authService = StaticGXCoreLoader::getService('Auth');
        $credentials = MainFactory::create('UsernamePasswordCredentials',
                                           new NonEmptyStringType($_SERVER['PHP_AUTH_USER']),
                                           new StringType($_SERVER['PHP_AUTH_PW']));
        
        $db      = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $query   = $db->get_where('customers',
                                  [
                                      'customers_email_address' => $_SERVER['PHP_AUTH_USER'],
                                      'customers_status'        => '0'
                                  ]);
        $isAdmin = $query->num_rows() === 1;
        $user    = $query->row_array();
        
        if (!$isAdmin || !$authService->authUser($credentials)) {
            throw new HttpApiV2Exception('Invalid Credentials', 401);
        }
        
        /** @var PermissionService $adminAccessService */
        $adminAccessService = LegacyDependencyContainer::getInstance()->get(PermissionService::class);
        
        $controllerName = substr($controllerName, 0, -10);
        $hasPermission  = $adminAccessService->checkAdminPermission((int)$user['customers_id'],
                                                                    PermissionAction::READ,
                                                                    GroupItem::CONTROLLER_TYPE,
                                                                    'DefaultApiV2');
        
        if (($this->request->isPost() && $this->uri[count($this->uri) - 1] !== 'search')
            || $this->request->isPut()
            || $this->request->isPatch()) {
            $hasPermission &= $adminAccessService->checkAdminPermission((int)$user['customers_id'],
                                                                        PermissionAction::WRITE,
                                                                        GroupItem::CONTROLLER_TYPE,
                                                                        $controllerName);
        } elseif ($this->request->isDelete()) {
            $hasPermission &= $adminAccessService->checkAdminPermission((int)$user['customers_id'],
                                                                        PermissionAction::DELETE,
                                                                        GroupItem::CONTROLLER_TYPE,
                                                                        $controllerName);
        } else {
            $hasPermission &= $adminAccessService->checkAdminPermission((int)$user['customers_id'],
                                                                        PermissionAction::READ,
                                                                        GroupItem::CONTROLLER_TYPE,
                                                                        $controllerName);
        }
        
        if (!$hasPermission) {
            throw new HttpApiV2Exception('Forbidden - No Permissions', 403);
        }
        // authorization valid
    }
    
    
    /**
     * Authorize requests with JWT header.
     *
     * @param $controllerName
     *
     * @throws HttpApiV2Exception
     * @throws JsonWebTokenException
     */
    protected function authorizeBearer($controllerName)
    {
        [$bearer, $token] = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
        if ($bearer !== 'Bearer' || empty($token)) {
            throw new HttpApiV2Exception('Invalid syntax in Authorization header');
        }
        
        $secret      = MainFactory::create('NonEmptyStringType', JsonWebTokenSecretProvider::getSecret());
        $tokenString = MainFactory::create('NonEmptyStringType', $token);
        $parsedToken = JsonWebTokenParser::parseToken($tokenString, $secret);
        
        /** @var PermissionService $adminAccessService */
        $adminAccessService = LegacyDependencyContainer::getInstance()->get(PermissionService::class);
        
        $customerId    = (int)$parsedToken->getPayload()->getValue('customer_id');
        $issTimestamp  = abs((int)$parsedToken->getPayload()->getValue('iat'));
        $expTimestamp  = abs((int)$parsedToken->getPayload()->getValue('exp'));
        $currentTime   = time();
        $hasPermission = $adminAccessService->checkAdminPermission($customerId,
                                                                   PermissionAction::READ,
                                                                   GroupItem::CONTROLLER_TYPE,
                                                                   'DefaultApiV2');
        
        if (($this->request->isPost() && $this->uri[count($this->uri) - 1] !== 'search')
            || $this->request->isPut()
            || $this->request->isPatch()) {
            $hasPermission &= $adminAccessService->checkAdminPermission($customerId,
                                                                        PermissionAction::WRITE,
                                                                        GroupItem::CONTROLLER_TYPE,
                                                                        $controllerName);
        } elseif ($this->request->isDelete()) {
            $hasPermission &= $adminAccessService->checkAdminPermission($customerId,
                                                                        PermissionAction::DELETE,
                                                                        GroupItem::CONTROLLER_TYPE,
                                                                        $controllerName);
        } else {
            $hasPermission &= $adminAccessService->checkAdminPermission($customerId,
                                                                        PermissionAction::READ,
                                                                        GroupItem::CONTROLLER_TYPE,
                                                                        $controllerName);
        }
        if ($issTimestamp === 0 || $expTimestamp === 0) {
            throw new JsonWebTokenException('invalid exp/iat in token');
        }
        $timeValid = $issTimestamp <= $currentTime && $currentTime <= $expTimestamp;
        
        if (!$hasPermission || !$timeValid) {
            throw new JsonWebTokenException('permission denied');
        }
        // authorization valid
    }
}
