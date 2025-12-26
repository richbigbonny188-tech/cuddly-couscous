/* --------------------------------------------------------------
  GxCookieConsent.js 2019-12-19
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/*  --------------------------------------------------------------
 *  Event listener for storing cookie selection in GXConsents
 *  -------------------------------------------------------------- */

(function() {
	
	let eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent',
		messageEvent = eventMethod === 'attachEvent' ? 'onmessage' : 'message',
		eventer = window[eventMethod];
	
	let handleOptIn = function(event) {
		
		let eventDataContains = function(str) {
			return JSON.stringify(event.data).indexOf(str) !== -1;
		}
		
		let storeOilDataInCookie = function(data) {
			
			let cookieDate = new Date;
			//  the oil.js cookie expires after 1 month
			cookieDate.setMonth(cookieDate.getMonth() + 1);
			
			let cookieString = 'GXConsents=' + JSON.stringify(data) + ';';
			cookieString += 'expires=' + cookieDate.toUTCString() + ';';
			cookieString += 'path=/;SameSite=Lax;'
			document.cookie = cookieString;
		};
		
		if (event && event.data && (eventDataContains('oil_optin_done') || eventDataContains('oil_has_optedin'))) {
			
			// [8, 12, 28] enables vendor ids in the response
			__cmp('getVendorConsents', [8, 12, 28], storeOilDataInCookie);
		}
	}
	
	eventer(messageEvent, handleOptIn, false);
	
	$(document).on('click', '[trigger-cookie-consent-panel]',  function () {
	
		window.AS_OIL.showPreferenceCenter();

		if (!$('.as-oil.light').length) {
			$('body').append(
				$('<div/>')
					.addClass('as-oil light')
					.append(
						$('<div/>')
							.attr('id', 'oil-preference-center')
							.addClass('as-oil-content-overlay cpc-dynamic-panel')
					)
			);
		}
	});
})();
