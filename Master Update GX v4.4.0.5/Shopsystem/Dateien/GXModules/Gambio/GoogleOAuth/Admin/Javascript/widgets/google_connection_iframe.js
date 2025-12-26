/* --------------------------------------------------------------
 google_connection_iframe.js 2018-05-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2018 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

gx_google_oauth.widgets.module(
    'google_connection_iframe',

    [],

    function (data) {

        'use strict';

        // ------------------------------------------------------------------------
        // VARIABLE DEFINITION
        // ------------------------------------------------------------------------

        /**
         * Widget Reference
         *
         * @type {object}
         */
        const $this = $(this);

        /**
         * Default Options for Widget
         *
         * @type {object}
         */
        const defaults = {
            connected: false
        };

        /**
         * Final Widget Options
         *
         * @type {object}
         */
        const options = $.extend(true, {}, defaults, data);

        /**
         * Module Object
         *
         * @type {object}
         */
        const module = {};

        /**
         * CSS styles for manually rendered iframe.
         * @type {{width: string, border: string}}
         */
        const iFrameStyles = {
            width: '100%',
            border: 'none',
            height: '290px',
            'min-height': '190px'
        };

        /**
         * Manually rendered iframe element.
         * Member is available after ::renderIframe function execution.
         */
        let $connectionIFrame;

        // ------------------------------------------------------------------------
        // FUNCTIONALITY
        // ------------------------------------------------------------------------
        /**
         * Validates widgets and check that all required options exists.
         */
        const validateOptions = () => {
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
        const optionExist = option => {
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
        const renderIframe = () => {
            let iFrameUrl = options.url + '?origin=' + options.origin + '&from=' + options.from + '&version='
                + options.version + '&language=' + options.language + '&error=' + options.error;

            let connected = options.connected === 1;

            if (connected) {
                iFrameUrl = iFrameUrl + '&connected=true';
            }

            $connectionIFrame = $('<iframe/>')
                .attr('src', '../GXModules/Gambio/GoogleOAuth/Admin/Html/initial_iframe_content.html')
                .on('load', requestIframeHeight)
                .css(iFrameStyles);
            $this.parent().empty().append($connectionIFrame);
            $connectionIFrame.attr('src', iFrameUrl);
        };

        /**
         * Requests the iframe height via post message.
         * @param e
         */
        const requestIframeHeight = e => {
            e.target.contentWindow.postMessage({
                type: 'request_iframe_height'
            }, '*');
        };

        /**
         * Post message response processing for iframe height.
         * @param e
         */
        const responseIframeHeight = e => {
            if (e.data.type === 'response_iframe_height') {
                $connectionIFrame.css({'height': e.data.height.toString() + 'px'});
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
