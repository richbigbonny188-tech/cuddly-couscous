<?php
/* --------------------------------------------------------------
 ConfigurationProperties.php 2019-10-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2019 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Models\Read;

use Gambio\Core\Configuration\Models\Read\Collections\Options;

/**
 * Class ConfigurationProperties
 * @package Gambio\Core\Configuration\Models
 */
class ConfigurationGroupItemProperties
{
    public const KEY     = 'key';
    public const VALUE   = 'value';
    public const OPTIONS = 'options';
    
    /**
     * @var string
     */
    private $key;
    
    /**
     * @var string
     */
    private $value;
    
    /**
     * @var Options|null
     */
    private $options;
    
    /**
     * @var LanguageValues|null
     */
    private $lang;
    
    
    /**
     * ConfigurationProperties constructor.
     *
     * @param string              $key
     * @param string|null         $value
     * @param Options|null        $options
     * @param LanguageValues|null $lang
     */
    private function __construct(string $key, ?string $value, Options $options = null, LanguageValues $lang = null)
    {
        $this->key     = $key;
        $this->value   = $value;
        $this->options = $options;
        $this->lang    = $lang;
    }
    
    
    /**
     * Factory method to create simple configuration properties.
     *
     * @param string $key
     * @param string $value
     *
     * @return ConfigurationGroupItemProperties
     */
    public static function simple(string $key, string $value): self
    {
        return new static($key, $value);
    }
    
    
    /**
     * Factory method to create configuration properties with options.
     *
     * @param string  $key
     * @param string  $value
     * @param Options $options
     *
     * @return static
     */
    public static function withOptions(string $key, string $value, Options $options): self
    {
        return new static($key, $value, $options);
    }
    
    
    /**
     * Factory method to create language specific configuration properties.
     *
     * @param string         $key
     * @param LanguageValues $values
     *
     * @return static
     */
    public static function languageSpecific(string $key, LanguageValues $values)
    {
        return new static($key, null, null, $values);
    }
    
    
    /**
     * Array serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data              = [self::KEY => $this->key];
        $data[self::VALUE] = $this->isLanguageSpecific() ? $this->lang->toArray() : $this->value;
        
        if ($this->options) {
            $data[self::OPTIONS] = $this->options->toArray();
        }
        
        return $data;
    }
    
    
    /**
     * Checks if configuration properties are language specific.
     *
     * @return bool
     */
    private function isLanguageSpecific(): bool
    {
        return $this->lang !== null && $this->value === null;
    }
}