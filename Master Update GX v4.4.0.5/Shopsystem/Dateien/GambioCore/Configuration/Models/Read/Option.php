<?php
/* --------------------------------------------------------------
 Option.php 2019-10-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2019 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Models\Read;

use InvalidArgumentException;
use function array_key_exists;
use function array_keys;
use function count;
use function implode;

/**
 * Class Option
 * @package Gambio\Core\Configuration\Models
 */
class Option
{
    public const VALUE = 'value';
    public const TEXT  = 'text';
    
    /**
     * @var string
     */
    private $value;
    
    /**
     * @var string
     */
    private $text;
    
    /**
     * @var bool|null
     */
    private $active;
    
    /**
     * @var array
     */
    private $context;
    
    
    /**
     * Option constructor.
     *
     * @param string    $value
     * @param string    $text
     * @param bool|null $active
     * @param array     $context
     */
    private function __construct(string $value, string $text, ?bool $active, array $context = [])
    {
        $this->value   = $value;
        $this->text    = $text;
        $this->active  = $active;
        $this->context = $context;
    }
    
    
    /**
     * Factory method for Option.
     *
     * @param string    $value
     * @param string    $text
     * @param bool|null $active
     * @param array     $context
     *
     * @return Option
     */
    public static function create(string $value, string $text, ?bool $active = null, array $context = []): self
    {
        return new static($value, $text, $active, $context);
    }
    
    
    /**
     * Factory method for Option.
     *
     * @param array $data
     *
     * @return Option
     */
    public static function fromArray(array $data): self
    {
        static::validateKey($data, self::VALUE);
        static::validateKey($data, self::TEXT);
        
        return new static($data[self::VALUE], $data[self::TEXT], $data['active'] ?? null, $data['context'] ?? []);
    }
    
    
    /**
     * Validates if $key exists in array $data.
     *
     * @param array  $data
     * @param string $key
     */
    private static function validateKey(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            $existingKeys = implode(', ', array_keys($data));
            $msg          = "Invalid data array provided. The key ($key) is missing. Existing keys: $existingKeys.";
            
            throw new InvalidArgumentException($msg);
        }
    }
    
    
    /**
     * Array serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            self::VALUE => $this->value,
            self::TEXT  => $this->text,
        ];
        if (null !== $this->active) {
            $data['active'] = $this->active;
        }
        if (count($this->context) > 0) {
            $data['context'] = $this->context;
        }
        
        return $data;
    }
}