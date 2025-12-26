<?php
/* --------------------------------------------------------------
  WidgetRegistrar.php 2019-08-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Class WidgetRegistrar
 *
 * To register a new widget to the system
 * create an overload for this class.
 *
 * Extend the proceed-method and add
 * the absolute path of your widget.json to the array
 */
class WidgetRegistrar
{
    /**
     * @var string[] absolute widget paths
     */
    protected $widgetJsonList = [];
    
    /**
     * @var string[]
     */
    protected const GAMBIO_WIDGET_ORDER = [
        'text',
        'image',
        'headline',
        'button',
        'code',
        'separator',
        'product',
        'productlist',
        'map'
    ];
    
    
    /**
     * @param string $path
     */
    public function addWidget(string $path): void
    {
        $this->widgetJsonList[] = $path;
    }
    
    
    /**
     *
     */
    public function proceed(): void
    {
        
    }
    
    
    /**
     * @return array absolute paths to every available widget
     */
    public function getWidgetsJsonFiles(): array
    {
        if (count($this->widgetJsonList) === 0) {
            
            $this->proceed();
            $this->sortWidgetJsonFiles();
        }
        
        return $this->widgetJsonList;
    }
    
    
    /**
     * Sorts Gambios widgets in a specific order and any other
     * widget alphabetically
     */
    protected function sortWidgetJsonFiles(): void
    {
        $gambioWidgets = $otherWidgets = [];
        
        foreach ($this->widgetJsonList as $widgetJsonPath) {
            
            $widgetJson = json_decode(file_get_contents($widgetJsonPath), false);
            
            if (isset($widgetJson->author, $widgetJson->author->name, $widgetJson->id)
                && $widgetJson->author->name === 'Gambio GmbH'
                && in_array($widgetJson->id, self::GAMBIO_WIDGET_ORDER, true)) {
                
                $widgetPosition                 = array_search($widgetJson->id, self::GAMBIO_WIDGET_ORDER, true);
                $gambioWidgets[$widgetPosition] = $widgetJsonPath;
            } else {
                
                $otherWidgets[] = $widgetJsonPath;
            }
        }
        
        sort($otherWidgets, SORT_STRING);
        ksort($gambioWidgets, SORT_NUMERIC);
        
        $this->widgetJsonList = array_merge($gambioWidgets, $otherWidgets);
    }
}