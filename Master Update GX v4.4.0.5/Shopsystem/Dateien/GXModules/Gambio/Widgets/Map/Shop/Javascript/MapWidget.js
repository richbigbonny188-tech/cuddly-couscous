/* --------------------------------------------------------------
  MapWidget.js 2020-11-06
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/
(function() {
    
    'use strict';
    
    let config = {apiKey: '', widgetId: '', mapsConfig: {}};
    const windowDocument = window.document.getElementById('shop-iframe-editor') || window.document;
    
    const initialize = (apiKey, widgetId, mapsConfig) => {
        config.apiKey = apiKey;
        config.widgetId = widgetId;
        config.mapsConfig = mapsConfig;
        
        if (hasScriptTag()) {
            initMap();
            return;
        }
    
        addGoogleMapsScript();
    }
    
    const hasScriptTag = () => windowDocument.getElementById('map-widget-script') !== null;
    
    const initMap = () => {
        let widgetConfigJson = config.mapsConfig;
        let mapElement = windowDocument.getElementById(config.widgetId);
        
        let map = new google.maps.Map(mapElement, widgetConfigJson);
        let marker = new google.maps.Marker({ position: widgetConfigJson.center, map: map });
    }
    
    const addGoogleMapsScript = () => {
        if (!hasScriptTag()) {
            let widgetScriptTag = windowDocument.createElement('script');
            widgetScriptTag.setAttribute('src', `https://maps.googleapis.com/maps/api/js?key=${config.apiKey}`);
            widgetScriptTag.id = 'map-widget-script';
            widgetScriptTag.onload = () => initMap();
            windowDocument.body.appendChild(widgetScriptTag);
        }
    }
    
    window.MapWidget = window.MapWidget || {};
    window.MapWidget = Object.assign({}, {initialize}, window.MapWidget);
})();