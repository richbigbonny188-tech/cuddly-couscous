<?php
/* --------------------------------------------------------------
   StyleEditorAuthenticator.php 2020-08-05
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

namespace Gambio\StyleEdit\Api;

use Exception, \Firebase\JWT\JWT, Gambio\StyleEdit\Core\TranslatedException, CustomerServiceFactory, IdType, MainFactory, StaticGXCoreLoader, StyleEdit4AuthenticationController;
use Gambio\StyleEdit\Adapters\Interfaces\JwtAdapterInterface;
use GXModules\Gambio\StyleEdit\Adapters\Interfaces\CustomerServiceAdapterInterface;

/**
 * Class StyleEditAuthenticator
 */
class StyleEditAuthenticator
{
    protected const BEARER_PATTERN = '/^Bearer\s/';
    /**
     * @var JwtAdapterInterface
     */
    protected $jwtAdapter;

    /**
     * @var CustomerServiceAdapterInterface
     */
    protected $customerServiceAdapter;

    
    /**
     * StyleEditAuthenticator constructor.
     *
     * @param JwtAdapterInterface             $jwtAdapter
     * @param CustomerServiceAdapterInterface $customerServiceAdapter
     */
    public function __construct(
        JwtAdapterInterface $jwtAdapter,
        CustomerServiceAdapterInterface $customerServiceAdapter
    ) {
        $this->jwtAdapter = $jwtAdapter;
        $this->customerServiceAdapter = $customerServiceAdapter;
    }


    /**
     * @return bool
     * @throws TranslatedException
     * @throws Exception
     */
    public function authorize()
    {
        
        $exception = new TranslatedException('UNAUTHORIZED', [], 403);
        
        $authToken = $this->getBearer();
        
        if ($authToken === '') {
            
            throw $exception;
        }
        
        $authToken = preg_replace(self::BEARER_PATTERN, '', $authToken);
        
        $token = (array)JWT::decode($authToken, $this->jwtAdapter->getShoppingSecret(), ['HS256']);
        
        if (!(is_array($token) && count($token)) || !$this->jwtIsValid($token)) {
            
            throw $exception;
        }
        
        return true;
    }
    
    
    /**
     * @param array $token
     *
     * @return bool
     */
    public function jwtIsValid(array $token): bool
    {
        $customerId = $token['customer_id'];
        
        try {
            $customer = $this->customerServiceAdapter->getCustomerById((int)$customerId);
        } catch (Exception $exception) {
            return false;
        }
        
        $firstName = (string)$customer->getFirstname();
        $lastName  = (string)$customer->getLastname();
        $statusId  = (int)$customer->getStatusId();
        
        $firstNameToken = (string)$token['customer_first_name'];
        $lastNameToken  = (string)$token['customer_last_name'];
        $statusIdToken  = (int)$token['customers_status_id'];
        
        return $firstName === $firstNameToken && $lastName === $lastNameToken && $statusId === $statusIdToken;
    }
    
    
    /**
     * @return string
     */
    protected function getBearer(): string
    {
        $tokenIndexes = ['HTTP_X_AUTH_TOKEN', 'HTTP_AUTHORIZATION'];
        
        foreach ($tokenIndexes as $index) {
            
            if (isset($_SERVER[$index]) && is_string($_SERVER[$index])
                && preg_match(self::BEARER_PATTERN, $_SERVER[$index])) {
                
                return $_SERVER[$index];
            }
        }
        
        return '';
    }
}