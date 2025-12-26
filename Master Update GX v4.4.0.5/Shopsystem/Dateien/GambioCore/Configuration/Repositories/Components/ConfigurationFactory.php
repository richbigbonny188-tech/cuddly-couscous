<?php
/* --------------------------------------------------------------
   ConfigurationFactory.php 2020-07-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Configuration\Repositories\Components;

use Gambio\Core\Configuration\Models\Read\Collections\ConfigurationGroup;
use Gambio\Core\Configuration\Models\Read\Collections\Options;
use Gambio\Core\Configuration\Models\Read\Configuration as ReadConfiguration;
use Gambio\Core\Configuration\Models\Read\ConfigurationGroupItem;
use Gambio\Core\Configuration\Models\Read\ConfigurationGroupItemLabel;
use Gambio\Core\Configuration\Models\Read\ConfigurationGroupItemProperties;
use Gambio\Core\Configuration\Models\Read\LanguageValues;
use Gambio\Core\Configuration\Types\AccountTemplate;
use Gambio\Core\Configuration\Types\AccountType;
use Gambio\Core\Configuration\Types\AuthMode;
use Gambio\Core\Configuration\Types\CaptchaType;
use Gambio\Core\Configuration\Types\Checkbox;
use Gambio\Core\Configuration\Types\ChfEurUsdCurrencies;
use Gambio\Core\Configuration\Types\CodFeeTransferCharge;
use Gambio\Core\Configuration\Types\ConfigurationType;
use Gambio\Core\Configuration\Types\Country;
use Gambio\Core\Configuration\Types\CountryZone;
use Gambio\Core\Configuration\Types\Currencies;
use Gambio\Core\Configuration\Types\DefaultCustomerGroup;
use Gambio\Core\Configuration\Types\DefaultPaymentMethod;
use Gambio\Core\Configuration\Types\DefaultType;
use Gambio\Core\Configuration\Types\DownloadOrderStatus;
use Gambio\Core\Configuration\Types\EmailTransportMethod;
use Gambio\Core\Configuration\Types\GeoZone;
use Gambio\Core\Configuration\Types\Lang;
use Gambio\Core\Configuration\Types\LineBreak;
use Gambio\Core\Configuration\Types\NumberType;
use Gambio\Core\Configuration\Types\Ordering;
use Gambio\Core\Configuration\Types\OrderStatus;
use Gambio\Core\Configuration\Types\PasswordEncryptionType;
use Gambio\Core\Configuration\Types\PostfinanceBasicLanguage;
use Gambio\Core\Configuration\Types\ProductReviewMode;
use Gambio\Core\Configuration\Types\ProductsOrderBy;
use Gambio\Core\Configuration\Types\SearchOperator;
use Gambio\Core\Configuration\Types\SenderMail;
use Gambio\Core\Configuration\Types\ShippingDestination;
use Gambio\Core\Configuration\Types\SmtpEncryption;
use Gambio\Core\Configuration\Types\TaxCalculationMode;
use Gambio\Core\Configuration\Types\TaxClass;
use Gambio\Core\Configuration\Types\Textarea;
use Gambio\Core\Configuration\Types\Timezone;
use Gambio\Core\Configuration\Types\UrlReader;
use Gambio\Core\Configuration\Types\Utf8OrIso;
use Gambio\Core\Configuration\Types\WeightOrPrice;
use Webmozart\Assert\Assert;

/**
 * Class ConfigurationFactory
 * @package Gambio\Core\Configuration
 */
class ConfigurationFactory
{
    private const TYPES = [
        'account-type'             => AccountType::class,
        'account-template'         => AccountTemplate::class,
        'ordering'                 => Ordering::class,
        'number'                   => NumberType::class,
        'lang'                     => Lang::class,
        'country'                  => Country::class,
        'country-zone'             => CountryZone::class,
        'geo-zone'                 => GeoZone::class,
        '1-0-switcher'             => Checkbox::class,
        'switcher'                 => Checkbox::class,
        'checkbox'                 => Checkbox::class,
        'textarea'                 => Textarea::class,
        'timezone'                 => Timezone::class,
        'tax-class'                => TaxClass::class,
        'shipping-destination'     => ShippingDestination::class,
        'tax-calculation'          => TaxCalculationMode::class,
        'tax-calculation-mode'     => TaxCalculationMode::class,
        'weight-or-price'          => WeightOrPrice::class,
        'line-break'               => LineBreak::class,
        'sender-mail'              => SenderMail::class,
        'search-operator'          => SearchOperator::class,
        'order-status'             => OrderStatus::class,
        'products-order-by'        => ProductsOrderBy::class,
        'smtp-encryption'          => SmtpEncryption::class,
        'default-customer-group'   => DefaultCustomerGroup::class,
        'email-transport-method'   => EmailTransportMethod::class,
        'url-reader'               => UrlReader::class,
        'auth-mode'                => AuthMode::class,
        'utf8-iso'                 => Utf8OrIso::class,
        'currencies'               => Currencies::class,
        'chf-eur-usd'              => ChfEurUsdCurrencies::class,
        'download-order-status'    => DownloadOrderStatus::class,
        'chf-eur-usd-currencies'   => ChfEurUsdCurrencies::class,
        'postfinance-basic-lang'   => PostfinanceBasicLanguage::class,
        'product-review-mode'      => ProductReviewMode::class,
        'captcha-type'             => CaptchaType::class,
        'password-encryption-type' => PasswordEncryptionType::class,
        'default-payment-method'   => DefaultPaymentMethod::class,
        
        'cod-fee' => CodFeeTransferCharge::class
    ];
    
    
    /**
     * Creates a new configuration with the given data.
     *
     * @param array $data
     *
     * @return ReadConfiguration
     */
    public function createReadConfiguration(array $data): ReadConfiguration
    {
        Assert::keyExists($data, 'key');
        Assert::keyExists($data, 'value');
        
        return ReadConfiguration::create($data['key'], $data['value']);
    }
    
    
    /**
     * Creates the configuration type (DefaultType on null).
     * This is important data for the ui to provide selectable options to the user.
     *
     * @param string|null $type
     *
     * @return ConfigurationType
     */
    public function createConfigurationType(?string $type): ConfigurationType
    {
        if (array_key_exists($type, self::TYPES)) {
            $typeClass = self::TYPES[$type];
            
            return new $typeClass;
        }
        
        return new DefaultType();
    }
    
    
    /**
     * Creates a collection of configurations.
     * @codeCoverageIgnore Due to the methods simplicity.
     *
     * @param ConfigurationGroupItem[] $configurations
     *
     * @return ConfigurationGroup
     */
    public function createConfigurations(ConfigurationGroupItem ...$configurations): ConfigurationGroup
    {
        return ConfigurationGroup::create(...$configurations);
    }
    
    
    /**
     * Creates a new configuration.
     * @codeCoverageIgnore Due to the methods simplicity.
     *
     * @param string       $key
     * @param string       $type
     * @param string       $title
     * @param string       $description
     * @param string|null  $value
     * @param Options|null $options
     * @param array|null   $languageValues
     *
     * @return ConfigurationGroupItem
     */
    public function createConfiguration(
        string $key,
        string $type,
        string $title,
        string $description,
        ?string $value,
        ?Options $options,
        ?array $languageValues
    ): ConfigurationGroupItem {
        $label = ConfigurationGroupItemLabel::create($title, $description);
        
        if ($languageValues) {
            $properties = ConfigurationGroupItemProperties::languageSpecific($key,
                                                                             LanguageValues::fromArray($languageValues));
        } elseif ($options) {
            $properties = ConfigurationGroupItemProperties::withOptions($key, $value, $options);
        } else {
            $properties = ConfigurationGroupItemProperties::simple($key, $value);
        }
        
        return ConfigurationGroupItem::create($label, $properties, $type);
    }
}
