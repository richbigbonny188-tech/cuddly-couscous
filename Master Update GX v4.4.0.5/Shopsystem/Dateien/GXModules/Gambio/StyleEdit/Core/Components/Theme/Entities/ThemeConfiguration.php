<?php
/*--------------------------------------------------------------------------------------------------
    ThemeConfiguration.php 2020-07-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Components\Theme\Entities;

use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\BasicThemeInterface;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\CurrentThemeInterface;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Serializers\ThemeConfigurationSerializerPreProcessor;
use Gambio\StyleEdit\Core\Options\Entities\ConfigurationCategory;

/**
 * Class ThemeConfiguration
 * @package Gambio\StyleEdit\Core\Components\Theme
 */
class ThemeConfiguration implements CurrentThemeInterface, \JsonSerializable
{
    /**
     * @var ConfigurationCategory
     */
    protected $areas;
    /**
     * @var string
     */
    protected $author = '';
    /**
     * @var ConfigurationCategory
     */
    protected $basics;
    /**
     * @var array
     */
    protected $children = [];
    /**
     * @var array
     */
    protected $colorPalette;
    /**
     * @var string|null
     */
    protected $extendsOf = '';
    /**
     * @var string
     */
    protected $id = '';
    /**
     * @var string | null
     */
    protected $inherits = '';
    /**
     * @var bool
     */
    protected $isActive = false;
    /**
     * @var bool
     */
    protected $isEditable = true;
    /**
     * @var bool
     */
    protected $isPreview = false;
    /**
     * @var bool
     */
    protected $isRemovable;
    /**
     * @var array of Language
     */
    protected $languages = [];
    /**
     * @var string
     */
    protected $path = '';
    /**
     * @var ConfigurationCategory
     */
    protected $styles;
    /**
     * @var string
     */
    protected $thumbnail = '';
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $version = '';
    /**
     * @var bool
     */
    private $isUpdatable;


    /**
     * ThemeConfiguration constructor.
     *
     * @param string $id
     * @param string $title
     * @param string $thumbnail
     * @param string $author
     * @param string $version
     * @param string $extendsOf
     * @param string|null $inherit
     * @param bool $isPreview
     * @param bool $isEditable
     * @param bool $isRemovable
     * @param bool $isActive
     * @param array $colorPalette
     * @param ConfigurationCategory $areas
     * @param ConfigurationCategory $basics
     * @param ConfigurationCategory $styles
     * @param array $children
     * @param array $languages
     * @param string $path
     * @param bool $isUpdatable
     */
    public function __construct(
        string $id,
        string $title,
        string $thumbnail,
        ?string $author,
        ?string $version,
        ?string $extendsOf,
        ?string $inherit,
        bool $isPreview,
        bool $isEditable,
        bool $isRemovable,
        bool $isActive,
        array $colorPalette,
        ?ConfigurationCategory $areas,
        ?ConfigurationCategory $basics,
        ?ConfigurationCategory $styles,
        array $children,
        array $languages,
        string $path,
        bool $isUpdatable
    ) {
        $this->id           = $id;
        $this->title        = $title;
        $this->thumbnail    = $thumbnail;
        $this->author       = $author;
        $this->version      = $version;
        $this->extendsOf    = $extendsOf;
        $this->inherits     = $inherit;
        $this->isPreview    = $isPreview;
        $this->isEditable   = $isEditable;
        $this->isRemovable  = $isRemovable;
        $this->isActive     = $isActive;
        $this->colorPalette = $colorPalette;
        $this->areas        = $areas;
        $this->basics       = $basics;
        $this->children     = $children;
        $this->languages    = $languages;
        $this->path         = $path;
        $this->styles       = $styles;
        $this->isUpdatable = $isUpdatable;
    }
    
    
    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->isEditable;
    }
    
    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
    
    
    /**
     * @return string|null
     */
    public function extendsOf(): ?string
    {
        return $this->extendsOf;
    }
    
    
    /**
     * @return string
     */
    public function author(): ?string
    {
        return $this->author;
    }
    
    
    /**
     * @return string
     */
    public function version(): ?string
    {
        return $this->version;
    }
    
    
    /**
     * @return bool
     */
    public function isPreview(): bool
    {
        return $this->isPreview;
    }
    
    
    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }
    
    
    /**
     * @return array
     */
    public function colorPalette(): ?array
    {
        return $this->colorPalette;
    }
    
    
    /**
     * @return array
     */
    public function languages(): array
    {
        return $this->languages;
    }
    
    
    /**
     * @return ConfigurationCategory
     */
    public function basics(): ?ConfigurationCategory
    {
        return $this->basics;
    }
    
    
    /**
     * @return ConfigurationCategory
     */
    public function areas(): ?ConfigurationCategory
    {
        return $this->areas;
    }
    
    
    /**
     * @return ConfigurationCategory
     */
    public function styles(): ?ConfigurationCategory
    {
        return $this->styles;
    }
    
    
    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }
    
    
    /**
     * @return string
     */
    public function thumbnail(): string
    {
        return $this->thumbnail;
    }
    
    
    /**
     * @return string
     */
    public function path(): string
    {
        return (string)$this->path;
    }
    
    
    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $serializer = new ThemeConfigurationSerializerPreProcessor($this);
        
        return $serializer->process();
    }
    
    
    /**
     * @return bool
     */
    public function isRemovable(): bool
    {
        return $this->isRemovable && count($this->children()) === 0;
    }
    
    
    /**
     * @return array
     */
    public function &children(): array
    {
        return $this->children;
    }
    
    
    /**
     * @return string
     */
    public function inherits(): ?string
    {
        return $this->inherits;
    }
    
    
    /**
     * @inheritDoc
     */
    public function parent(): ?BasicThemeInterface
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isUpdatable(): bool
    {
        return $this->isUpdatable;
    }
}
