'use strict';

/* --------------------------------------------------------------
 google_connection_iframe.js 2018-05-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2018 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

gx_google_oauth.widgets.module('google_connection_iframe', [], function (data) {

    'use strict';

    // ------------------------------------------------------------------------
    // VARIABLE DEFINITION
    // ------------------------------------------------------------------------

    /**
     * Widget Reference
     *
     * @type {object}
     */

    var $this = $(this);

    /**
     * Default Options for Widget
     *
     * @type {object}
     */
    var defaults = {
        connected: false
    };

    /**
     * Final Widget Options
     *
     * @type {object}
     */
    var options = $.extend(true, {}, defaults, data);

    /**
     * Module Object
     *
     * @type {object}
     */
    var module = {};

    /**
     * CSS styles for manually rendered iframe.
     * @type {{width: string, border: string}}
     */
    var iFrameStyles = {
        width: '100%',
        border: 'none',
        height: '290px',
        'min-height': '190px'
    };

    /**
     * Manually rendered iframe element.
     * Member is available after ::renderIframe function execution.
     */
    var $connectionIFrame = void 0;

    // ------------------------------------------------------------------------
    // FUNCTIONALITY
    // ------------------------------------------------------------------------
    /**
     * Validates widgets and check that all required options exists.
     */
    var validateOptions = function validateOptions() {
        optionExist('url');
        optionExist('origin');
        optionExist('language');
        optionExist('error');
        optionExist('from');
        optionExist('version');
    };

    /**
     * Checks if given option exists and throws error if not.
     *
     * @param {string} option
     */
    var optionExist = function optionExist(option) {
        if (!(option in options)) {
            throw new Error('Required option "' + option + '" is missing');
        }
    };

    // ------------------------------------------------------------------------
    // EVENT HANDLERS
    // ------------------------------------------------------------------------
    /**
     * Renders (manually) the "google connecting iframe".
     * The iframe gets rendered by javascript to improve the post messaging.
     */
    var renderIframe = function renderIframe() {
        var iFrameUrl = options.url + '?origin=' + options.origin + '&from=' + options.from + '&version=' + options.version + '&language=' + options.language + '&error=' + options.error;

        var connected = options.connected === 1;

        if (connected) {
            iFrameUrl = iFrameUrl + '&connected=true';
        }

        $connectionIFrame = $('<iframe/>').attr('src', '../GXModules/Gambio/GoogleOAuth/Admin/Html/initial_iframe_content.html').on('load', requestIframeHeight).css(iFrameStyles);
        $this.parent().empty().append($connectionIFrame);
        $connectionIFrame.attr('src', iFrameUrl);
    };

    /**
     * Requests the iframe height via post message.
     * @param e
     */
    var requestIframeHeight = function requestIframeHeight(e) {
        e.target.contentWindow.postMessage({
            type: 'request_iframe_height'
        }, '*');
    };

    /**
     * Post message response processing for iframe height.
     * @param e
     */
    var responseIframeHeight = function responseIframeHeight(e) {
        if (e.data.type === 'response_iframe_height') {
            $connectionIFrame.css({ 'height': e.data.height.toString() + 'px' });
        }
    };

    // ------------------------------------------------------------------------
    // INITIALIZATION
    // ------------------------------------------------------------------------

    /**
     * Initialize method of the widget, called by the engine.
     */
    module.init = function (done) {
        validateOptions();

        window.addEventListener('message', responseIframeHeight, false);
        renderIframe();

        done();
    };

    return module;
});
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIkFkbWluL0phdmFzY3JpcHQvd2lkZ2V0cy9nb29nbGVfY29ubmVjdGlvbl9pZnJhbWUuanMiXSwibmFtZXMiOlsiZ3hfZ29vZ2xlX29hdXRoIiwid2lkZ2V0cyIsIm1vZHVsZSIsImRhdGEiLCIkdGhpcyIsIiQiLCJkZWZhdWx0cyIsImNvbm5lY3RlZCIsIm9wdGlvbnMiLCJleHRlbmQiLCJpRnJhbWVTdHlsZXMiLCJ3aWR0aCIsImJvcmRlciIsImhlaWdodCIsIiRjb25uZWN0aW9uSUZyYW1lIiwidmFsaWRhdGVPcHRpb25zIiwib3B0aW9uRXhpc3QiLCJvcHRpb24iLCJFcnJvciIsInJlbmRlcklmcmFtZSIsImlGcmFtZVVybCIsInVybCIsIm9yaWdpbiIsImZyb20iLCJ2ZXJzaW9uIiwibGFuZ3VhZ2UiLCJlcnJvciIsImF0dHIiLCJvbiIsInJlcXVlc3RJZnJhbWVIZWlnaHQiLCJjc3MiLCJwYXJlbnQiLCJlbXB0eSIsImFwcGVuZCIsImUiLCJ0YXJnZXQiLCJjb250ZW50V2luZG93IiwicG9zdE1lc3NhZ2UiLCJ0eXBlIiwicmVzcG9uc2VJZnJhbWVIZWlnaHQiLCJ0b1N0cmluZyIsImluaXQiLCJkb25lIiwid2luZG93IiwiYWRkRXZlbnRMaXN0ZW5lciJdLCJtYXBwaW5ncyI6Ijs7QUFBQTs7Ozs7Ozs7OztBQVVBQSxnQkFBZ0JDLE9BQWhCLENBQXdCQyxNQUF4QixDQUNJLDBCQURKLEVBR0ksRUFISixFQUtJLFVBQVVDLElBQVYsRUFBZ0I7O0FBRVo7O0FBRUE7QUFDQTtBQUNBOztBQUVBOzs7Ozs7QUFLQSxRQUFNQyxRQUFRQyxFQUFFLElBQUYsQ0FBZDs7QUFFQTs7Ozs7QUFLQSxRQUFNQyxXQUFXO0FBQ2JDLG1CQUFXO0FBREUsS0FBakI7O0FBSUE7Ozs7O0FBS0EsUUFBTUMsVUFBVUgsRUFBRUksTUFBRixDQUFTLElBQVQsRUFBZSxFQUFmLEVBQW1CSCxRQUFuQixFQUE2QkgsSUFBN0IsQ0FBaEI7O0FBRUE7Ozs7O0FBS0EsUUFBTUQsU0FBUyxFQUFmOztBQUVBOzs7O0FBSUEsUUFBTVEsZUFBZTtBQUNqQkMsZUFBTyxNQURVO0FBRWpCQyxnQkFBUSxNQUZTO0FBR2pCQyxnQkFBUSxPQUhTO0FBSWpCLHNCQUFjO0FBSkcsS0FBckI7O0FBT0E7Ozs7QUFJQSxRQUFJQywwQkFBSjs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7O0FBR0EsUUFBTUMsa0JBQWtCLFNBQWxCQSxlQUFrQixHQUFNO0FBQzFCQyxvQkFBWSxLQUFaO0FBQ0FBLG9CQUFZLFFBQVo7QUFDQUEsb0JBQVksVUFBWjtBQUNBQSxvQkFBWSxPQUFaO0FBQ0FBLG9CQUFZLE1BQVo7QUFDQUEsb0JBQVksU0FBWjtBQUNILEtBUEQ7O0FBU0E7Ozs7O0FBS0EsUUFBTUEsY0FBYyxTQUFkQSxXQUFjLFNBQVU7QUFDMUIsWUFBSSxFQUFFQyxVQUFVVCxPQUFaLENBQUosRUFBMEI7QUFDdEIsa0JBQU0sSUFBSVUsS0FBSixDQUFVLHNCQUFzQkQsTUFBdEIsR0FBK0IsY0FBekMsQ0FBTjtBQUNIO0FBQ0osS0FKRDs7QUFNQTtBQUNBO0FBQ0E7QUFDQTs7OztBQUlBLFFBQU1FLGVBQWUsU0FBZkEsWUFBZSxHQUFNO0FBQ3ZCLFlBQUlDLFlBQVlaLFFBQVFhLEdBQVIsR0FBYyxVQUFkLEdBQTJCYixRQUFRYyxNQUFuQyxHQUE0QyxRQUE1QyxHQUF1RGQsUUFBUWUsSUFBL0QsR0FBc0UsV0FBdEUsR0FDVmYsUUFBUWdCLE9BREUsR0FDUSxZQURSLEdBQ3VCaEIsUUFBUWlCLFFBRC9CLEdBQzBDLFNBRDFDLEdBQ3NEakIsUUFBUWtCLEtBRDlFOztBQUdBLFlBQUluQixZQUFZQyxRQUFRRCxTQUFSLEtBQXNCLENBQXRDOztBQUVBLFlBQUlBLFNBQUosRUFBZTtBQUNYYSx3QkFBWUEsWUFBWSxpQkFBeEI7QUFDSDs7QUFFRE4sNEJBQW9CVCxFQUFFLFdBQUYsRUFDZnNCLElBRGUsQ0FDVixLQURVLEVBQ0gsd0VBREcsRUFFZkMsRUFGZSxDQUVaLE1BRlksRUFFSkMsbUJBRkksRUFHZkMsR0FIZSxDQUdYcEIsWUFIVyxDQUFwQjtBQUlBTixjQUFNMkIsTUFBTixHQUFlQyxLQUFmLEdBQXVCQyxNQUF2QixDQUE4Qm5CLGlCQUE5QjtBQUNBQSwwQkFBa0JhLElBQWxCLENBQXVCLEtBQXZCLEVBQThCUCxTQUE5QjtBQUNILEtBaEJEOztBQWtCQTs7OztBQUlBLFFBQU1TLHNCQUFzQixTQUF0QkEsbUJBQXNCLElBQUs7QUFDN0JLLFVBQUVDLE1BQUYsQ0FBU0MsYUFBVCxDQUF1QkMsV0FBdkIsQ0FBbUM7QUFDL0JDLGtCQUFNO0FBRHlCLFNBQW5DLEVBRUcsR0FGSDtBQUdILEtBSkQ7O0FBTUE7Ozs7QUFJQSxRQUFNQyx1QkFBdUIsU0FBdkJBLG9CQUF1QixJQUFLO0FBQzlCLFlBQUlMLEVBQUUvQixJQUFGLENBQU9tQyxJQUFQLEtBQWdCLHdCQUFwQixFQUE4QztBQUMxQ3hCLDhCQUFrQmdCLEdBQWxCLENBQXNCLEVBQUMsVUFBVUksRUFBRS9CLElBQUYsQ0FBT1UsTUFBUCxDQUFjMkIsUUFBZCxLQUEyQixJQUF0QyxFQUF0QjtBQUNIO0FBQ0osS0FKRDs7QUFNQTtBQUNBO0FBQ0E7O0FBRUE7OztBQUdBdEMsV0FBT3VDLElBQVAsR0FBYyxVQUFVQyxJQUFWLEVBQWdCO0FBQzFCM0I7O0FBRUE0QixlQUFPQyxnQkFBUCxDQUF3QixTQUF4QixFQUFtQ0wsb0JBQW5DLEVBQXlELEtBQXpEO0FBQ0FwQjs7QUFFQXVCO0FBQ0gsS0FQRDs7QUFTQSxXQUFPeEMsTUFBUDtBQUNILENBcEpMIiwiZmlsZSI6IkFkbWluL0phdmFzY3JpcHQvd2lkZ2V0cy9nb29nbGVfY29ubmVjdGlvbl9pZnJhbWUuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvKiAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLVxuIGdvb2dsZV9jb25uZWN0aW9uX2lmcmFtZS5qcyAyMDE4LTA1LTE4XG4gR2FtYmlvIEdtYkhcbiBodHRwOi8vd3d3LmdhbWJpby5kZVxuIENvcHlyaWdodCAoYykgMjAxOCBHYW1iaW8gR21iSFxuIFJlbGVhc2VkIHVuZGVyIHRoZSBHTlUgR2VuZXJhbCBQdWJsaWMgTGljZW5zZSAoVmVyc2lvbiAyKVxuIFtodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvZ3BsLTIuMC5odG1sXVxuIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tXG4gKi9cblxuZ3hfZ29vZ2xlX29hdXRoLndpZGdldHMubW9kdWxlKFxuICAgICdnb29nbGVfY29ubmVjdGlvbl9pZnJhbWUnLFxuXG4gICAgW10sXG5cbiAgICBmdW5jdGlvbiAoZGF0YSkge1xuXG4gICAgICAgICd1c2Ugc3RyaWN0JztcblxuICAgICAgICAvLyAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS1cbiAgICAgICAgLy8gVkFSSUFCTEUgREVGSU5JVElPTlxuICAgICAgICAvLyAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS1cblxuICAgICAgICAvKipcbiAgICAgICAgICogV2lkZ2V0IFJlZmVyZW5jZVxuICAgICAgICAgKlxuICAgICAgICAgKiBAdHlwZSB7b2JqZWN0fVxuICAgICAgICAgKi9cbiAgICAgICAgY29uc3QgJHRoaXMgPSAkKHRoaXMpO1xuXG4gICAgICAgIC8qKlxuICAgICAgICAgKiBEZWZhdWx0IE9wdGlvbnMgZm9yIFdpZGdldFxuICAgICAgICAgKlxuICAgICAgICAgKiBAdHlwZSB7b2JqZWN0fVxuICAgICAgICAgKi9cbiAgICAgICAgY29uc3QgZGVmYXVsdHMgPSB7XG4gICAgICAgICAgICBjb25uZWN0ZWQ6IGZhbHNlXG4gICAgICAgIH07XG5cbiAgICAgICAgLyoqXG4gICAgICAgICAqIEZpbmFsIFdpZGdldCBPcHRpb25zXG4gICAgICAgICAqXG4gICAgICAgICAqIEB0eXBlIHtvYmplY3R9XG4gICAgICAgICAqL1xuICAgICAgICBjb25zdCBvcHRpb25zID0gJC5leHRlbmQodHJ1ZSwge30sIGRlZmF1bHRzLCBkYXRhKTtcblxuICAgICAgICAvKipcbiAgICAgICAgICogTW9kdWxlIE9iamVjdFxuICAgICAgICAgKlxuICAgICAgICAgKiBAdHlwZSB7b2JqZWN0fVxuICAgICAgICAgKi9cbiAgICAgICAgY29uc3QgbW9kdWxlID0ge307XG5cbiAgICAgICAgLyoqXG4gICAgICAgICAqIENTUyBzdHlsZXMgZm9yIG1hbnVhbGx5IHJlbmRlcmVkIGlmcmFtZS5cbiAgICAgICAgICogQHR5cGUge3t3aWR0aDogc3RyaW5nLCBib3JkZXI6IHN0cmluZ319XG4gICAgICAgICAqL1xuICAgICAgICBjb25zdCBpRnJhbWVTdHlsZXMgPSB7XG4gICAgICAgICAgICB3aWR0aDogJzEwMCUnLFxuICAgICAgICAgICAgYm9yZGVyOiAnbm9uZScsXG4gICAgICAgICAgICBoZWlnaHQ6ICcyOTBweCcsXG4gICAgICAgICAgICAnbWluLWhlaWdodCc6ICcxOTBweCdcbiAgICAgICAgfTtcblxuICAgICAgICAvKipcbiAgICAgICAgICogTWFudWFsbHkgcmVuZGVyZWQgaWZyYW1lIGVsZW1lbnQuXG4gICAgICAgICAqIE1lbWJlciBpcyBhdmFpbGFibGUgYWZ0ZXIgOjpyZW5kZXJJZnJhbWUgZnVuY3Rpb24gZXhlY3V0aW9uLlxuICAgICAgICAgKi9cbiAgICAgICAgbGV0ICRjb25uZWN0aW9uSUZyYW1lO1xuXG4gICAgICAgIC8vIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLVxuICAgICAgICAvLyBGVU5DVElPTkFMSVRZXG4gICAgICAgIC8vIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLVxuICAgICAgICAvKipcbiAgICAgICAgICogVmFsaWRhdGVzIHdpZGdldHMgYW5kIGNoZWNrIHRoYXQgYWxsIHJlcXVpcmVkIG9wdGlvbnMgZXhpc3RzLlxuICAgICAgICAgKi9cbiAgICAgICAgY29uc3QgdmFsaWRhdGVPcHRpb25zID0gKCkgPT4ge1xuICAgICAgICAgICAgb3B0aW9uRXhpc3QoJ3VybCcpO1xuICAgICAgICAgICAgb3B0aW9uRXhpc3QoJ29yaWdpbicpO1xuICAgICAgICAgICAgb3B0aW9uRXhpc3QoJ2xhbmd1YWdlJyk7XG4gICAgICAgICAgICBvcHRpb25FeGlzdCgnZXJyb3InKTtcbiAgICAgICAgICAgIG9wdGlvbkV4aXN0KCdmcm9tJyk7XG4gICAgICAgICAgICBvcHRpb25FeGlzdCgndmVyc2lvbicpO1xuICAgICAgICB9O1xuXG4gICAgICAgIC8qKlxuICAgICAgICAgKiBDaGVja3MgaWYgZ2l2ZW4gb3B0aW9uIGV4aXN0cyBhbmQgdGhyb3dzIGVycm9yIGlmIG5vdC5cbiAgICAgICAgICpcbiAgICAgICAgICogQHBhcmFtIHtzdHJpbmd9IG9wdGlvblxuICAgICAgICAgKi9cbiAgICAgICAgY29uc3Qgb3B0aW9uRXhpc3QgPSBvcHRpb24gPT4ge1xuICAgICAgICAgICAgaWYgKCEob3B0aW9uIGluIG9wdGlvbnMpKSB7XG4gICAgICAgICAgICAgICAgdGhyb3cgbmV3IEVycm9yKCdSZXF1aXJlZCBvcHRpb24gXCInICsgb3B0aW9uICsgJ1wiIGlzIG1pc3NpbmcnKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfTtcblxuICAgICAgICAvLyAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS1cbiAgICAgICAgLy8gRVZFTlQgSEFORExFUlNcbiAgICAgICAgLy8gLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tXG4gICAgICAgIC8qKlxuICAgICAgICAgKiBSZW5kZXJzIChtYW51YWxseSkgdGhlIFwiZ29vZ2xlIGNvbm5lY3RpbmcgaWZyYW1lXCIuXG4gICAgICAgICAqIFRoZSBpZnJhbWUgZ2V0cyByZW5kZXJlZCBieSBqYXZhc2NyaXB0IHRvIGltcHJvdmUgdGhlIHBvc3QgbWVzc2FnaW5nLlxuICAgICAgICAgKi9cbiAgICAgICAgY29uc3QgcmVuZGVySWZyYW1lID0gKCkgPT4ge1xuICAgICAgICAgICAgbGV0IGlGcmFtZVVybCA9IG9wdGlvbnMudXJsICsgJz9vcmlnaW49JyArIG9wdGlvbnMub3JpZ2luICsgJyZmcm9tPScgKyBvcHRpb25zLmZyb20gKyAnJnZlcnNpb249J1xuICAgICAgICAgICAgICAgICsgb3B0aW9ucy52ZXJzaW9uICsgJyZsYW5ndWFnZT0nICsgb3B0aW9ucy5sYW5ndWFnZSArICcmZXJyb3I9JyArIG9wdGlvbnMuZXJyb3I7XG5cbiAgICAgICAgICAgIGxldCBjb25uZWN0ZWQgPSBvcHRpb25zLmNvbm5lY3RlZCA9PT0gMTtcblxuICAgICAgICAgICAgaWYgKGNvbm5lY3RlZCkge1xuICAgICAgICAgICAgICAgIGlGcmFtZVVybCA9IGlGcmFtZVVybCArICcmY29ubmVjdGVkPXRydWUnO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAkY29ubmVjdGlvbklGcmFtZSA9ICQoJzxpZnJhbWUvPicpXG4gICAgICAgICAgICAgICAgLmF0dHIoJ3NyYycsICcuLi9HWE1vZHVsZXMvR2FtYmlvL0dvb2dsZU9BdXRoL0FkbWluL0h0bWwvaW5pdGlhbF9pZnJhbWVfY29udGVudC5odG1sJylcbiAgICAgICAgICAgICAgICAub24oJ2xvYWQnLCByZXF1ZXN0SWZyYW1lSGVpZ2h0KVxuICAgICAgICAgICAgICAgIC5jc3MoaUZyYW1lU3R5bGVzKTtcbiAgICAgICAgICAgICR0aGlzLnBhcmVudCgpLmVtcHR5KCkuYXBwZW5kKCRjb25uZWN0aW9uSUZyYW1lKTtcbiAgICAgICAgICAgICRjb25uZWN0aW9uSUZyYW1lLmF0dHIoJ3NyYycsIGlGcmFtZVVybCk7XG4gICAgICAgIH07XG5cbiAgICAgICAgLyoqXG4gICAgICAgICAqIFJlcXVlc3RzIHRoZSBpZnJhbWUgaGVpZ2h0IHZpYSBwb3N0IG1lc3NhZ2UuXG4gICAgICAgICAqIEBwYXJhbSBlXG4gICAgICAgICAqL1xuICAgICAgICBjb25zdCByZXF1ZXN0SWZyYW1lSGVpZ2h0ID0gZSA9PiB7XG4gICAgICAgICAgICBlLnRhcmdldC5jb250ZW50V2luZG93LnBvc3RNZXNzYWdlKHtcbiAgICAgICAgICAgICAgICB0eXBlOiAncmVxdWVzdF9pZnJhbWVfaGVpZ2h0J1xuICAgICAgICAgICAgfSwgJyonKTtcbiAgICAgICAgfTtcblxuICAgICAgICAvKipcbiAgICAgICAgICogUG9zdCBtZXNzYWdlIHJlc3BvbnNlIHByb2Nlc3NpbmcgZm9yIGlmcmFtZSBoZWlnaHQuXG4gICAgICAgICAqIEBwYXJhbSBlXG4gICAgICAgICAqL1xuICAgICAgICBjb25zdCByZXNwb25zZUlmcmFtZUhlaWdodCA9IGUgPT4ge1xuICAgICAgICAgICAgaWYgKGUuZGF0YS50eXBlID09PSAncmVzcG9uc2VfaWZyYW1lX2hlaWdodCcpIHtcbiAgICAgICAgICAgICAgICAkY29ubmVjdGlvbklGcmFtZS5jc3MoeydoZWlnaHQnOiBlLmRhdGEuaGVpZ2h0LnRvU3RyaW5nKCkgKyAncHgnfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH07XG5cbiAgICAgICAgLy8gLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tXG4gICAgICAgIC8vIElOSVRJQUxJWkFUSU9OXG4gICAgICAgIC8vIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLVxuXG4gICAgICAgIC8qKlxuICAgICAgICAgKiBJbml0aWFsaXplIG1ldGhvZCBvZiB0aGUgd2lkZ2V0LCBjYWxsZWQgYnkgdGhlIGVuZ2luZS5cbiAgICAgICAgICovXG4gICAgICAgIG1vZHVsZS5pbml0ID0gZnVuY3Rpb24gKGRvbmUpIHtcbiAgICAgICAgICAgIHZhbGlkYXRlT3B0aW9ucygpO1xuXG4gICAgICAgICAgICB3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcignbWVzc2FnZScsIHJlc3BvbnNlSWZyYW1lSGVpZ2h0LCBmYWxzZSk7XG4gICAgICAgICAgICByZW5kZXJJZnJhbWUoKTtcblxuICAgICAgICAgICAgZG9uZSgpO1xuICAgICAgICB9O1xuXG4gICAgICAgIHJldHVybiBtb2R1bGU7XG4gICAgfSk7XG4iXX0=
