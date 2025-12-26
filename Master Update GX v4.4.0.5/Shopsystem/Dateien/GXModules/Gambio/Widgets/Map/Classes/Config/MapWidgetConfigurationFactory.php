<?php
/*--------------------------------------------------------------------------------------------------
    MapWidgetConfigurationFactory.php 2020-11-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

class MapWidgetConfigurationFactory implements MapWidgetConfigurationFactoryInterface
{
    
    /**
     * @param array $params
     *
     * @return MapWidgetCommandConfiguration
     */
    public function createCommandConfigurationFromArray(array $params): MapWidgetCommandConfiguration
    {
        $id               = isset($params['id']) ? $params['id'] : '';
        $mapConfiguration = isset($params['mapConfiguration']) ? $params['mapConfiguration'] : '';
        $languageId       = isset($params['languageId']) ? (int)$params['languageId'] : 0;
        $width            = isset($params['width']) ? $params['width'] : '';
        $height           = isset($params['height']) ? $params['height'] : '';
        $isPreview        = isset($params['isPreview']) ? (bool)$params['isPreview'] : false;
        
        return new MapWidgetCommandConfiguration($id, $mapConfiguration, $languageId, $width, $height, $isPreview);
    }
}