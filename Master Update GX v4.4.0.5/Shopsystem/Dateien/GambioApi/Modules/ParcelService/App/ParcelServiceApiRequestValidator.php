<?php
/* --------------------------------------------------------------
   ParcelServiceApiRequestValidator.php 2020-08-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\ParcelService\App;

/**
 * Class ParcelServiceApiRequestValidator
 *
 * @package Gambio\Api\Modules\ParcelService\App
 */
class ParcelServiceApiRequestValidator
{
    /**
     * @param array $parsedBody
     *
     * @return string[][]
     */
    public function validatePostRequestBody(array $parsedBody): array
    {
        $mandatory            = ['name', 'isDefault', 'descriptions'];
        $descriptionMandatory = ['languageCode', 'url', 'comment'];
        
        return $this->checkAttributes($parsedBody, $mandatory, $descriptionMandatory);
    }
    
    
    /**
     * @param array $parsedBody
     *
     * @return string[][]
     */
    public function validatePutRequestBody(array $parsedBody): array
    {
        $mandatory            = ['id', 'name', 'isDefault', 'descriptions'];
        $descriptionMandatory = ['languageCode', 'url', 'comment'];
        
        return $this->checkAttributes($parsedBody, $mandatory, $descriptionMandatory);
    }
    
    
    /**
     * @param array $parsedBody
     * @param array $mandatory
     * @param array $descriptionMandatory
     *
     * @return array
     */
    private function checkAttributes(array $parsedBody, array $mandatory, array $descriptionMandatory): array
    {
        $errors = [];
        
        foreach ($parsedBody as $reference => $parcelServiceDetails) {
            $providedAttributes = array_keys($parcelServiceDetails);
            $missingAttributes  = array_diff($mandatory, $providedAttributes);
            
            if (count($missingAttributes) > 0) {
                $errors[$reference] = [];
                foreach ($missingAttributes as $missingAttribute) {
                    $errors[$reference][] = 'Attribute "' . $missingAttribute . '" is missing.';
                }
            }
            
            if (array_key_exists('descriptions', $parcelServiceDetails)) {
                $errors = $this->checkDescriptionAttributes($descriptionMandatory,
                                                            $parcelServiceDetails,
                                                            $errors,
                                                            $reference);
            }
        }
        
        return $errors;
    }
    
    
    /**
     * @param array      $descriptionMandatory
     * @param array      $parcelServiceDetails
     * @param array      $errors
     * @param string|int $reference
     *
     * @return array
     */
    private function checkDescriptionAttributes(
        array $descriptionMandatory,
        array $parcelServiceDetails,
        array $errors,
        $reference
    ): array {
        foreach ($parcelServiceDetails['descriptions'] as $key => $description) {
            $providedDescriptionAttributes = array_keys($description);
            $missingDescriptionAttributes  = array_diff($descriptionMandatory, $providedDescriptionAttributes);
            
            if (count($missingDescriptionAttributes) > 0) {
                $errors[$reference] = $errors[$reference] ?? [];
                foreach ($missingDescriptionAttributes as $missingAttribute) {
                    $errors[$reference][] = 'Attribute "descriptions.' . $key . '.' . $missingAttribute
                                            . '" is missing.';
                }
            }
        }
        
        return $errors;
    }
}