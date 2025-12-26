<?php
/*--------------------------------------------------------------------------------------------------
    ThemeConfigurationSerializer.php 2020-07-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Components\Theme\Entities\Serializers;

use Gambio\StyleEdit\Core\Components\Theme\Entities\ThemeConfiguration;
use Gambio\StyleEdit\Core\Language\Entities\Language;

/**
 * Class ConfigurationSerializerPreProcessor
 * @package Gambio\StyleEdit\Core\Components\Theme\Entities\Serializers
 */
class ThemeConfigurationSerializerPreProcessor
{
    /**
     * @var ThemeConfiguration
     */
    protected $configuration;
    
    
    /**
     * ThemeConfigurationSerializer constructor.
     *
     * @param ThemeConfiguration $configuration
     */
    public function __construct(ThemeConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }
    
    
    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function process()
    {
        $themeData          = [];
        $themeData['id']    = $this->configuration->id();
        $themeData['title'] = $this->configuration->title();
        
        if ($this->configuration->extendsOf()) {
            $themeData['extends'] = $this->configuration->extendsOf();
        }
        
        if ($this->configuration->inherits()) {
            $themeData['inherits'] = $this->configuration->inherits();
        }

        if ($this->configuration->isPreview()) {
            $themeData['preview'] = $this->configuration->isPreview();
        }
        
        $themeData['thumbnail'] = $this->configuration->thumbnail();
        $themeData['author']    = $this->configuration->author();
        $themeData['version']   = $this->configuration->version();
        $themeData['removable'] = $this->configuration->isRemovable();
        if ($this->configuration->colorPalette()) {
            $themeData['colorPalette'] = $this->configuration->colorPalette();
        }

        $themeData['languages'] = [];
        foreach ($this->configuration->languages() as $language) {
            /* @var Language $language */
            $themeData['languages'][] = $language->code();
        }

        if ($this->configuration->basics() || $this->configuration->areas()) {
            $themeData['config'] = [];
            if ($this->configuration->basics()) {
                $themeData['config']['basics'] = $this->configuration->basics();
            }
            
            if ($this->configuration->areas()) {
                $themeData['config']['areas'] = $this->configuration->areas();
            }
            
            if ($this->configuration->styles()) {
                $themeData['config']['styles'] = $this->configuration->styles();
            }
        }
        
        if ($this->configuration->isActive()) {
            $themeData['active'] = $this->configuration->isActive();
        }

        $themeData['updatable'] = $this->configuration->isUpdatable();
        $themeData['editable'] = $this->configuration->isEditable();
        
        return $themeData;
    }
}