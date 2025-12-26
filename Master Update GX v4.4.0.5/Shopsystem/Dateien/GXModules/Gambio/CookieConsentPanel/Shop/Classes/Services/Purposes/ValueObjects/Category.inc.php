<?php
/* --------------------------------------------------------------
  Category.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryInterface;
use Gambio\CookieConsentPanel\Storage\CookieConsentPanelStorage;
use Gambio\Core\Language\Exceptions\LanguageNotFoundException;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class Category
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
abstract class Category implements CategoryInterface, JsonSerializable
{
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var string
     */
    protected $description;
    
    /**
     * @var int
     */
    protected $id;
    
    /**
     * @var CookieConsentPanelStorage
     */
    protected static $storage;
    
    /**
     * @var bool
     */
    protected $value;
    /**
     * @var bool
     */
    protected $locked;
    
    
    /**
     * Category constructor.
     *
     * @param int    $id
     * @param string $name
     * @param string $description
     * @param bool   $value
     * @param bool   $locked
     */
    protected function __construct(int $id, string $name, string $description = '', bool $value = false, bool $locked = false)
    {
        $this->id          = $id;
        $this->name        = $name;
        $this->description = $description;
        $this->value       = $value;
        $this->locked      = $locked;
    }
    
    
    /**
     * @param LanguageCode $code
     *
     * @return CategoryInterface
     */
    abstract public static function create(LanguageCode $code): CategoryInterface;
    
    
    /**
     * @return CookieConsentPanelStorage
     */
    protected static function storage(): CookieConsentPanelStorage
    {
        if (self::$storage === null) {
    
            self::$storage = new CookieConsentPanelStorage;
        }
        
        return self::$storage;
    }
    
    
    /**
     * @param string       $key
     *
     * @param LanguageCode $code
     *
     * @return string
     */
    protected static function translatedName(string $key, LanguageCode $code): string
    {
        $json = self::storage()->get($key);
        $object = json_decode($json);
        
        if (!isset($object->{$code->value()})) {
            return 'Invalid language code provided. No data found for language code: '.$code->value();
        }
        
        return $object->{$code->value()};
    }
    
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * @inheritDoc
     */
    public function id(): int
    {
        return $this->id;
    }
    
    
    
    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return (object)[
            'id'          => $this->id(),
            'name'        => $this->name(),
            'description' => $this->description(),
            'value'       => $this->value(),
            'locked'      => $this->locked()
        ];
    }
    
    
    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }
    
    
    /**
     * @return bool
     */
    public function value(): bool
    {
        return $this->value;
    }
    
    
    /**
     * @return bool
     */
    public function locked(): bool
    {
        return $this->locked;
    }
}