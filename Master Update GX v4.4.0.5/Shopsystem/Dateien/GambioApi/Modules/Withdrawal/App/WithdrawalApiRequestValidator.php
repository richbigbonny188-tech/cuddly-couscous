<?php
/* --------------------------------------------------------------
   WithdrawalApiRequestValidator.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\Withdrawal\App;

/**
 * Class WithdrawalApiRequestValidator
 *
 * @package Gambio\Api\Modules\Withdrawal\App
 */
class WithdrawalApiRequestValidator
{
    /**
     * @param array $parsedBody
     *
     * @return string[][]
     */
    public function validatePostRequestBody(array $parsedBody): array
    {
        $mandatory = [
            'order.id',
            'order.creationDate',
            'order.deliveryDate',
            'customer.id',
            'customer.gender',
            'customer.firstName',
            'customer.lastName',
            'customer.address.street',
            'customer.address.postcode',
            'customer.address.city',
            'customer.address.country',
            'customer.email',
            'date',
            'content',
            'createdByAdmin',
        ];
        
        return $this->checkAttributes($parsedBody, $mandatory);
    }
    
    
    /**
     * @param array $parsedBody
     *
     * @return string[][]
     */
    public function validatePutRequestBody(array $parsedBody): array
    {
        $mandatory = [
            'order.id',
        ];
        
        return $this->checkAttributes($parsedBody, $mandatory);
    }
    
    
    /**
     * @param array $parsedBody
     * @param array $mandatory
     *
     * @return array
     */
    private function checkAttributes(array $parsedBody, array $mandatory): array
    {
        $errors = [];
        
        foreach ($parsedBody as $index => $withdrawalDetails) {
            $providedAttributes = $this->getArrayKeysRecursive($withdrawalDetails);
            $missingAttributes  = array_diff($mandatory, $providedAttributes);
            
            if (count($missingAttributes) > 0) {
                $errors[$index] = [];
                foreach ($missingAttributes as $missingAttribute) {
                    $errors[$index][] = 'Attribute "' . $missingAttribute . '" is missing.';
                }
            }
        }
        
        return $errors;
    }
    
    
    /**
     * @param array  $array
     * @param string $connectionString
     *
     * @return array
     */
    private function getArrayKeysRecursive(array $array, string $connectionString = '.'): array
    {
        $keys = [];
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $keys[] = $key;
                continue;
            }
            
            foreach ($this->getArrayKeysRecursive($value) as $subKey) {
                $keys[] = $key . $connectionString . $subKey;
            }
        }
        
        return $keys;
    }
}