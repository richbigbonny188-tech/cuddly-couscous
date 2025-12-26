<?php
/*--------------------------------------------------------------------------------------------------
    MapWidgetApplicationBottom.inc.php 2020-11-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

class MapWidgetApplicationBottom extends MapWidgetApplicationBottom_parent
{
    function proceed()
    {
        
        $buffer = [];
        $buffer[] = "<script src='{$this->getJsPath()}'></script>" . PHP_EOL;
        
        $this->v_output_buffer['MAP_WIDGET'] = implode(PHP_EOL, $buffer);
        
        parent::proceed();
    }
    
    
    /**
     * @return string
     */
    protected function getJsPath()
    {
        if (file_exists(DIR_FS_CATALOG . '.dev-environment')) {
            return 'GXModules/Gambio/Widgets/Map/Shop/Javascript/MapWidget.js';
        }
        
        return 'GXModules/Gambio/Widgets/Build/Map/Shop/Javascript/MapWidget.min.js';
    }
}