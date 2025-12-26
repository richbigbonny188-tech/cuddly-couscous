<?php
/*--------------------------------------------------------------------------------------------------
    MapWidgetCommandConfiguiration.php 2020-11-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

class MapWidgetCommandConfiguration
{
    /**
     * @var string
     */
    protected $widgetId;
    
    
    /**
     * @var array
     */
    protected $mapConfiguration;
    
    
    /**
     * @var int
     */
    protected $languageId;
    
    
    /**
     * @var string
     */
    protected $width;
    
    
    /**
     * @var string
     */
    protected    $height;
    
    /**
     * @var bool
     */
    protected $isPreview;
    
    
    public function __construct(
        string $widgetId,
        array $mapConfiguration,
        int $languageId,
        string $width,
        string $height,
        bool $isPreview
    ) {
        $this->widgetId         = $widgetId;
        $this->mapConfiguration = $mapConfiguration;
        $this->languageId       = $languageId;
        $this->width            = $width;
        $this->height           = $height;
        $this->isPreview        = $isPreview;
    }
    
    
    /**
     * @return string
     */
    public function widgetId(): string
    {
        return $this->widgetId;
    }
    
    
    /**
     * @return array
     */
    public function mapConfiguration(): array
    {
        return $this->mapConfiguration;
    }
    
    
    /**
     * @return int
     */
    public function languageId(): int
    {
        return $this->languageId;
    }
    
    
    /**
     * @return string
     */
    public function width(): string
    {
        return $this->width;
    }
    
    
    /**
     * @return string
     */
    public function height(): string
    {
        return $this->height;
    }
    
    
    /**
     * @return bool
     */
    public function isPreview(): bool
    {
        return $this->isPreview;
    }
    
    
}