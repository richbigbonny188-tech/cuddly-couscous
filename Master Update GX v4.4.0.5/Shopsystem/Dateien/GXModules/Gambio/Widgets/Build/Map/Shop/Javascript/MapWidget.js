'use strict';

/* --------------------------------------------------------------
  MapWidget.js 2020-11-06
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/
(function () {

    'use strict';

    var config = { apiKey: '', widgetId: '', mapsConfig: {} };
    var windowDocument = window.document.getElementById('shop-iframe-editor') || window.document;

    var initialize = function initialize(apiKey, widgetId, mapsConfig) {
        config.apiKey = apiKey;
        config.widgetId = widgetId;
        config.mapsConfig = mapsConfig;

        if (hasScriptTag()) {
            initMap();
            return;
        }

        addGoogleMapsScript();
    };

    var hasScriptTag = function hasScriptTag() {
        return windowDocument.getElementById('map-widget-script') !== null;
    };

    var initMap = function initMap() {
        var widgetConfigJson = config.mapsConfig;
        var mapElement = windowDocument.getElementById(config.widgetId);

        var map = new google.maps.Map(mapElement, widgetConfigJson);
        var marker = new google.maps.Marker({ position: widgetConfigJson.center, map: map });
    };

    var addGoogleMapsScript = function addGoogleMapsScript() {
        if (!hasScriptTag()) {
            var widgetScriptTag = windowDocument.createElement('script');
            widgetScriptTag.setAttribute('src', 'https://maps.googleapis.com/maps/api/js?key=' + config.apiKey);
            widgetScriptTag.id = 'map-widget-script';
            widgetScriptTag.onload = function () {
                return initMap();
            };
            windowDocument.body.appendChild(widgetScriptTag);
        }
    };

    window.MapWidget = window.MapWidget || {};
    window.MapWidget = Object.assign({}, { initialize: initialize }, window.MapWidget);
})();
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIk1hcC9TaG9wL0phdmFzY3JpcHQvTWFwV2lkZ2V0LmpzIl0sIm5hbWVzIjpbImNvbmZpZyIsImFwaUtleSIsIndpZGdldElkIiwibWFwc0NvbmZpZyIsIndpbmRvd0RvY3VtZW50Iiwid2luZG93IiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsImluaXRpYWxpemUiLCJoYXNTY3JpcHRUYWciLCJpbml0TWFwIiwiYWRkR29vZ2xlTWFwc1NjcmlwdCIsIndpZGdldENvbmZpZ0pzb24iLCJtYXBFbGVtZW50IiwibWFwIiwiZ29vZ2xlIiwibWFwcyIsIk1hcCIsIm1hcmtlciIsIk1hcmtlciIsInBvc2l0aW9uIiwiY2VudGVyIiwid2lkZ2V0U2NyaXB0VGFnIiwiY3JlYXRlRWxlbWVudCIsInNldEF0dHJpYnV0ZSIsImlkIiwib25sb2FkIiwiYm9keSIsImFwcGVuZENoaWxkIiwiTWFwV2lkZ2V0IiwiT2JqZWN0IiwiYXNzaWduIl0sIm1hcHBpbmdzIjoiOztBQUFBOzs7Ozs7OztBQVFBLENBQUMsWUFBVzs7QUFFUjs7QUFFQSxRQUFJQSxTQUFTLEVBQUNDLFFBQVEsRUFBVCxFQUFhQyxVQUFVLEVBQXZCLEVBQTJCQyxZQUFZLEVBQXZDLEVBQWI7QUFDQSxRQUFNQyxpQkFBaUJDLE9BQU9DLFFBQVAsQ0FBZ0JDLGNBQWhCLENBQStCLG9CQUEvQixLQUF3REYsT0FBT0MsUUFBdEY7O0FBRUEsUUFBTUUsYUFBYSxTQUFiQSxVQUFhLENBQUNQLE1BQUQsRUFBU0MsUUFBVCxFQUFtQkMsVUFBbkIsRUFBa0M7QUFDakRILGVBQU9DLE1BQVAsR0FBZ0JBLE1BQWhCO0FBQ0FELGVBQU9FLFFBQVAsR0FBa0JBLFFBQWxCO0FBQ0FGLGVBQU9HLFVBQVAsR0FBb0JBLFVBQXBCOztBQUVBLFlBQUlNLGNBQUosRUFBb0I7QUFDaEJDO0FBQ0E7QUFDSDs7QUFFREM7QUFDSCxLQVhEOztBQWFBLFFBQU1GLGVBQWUsU0FBZkEsWUFBZTtBQUFBLGVBQU1MLGVBQWVHLGNBQWYsQ0FBOEIsbUJBQTlCLE1BQXVELElBQTdEO0FBQUEsS0FBckI7O0FBRUEsUUFBTUcsVUFBVSxTQUFWQSxPQUFVLEdBQU07QUFDbEIsWUFBSUUsbUJBQW1CWixPQUFPRyxVQUE5QjtBQUNBLFlBQUlVLGFBQWFULGVBQWVHLGNBQWYsQ0FBOEJQLE9BQU9FLFFBQXJDLENBQWpCOztBQUVBLFlBQUlZLE1BQU0sSUFBSUMsT0FBT0MsSUFBUCxDQUFZQyxHQUFoQixDQUFvQkosVUFBcEIsRUFBZ0NELGdCQUFoQyxDQUFWO0FBQ0EsWUFBSU0sU0FBUyxJQUFJSCxPQUFPQyxJQUFQLENBQVlHLE1BQWhCLENBQXVCLEVBQUVDLFVBQVVSLGlCQUFpQlMsTUFBN0IsRUFBcUNQLEtBQUtBLEdBQTFDLEVBQXZCLENBQWI7QUFDSCxLQU5EOztBQVFBLFFBQU1ILHNCQUFzQixTQUF0QkEsbUJBQXNCLEdBQU07QUFDOUIsWUFBSSxDQUFDRixjQUFMLEVBQXFCO0FBQ2pCLGdCQUFJYSxrQkFBa0JsQixlQUFlbUIsYUFBZixDQUE2QixRQUE3QixDQUF0QjtBQUNBRCw0QkFBZ0JFLFlBQWhCLENBQTZCLEtBQTdCLG1EQUFtRnhCLE9BQU9DLE1BQTFGO0FBQ0FxQiw0QkFBZ0JHLEVBQWhCLEdBQXFCLG1CQUFyQjtBQUNBSCw0QkFBZ0JJLE1BQWhCLEdBQXlCO0FBQUEsdUJBQU1oQixTQUFOO0FBQUEsYUFBekI7QUFDQU4sMkJBQWV1QixJQUFmLENBQW9CQyxXQUFwQixDQUFnQ04sZUFBaEM7QUFDSDtBQUNKLEtBUkQ7O0FBVUFqQixXQUFPd0IsU0FBUCxHQUFtQnhCLE9BQU93QixTQUFQLElBQW9CLEVBQXZDO0FBQ0F4QixXQUFPd0IsU0FBUCxHQUFtQkMsT0FBT0MsTUFBUCxDQUFjLEVBQWQsRUFBa0IsRUFBQ3ZCLHNCQUFELEVBQWxCLEVBQWdDSCxPQUFPd0IsU0FBdkMsQ0FBbkI7QUFDSCxDQTFDRCIsImZpbGUiOiJNYXAvU2hvcC9KYXZhc2NyaXB0L01hcFdpZGdldC5qcyIsInNvdXJjZXNDb250ZW50IjpbIi8qIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tXG4gIE1hcFdpZGdldC5qcyAyMDIwLTExLTA2XG4gIEdhbWJpbyBHbWJIXG4gIGh0dHA6Ly93d3cuZ2FtYmlvLmRlXG4gIENvcHlyaWdodCAoYykgMjAyMCBHYW1iaW8gR21iSFxuICBSZWxlYXNlZCB1bmRlciB0aGUgR05VIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgKFZlcnNpb24gMilcbiAgW2h0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy9ncGwtMi4wLmh0bWxdXG4gIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKi9cbihmdW5jdGlvbigpIHtcbiAgICBcbiAgICAndXNlIHN0cmljdCc7XG4gICAgXG4gICAgbGV0IGNvbmZpZyA9IHthcGlLZXk6ICcnLCB3aWRnZXRJZDogJycsIG1hcHNDb25maWc6IHt9fTtcbiAgICBjb25zdCB3aW5kb3dEb2N1bWVudCA9IHdpbmRvdy5kb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2hvcC1pZnJhbWUtZWRpdG9yJykgfHwgd2luZG93LmRvY3VtZW50O1xuICAgIFxuICAgIGNvbnN0IGluaXRpYWxpemUgPSAoYXBpS2V5LCB3aWRnZXRJZCwgbWFwc0NvbmZpZykgPT4ge1xuICAgICAgICBjb25maWcuYXBpS2V5ID0gYXBpS2V5O1xuICAgICAgICBjb25maWcud2lkZ2V0SWQgPSB3aWRnZXRJZDtcbiAgICAgICAgY29uZmlnLm1hcHNDb25maWcgPSBtYXBzQ29uZmlnO1xuICAgICAgICBcbiAgICAgICAgaWYgKGhhc1NjcmlwdFRhZygpKSB7XG4gICAgICAgICAgICBpbml0TWFwKCk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cbiAgICBcbiAgICAgICAgYWRkR29vZ2xlTWFwc1NjcmlwdCgpO1xuICAgIH1cbiAgICBcbiAgICBjb25zdCBoYXNTY3JpcHRUYWcgPSAoKSA9PiB3aW5kb3dEb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnbWFwLXdpZGdldC1zY3JpcHQnKSAhPT0gbnVsbDtcbiAgICBcbiAgICBjb25zdCBpbml0TWFwID0gKCkgPT4ge1xuICAgICAgICBsZXQgd2lkZ2V0Q29uZmlnSnNvbiA9IGNvbmZpZy5tYXBzQ29uZmlnO1xuICAgICAgICBsZXQgbWFwRWxlbWVudCA9IHdpbmRvd0RvY3VtZW50LmdldEVsZW1lbnRCeUlkKGNvbmZpZy53aWRnZXRJZCk7XG4gICAgICAgIFxuICAgICAgICBsZXQgbWFwID0gbmV3IGdvb2dsZS5tYXBzLk1hcChtYXBFbGVtZW50LCB3aWRnZXRDb25maWdKc29uKTtcbiAgICAgICAgbGV0IG1hcmtlciA9IG5ldyBnb29nbGUubWFwcy5NYXJrZXIoeyBwb3NpdGlvbjogd2lkZ2V0Q29uZmlnSnNvbi5jZW50ZXIsIG1hcDogbWFwIH0pO1xuICAgIH1cbiAgICBcbiAgICBjb25zdCBhZGRHb29nbGVNYXBzU2NyaXB0ID0gKCkgPT4ge1xuICAgICAgICBpZiAoIWhhc1NjcmlwdFRhZygpKSB7XG4gICAgICAgICAgICBsZXQgd2lkZ2V0U2NyaXB0VGFnID0gd2luZG93RG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnc2NyaXB0Jyk7XG4gICAgICAgICAgICB3aWRnZXRTY3JpcHRUYWcuc2V0QXR0cmlidXRlKCdzcmMnLCBgaHR0cHM6Ly9tYXBzLmdvb2dsZWFwaXMuY29tL21hcHMvYXBpL2pzP2tleT0ke2NvbmZpZy5hcGlLZXl9YCk7XG4gICAgICAgICAgICB3aWRnZXRTY3JpcHRUYWcuaWQgPSAnbWFwLXdpZGdldC1zY3JpcHQnO1xuICAgICAgICAgICAgd2lkZ2V0U2NyaXB0VGFnLm9ubG9hZCA9ICgpID0+IGluaXRNYXAoKTtcbiAgICAgICAgICAgIHdpbmRvd0RvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQod2lkZ2V0U2NyaXB0VGFnKTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBcbiAgICB3aW5kb3cuTWFwV2lkZ2V0ID0gd2luZG93Lk1hcFdpZGdldCB8fCB7fTtcbiAgICB3aW5kb3cuTWFwV2lkZ2V0ID0gT2JqZWN0LmFzc2lnbih7fSwge2luaXRpYWxpemV9LCB3aW5kb3cuTWFwV2lkZ2V0KTtcbn0pKCk7Il19
