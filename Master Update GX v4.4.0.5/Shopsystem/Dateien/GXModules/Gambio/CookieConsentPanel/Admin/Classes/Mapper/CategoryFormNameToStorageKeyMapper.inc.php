<?php
/* --------------------------------------------------------------
  CategoryFormNameToStorageKeyMapper.php 2020-01-14
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Class CategoryFormNameToStorageKeyMapper
 */
class CategoryFormNameToStorageKeyMapper
{
    protected const LANGUAGE_CODE_REGEX = '#(title|description)\_(\w+)$#';
    
    protected $keyToFormMap = [
        'category_1_description' => 'label_cpc_category_01_desc',
        'category_1_title'       => 'label_cpc_category_01_text',
        'category_2_description' => 'label_cpc_category_02_desc',
        'category_2_title'       => 'label_cpc_category_02_text',
        'category_3_description' => 'label_cpc_category_03_desc',
        'category_3_title'       => 'label_cpc_category_03_text',
        'category_4_description' => 'label_cpc_category_04_desc',
        'category_4_title'       => 'label_cpc_category_04_text',
        'category_5_description' => 'label_cpc_category_05_desc',
        'category_5_title'       => 'label_cpc_category_05_text',
    ];
    
    
    /**
     * @param array $formData
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function formNamesToDatabaseKeys(array $formData): array
    {
        $result = [];
        
        foreach ($formData as $inputName => $value) {
            
            if (preg_match(self::LANGUAGE_CODE_REGEX, $inputName, $matches) !== 1) {
                
                throw new InvalidArgumentException('Parsed form element names can not be mapped. Failed at '
                                                   . $inputName);
            }
            
            $key = preg_replace(self::LANGUAGE_CODE_REGEX, '$1', $inputName);
            
            if (!isset($result[$this->keyToFormMap[$key]])) {
                
                $result[$this->keyToFormMap[$key]] = new stdClass;
            }
    
            $result[$this->keyToFormMap[$key]]->{end($matches)} = $value;
        }
        
        return $result;
    }
}