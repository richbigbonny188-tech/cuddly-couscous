<?php
/* --------------------------------------------------------------
   CustomerConfigurationProvider.inc.php 2021-01-21
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class CustomerConfigurationProvider
 */
class CustomerConfigurationProvider
{
    /**
     * @var \CI_DB_query_builder
     */
    private $db;
    
    /**
     * @var \LanguageTextManager
     */
    private $languageTextManager;
    
    /**
     * @var array
     */
    private static $minLength = [
        'firstName'   => 'configuration/ENTRY_FIRST_NAME_MIN_LENGTH',
        'lastName'    => 'configuration/ENTRY_LAST_NAME_MIN_LENGTH',
        'dateOfBirth' => 'configuration/ENTRY_DOB_MIN_LENGTH',
        'dob'         => 'configuration/ENTRY_DOB_MIN_LENGTH',
        'email'       => 'configuration/ENTRY_EMAIL_ADDRESS_MIN_LENGTH',
        'street'      => 'configuration/ENTRY_STREET_ADDRESS_MIN_LENGTH',
        'houseNumber' => 'configuration/ENTRY_HOUSENUMBER_MIN_LENGTH',
        'company'     => 'configuration/ENTRY_COMPANY_MIN_LENGTH',
        'postcode'    => 'configuration/ENTRY_POSTCODE_MIN_LENGTH',
        'city'        => 'configuration/ENTRY_CITY_MIN_LENGTH',
        'countryZone' => 'configuration/ENTRY_STATE_MIN_LENGTH',
        'suburb'      => 'configuration/ENTRY_STATE_MIN_LENGTH',
        'state'       => 'configuration/ENTRY_STATE_MIN_LENGTH',
        'telephone'   => 'configuration/ENTRY_TELEPHONE_MIN_LENGTH',
        'password'    => 'configuration/ENTRY_PASSWORD_MIN_LENGTH'
    ];
    
    
    /**
     * @var array
     */
    private static $display = [
        'gender'      => 'configuration/ACCOUNT_GENDER',
        'dob'         => 'configuration/ACCOUNT_DOB',
        'dateOfBirth' => 'configuration/ACCOUNT_DOB',
        'company'     => 'configuration/ACCOUNT_COMPANY',
        'state'       => 'configuration/ACCOUNT_STATE',
        'telephone'   => 'configuration/ACCOUNT_TELEPHONE',
        'fax'         => 'configuration/ACCOUNT_FAX',
        'suburb'      => 'configuration/ACCOUNT_SUBURB',
        'countryZone' => 'configuration/ACCOUNT_STATE'
    ];
    
    
    /**
     * @var array
     */
    private static $errorMessages = [
        'gender'               => 'ENTRY_GENDER_ERROR',
        'firstName'            => 'ENTRY_FIRST_NAME_ERROR',
        'lastName'             => 'ENTRY_LAST_NAME_ERROR',
        'dob'                  => 'ENTRY_DATE_OF_BIRTH_ERROR',
        'dateOfBirth'          => 'ENTRY_DATE_OF_BIRTH_ERROR',
        'company'              => 'ENTRY_COMPANY_ERROR',
        'vatNumber'            => 'ENTRY_VAT_ERROR',
        'email'                => 'ENTRY_EMAIL_ADDRESS_ERROR',
        'emailAddressCheck'    => 'ENTRY_EMAIL_ADDRESS_CHECK_ERROR',
        'emailConfirmation'    => 'ENTRY_EMAIL_ADDRESS_CONFIRM_DIFFERENT_ERROR',
        'emailExists'          => 'ENTRY_EMAIL_ADDRESS_ERROR_EXISTS',
        'street'               => 'ENTRY_STREET_ADDRESS_ERROR',
        'houseNumber'          => 'ENTRY_HOUSENUMBER_ERROR',
        'postcode'             => 'ENTRY_POST_CODE_ERROR',
        'city'                 => 'ENTRY_CITY_ERROR',
        'country'              => 'ENTRY_COUNTRY_ERROR',
        'countryZone'          => 'ENTRY_STATE_ERROR',
        'countryZoneSelection' => 'ENTRY_STATE_ERROR_SELECT',
        'telephone'            => 'ENTRY_TELEPHONE_NUMBER_ERROR',
        'password'             => 'ENTRY_PASSWORD_ERROR',
        'passwordMismatch'     => 'ENTRY_PASSWORD_ERROR_NOT_MATCHING',
        'privacy'              => 'ENTRY_PRIVACY_ERROR',
        'invalidInput'         => 'ENTRY_MAX_LENGTH_ERROR'
    ];
    
    /**
     * @var array
     */
    private static $configuration = [
        'acceptPrivacy'          => [
            'table' => 'gx_configurations',
            'keys'  => [
                'gm_configuration/GM_SHOW_PRIVACY_REGISTRATION',
                'gm_configuration/PRIVACY_CHECKBOX_REGISTRATION'
            ],
        ],
        'optionalNames'          => [
            'table' => 'gx_configurations',
            'keys'  => [
                'configuration/ACCOUNT_NAMES_OPTIONAL'
            ]
        ],
        'splitStreetInformation' => [
            'table' => 'gx_configurations',
            'keys'  => [
                'configuration/ACCOUNT_SPLIT_STREET_INFORMATION'
            ]
        ],
        'moveOnlyIfNoGuest'      => [
            'table' => 'gx_configurations',
            'keys'  => [
                'configuration/MOVE_ONLY_IF_NO_GUEST'
            ]
        ],
        'genderMandatory'        => [
            'table' => 'gx_configurations',
            'keys'  => [
                'configuration/GENDER_MANDATORY'
            ]
        ]
    ];
    
    
    private static $defaultStatusId = [
        'customer' => 'configuration/DEFAULT_CUSTOMERS_STATUS_ID',
        'guest'    => 'configuration/DEFAULT_CUSTOMERS_STATUS_ID_GUEST'
    ];
    
    /**
     * @var array
     */
    private static $configurationTable = [
        'table' => 'gx_configurations',
        'key'   => 'key',
        'value' => 'value'
    ];
    
    /**
     * @var array
     */
    private static $gmConfigurationTable = [
        'table' => 'gx_configurations',
        'key'   => 'key',
        'value' => 'value',
    ];
    
    
    /**
     * CustomerConfigurationProvider constructor.
     *
     * @param \CI_DB_query_builder $db                  Database access, required to fetch configurations.
     * @param \LanguageTextManager $languageTextManager Text manager to fetch error messages.
     */
    public function __construct(CI_DB_query_builder $db, LanguageTextManager $languageTextManager)
    {
        $this->db                  = $db;
        $this->languageTextManager = $languageTextManager;
    }
    
    
    /**
     * Minimum length of provided configuration field.
     * Allowed fields are 'firstName', 'lastName', 'dateOfBirth', 'dob', 'email', 'street', 'houseNumber', 'company',
     * 'postcode', 'city', 'countryZone', 'suburb', 'state', 'telephone', 'password'.
     *
     * @param \StringType $configField Determines for which configuration field the minimum length should be provided.
     *
     * @return int Minimum length of provided configuration field.
     */
    public function minLength(StringType $configField)
    {
        $this->checkIfFieldExists($configField, static::$minLength);
        
        $result   = $this->db->select(static::$configurationTable['value'])
            ->from(static::$configurationTable['table'])
            ->where(static::$configurationTable['key'], static::$minLength[$configField->asString()])
            ->get()
            ->row_array();
        $rowValue = array_key_exists(static::$configurationTable['value'],
                                     $result) ? $result[static::$configurationTable['value']] : 0;
        
        return (int)$rowValue;
    }
    
    
    /**
     * Should the provided configuration field be displayed?
     * Allowed fields are 'gender', 'dob', 'dateOfBirth', 'company', 'state', 'telephone', 'fax', 'suburb'.
     *
     * @param \StringType $configField Determines for which configuration field the display check should be performed.
     *
     * @return bool True if provided configuration field should be displayed and false otherwise.
     */
    public function display(StringType $configField)
    {
        $this->checkIfFieldExists($configField, static::$display);
        
        $result   = $this->db->select(static::$configurationTable['value'])
            ->from(static::$configurationTable['table'])
            ->where(static::$configurationTable['key'], static::$display[$configField->asString()])
            ->get()
            ->row_array();
        $rowValue = array_key_exists(static::$configurationTable['value'],
                                     $result) ? $result[static::$configurationTable['value']] : 'false';
        
        return $rowValue === 'true';
    }
    
    
    /**
     * Error message for provided configuration field.
     * Allowed fields are 'gender', 'firstName', 'lastName', 'dateOfBirth', 'company', 'vatNumber', 'email',
     * 'emailAddressCheck', 'emailConfirmation', 'emailExists', 'street', 'houseNumber', 'postcode', 'city', 'country',
     * 'countryZone', 'countryZoneSelection', 'telephone', 'password', 'passwordMismatch', 'privacy', 'invalidInput'.
     *
     * @param \StringType $configField Determines for which configuration field the error message should be provided.
     *
     * @return string Error message of provided configuration field.
     */
    public function errorMessage(StringType $configField)
    {
        $message = $this->languageTextManager->get_text(static::$errorMessages[$configField->asString()], 'general');
        
        try {
            $minLength = $this->minLength($configField);
            
            return sprintf($message, $minLength);
        } catch (InvalidArgumentException $exception) {
            return $message;
        }
    }
    
    
    /**
     * Checks if given configuration is enabled.
     * Allowed fields are 'acceptPrivacy', 'optionalNames', 'splitStreetInformation'.
     *
     * @param \StringType $configField Determines for which configuration field should be checked.
     *
     * @return bool True if configuration is enabled and false otherwise.
     */
    public function configuration(StringType $configField)
    {
        $this->checkIfFieldExists($configField, static::$configuration);
        
        $configField = static::$configuration[$configField->asString()];
        $table       = $configField['table'];
        $configKey   = $table
                       === static::$configurationTable['table'] ? static::$configurationTable['key'] : static::$gmConfigurationTable['key'];
        $configValue = $table
                       === static::$configurationTable['table'] ? static::$configurationTable['value'] : static::$gmConfigurationTable['value'];
        
        $result = true;
        foreach ($configField['keys'] as $key) {
            $queryResult = $this->db->select($configValue)->from($table)->where($configKey, $key)->get()->row_array();
            $result      &= is_array($queryResult)
                            && array_key_exists($configValue,
                                                $queryResult) ? $this->isConfigurationValueTrue($queryResult[$configValue]) : false;
        }
        
        return (bool)$result;
    }
    
    /**
     * Checks if the value is one of the different presentations of true from the different config table sources.
     * @param $configValue The tested config value
     *
     * @return bool True, if the value is meant to represent true.
     */
    protected function isConfigurationValueTrue($configValue)
    {
        return $configValue === 'true' || $configValue === '1' || $configValue === true;
    }
    
    /**
     * Provides the default status id for customers and guests.
     * Allowed fields are 'customer' and 'guest'.
     *
     * @param \StringType $configField Either 'guest' or 'customer'.
     *
     * @return int Default status id of given config field.
     */
    public function defaultStatusId(StringType $configField)
    {
        $this->checkIfFieldExists($configField, static::$defaultStatusId);
        
        $result = $this->db->select(static::$configurationTable['value'])
            ->from(static::$configurationTable['table'])
            ->where(static::$configurationTable['key'],
                    static::$defaultStatusId[$configField->asString()])
            ->get()
            ->row_array();
        
        return array_key_exists(static::$configurationTable['value'],
                                $result) ? (int)$result[static::$configurationTable['value']] : 0;
    }
    
    
    /**
     * Checks if given configuration field exists.
     *
     * @param \StringType $configField Expected configuration field.
     * @param array       $fields      Allowed configuration fields.
     *
     * @return $this|\CustomerConfigurationProvider Same instance for chained method calls.
     * @throws \InvalidArgumentException If provided config field do not exist.
     */
    protected function checkIfFieldExists(StringType $configField, array $fields)
    {
        if (!array_key_exists($configField->asString(), $fields)) {
            throw new \InvalidArgumentException('Provided configuration field "' . $configField->asString()
                                                . '" does not exist for display. Allowed fields are "' . implode('", "',
                                                                                                                 array_keys($fields))
                                                . '".');
        }
        
        return $this;
    }
}