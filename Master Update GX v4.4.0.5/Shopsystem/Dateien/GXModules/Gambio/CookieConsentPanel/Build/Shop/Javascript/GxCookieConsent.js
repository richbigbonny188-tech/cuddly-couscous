'use strict';

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

(function () {

	var eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent',
	    messageEvent = eventMethod === 'attachEvent' ? 'onmessage' : 'message',
	    eventer = window[eventMethod];

	var handleOptIn = function handleOptIn(event) {

		var eventDataContains = function eventDataContains(str) {
			return JSON.stringify(event.data).indexOf(str) !== -1;
		};

		var storeOilDataInCookie = function storeOilDataInCookie(data) {

			var cookieDate = new Date();
			//  the oil.js cookie expires after 1 month
			cookieDate.setMonth(cookieDate.getMonth() + 1);

			var cookieString = 'GXConsents=' + JSON.stringify(data) + ';';
			cookieString += 'expires=' + cookieDate.toUTCString() + ';';
			cookieString += 'path=/;SameSite=Lax;';
			document.cookie = cookieString;
		};

		if (event && event.data && (eventDataContains('oil_optin_done') || eventDataContains('oil_has_optedin'))) {

			// [8, 12, 28] enables vendor ids in the response
			__cmp('getVendorConsents', [8, 12, 28], storeOilDataInCookie);
		}
	};

	eventer(messageEvent, handleOptIn, false);

	$(document).on('click', '[trigger-cookie-consent-panel]', function () {

		window.AS_OIL.showPreferenceCenter();

		if (!$('.as-oil.light').length) {
			$('body').append($('<div/>').addClass('as-oil light').append($('<div/>').attr('id', 'oil-preference-center').addClass('as-oil-content-overlay cpc-dynamic-panel')));
		}
	});
})();
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIlNob3AvSmF2YXNjcmlwdC9HeENvb2tpZUNvbnNlbnQuanMiXSwibmFtZXMiOlsiZXZlbnRNZXRob2QiLCJ3aW5kb3ciLCJhZGRFdmVudExpc3RlbmVyIiwibWVzc2FnZUV2ZW50IiwiZXZlbnRlciIsImhhbmRsZU9wdEluIiwiZXZlbnQiLCJldmVudERhdGFDb250YWlucyIsInN0ciIsIkpTT04iLCJzdHJpbmdpZnkiLCJkYXRhIiwiaW5kZXhPZiIsInN0b3JlT2lsRGF0YUluQ29va2llIiwiY29va2llRGF0ZSIsIkRhdGUiLCJzZXRNb250aCIsImdldE1vbnRoIiwiY29va2llU3RyaW5nIiwidG9VVENTdHJpbmciLCJkb2N1bWVudCIsImNvb2tpZSIsIl9fY21wIiwiJCIsIm9uIiwiQVNfT0lMIiwic2hvd1ByZWZlcmVuY2VDZW50ZXIiLCJsZW5ndGgiLCJhcHBlbmQiLCJhZGRDbGFzcyIsImF0dHIiXSwibWFwcGluZ3MiOiI7O0FBQUE7Ozs7Ozs7OztBQVNBOzs7O0FBSUEsQ0FBQyxZQUFXOztBQUVYLEtBQUlBLGNBQWNDLE9BQU9DLGdCQUFQLEdBQTBCLGtCQUExQixHQUErQyxhQUFqRTtBQUFBLEtBQ0NDLGVBQWVILGdCQUFnQixhQUFoQixHQUFnQyxXQUFoQyxHQUE4QyxTQUQ5RDtBQUFBLEtBRUNJLFVBQVVILE9BQU9ELFdBQVAsQ0FGWDs7QUFJQSxLQUFJSyxjQUFjLFNBQWRBLFdBQWMsQ0FBU0MsS0FBVCxFQUFnQjs7QUFFakMsTUFBSUMsb0JBQW9CLFNBQXBCQSxpQkFBb0IsQ0FBU0MsR0FBVCxFQUFjO0FBQ3JDLFVBQU9DLEtBQUtDLFNBQUwsQ0FBZUosTUFBTUssSUFBckIsRUFBMkJDLE9BQTNCLENBQW1DSixHQUFuQyxNQUE0QyxDQUFDLENBQXBEO0FBQ0EsR0FGRDs7QUFJQSxNQUFJSyx1QkFBdUIsU0FBdkJBLG9CQUF1QixDQUFTRixJQUFULEVBQWU7O0FBRXpDLE9BQUlHLGFBQWEsSUFBSUMsSUFBSixFQUFqQjtBQUNBO0FBQ0FELGNBQVdFLFFBQVgsQ0FBb0JGLFdBQVdHLFFBQVgsS0FBd0IsQ0FBNUM7O0FBRUEsT0FBSUMsZUFBZSxnQkFBZ0JULEtBQUtDLFNBQUwsQ0FBZUMsSUFBZixDQUFoQixHQUF1QyxHQUExRDtBQUNBTyxtQkFBZ0IsYUFBYUosV0FBV0ssV0FBWCxFQUFiLEdBQXdDLEdBQXhEO0FBQ0FELG1CQUFnQixzQkFBaEI7QUFDQUUsWUFBU0MsTUFBVCxHQUFrQkgsWUFBbEI7QUFDQSxHQVZEOztBQVlBLE1BQUlaLFNBQVNBLE1BQU1LLElBQWYsS0FBd0JKLGtCQUFrQixnQkFBbEIsS0FBdUNBLGtCQUFrQixpQkFBbEIsQ0FBL0QsQ0FBSixFQUEwRzs7QUFFekc7QUFDQWUsU0FBTSxtQkFBTixFQUEyQixDQUFDLENBQUQsRUFBSSxFQUFKLEVBQVEsRUFBUixDQUEzQixFQUF3Q1Qsb0JBQXhDO0FBQ0E7QUFDRCxFQXZCRDs7QUF5QkFULFNBQVFELFlBQVIsRUFBc0JFLFdBQXRCLEVBQW1DLEtBQW5DOztBQUVBa0IsR0FBRUgsUUFBRixFQUFZSSxFQUFaLENBQWUsT0FBZixFQUF3QixnQ0FBeEIsRUFBMkQsWUFBWTs7QUFFdEV2QixTQUFPd0IsTUFBUCxDQUFjQyxvQkFBZDs7QUFFQSxNQUFJLENBQUNILEVBQUUsZUFBRixFQUFtQkksTUFBeEIsRUFBZ0M7QUFDL0JKLEtBQUUsTUFBRixFQUFVSyxNQUFWLENBQ0NMLEVBQUUsUUFBRixFQUNFTSxRQURGLENBQ1csY0FEWCxFQUVFRCxNQUZGLENBR0VMLEVBQUUsUUFBRixFQUNFTyxJQURGLENBQ08sSUFEUCxFQUNhLHVCQURiLEVBRUVELFFBRkYsQ0FFVywwQ0FGWCxDQUhGLENBREQ7QUFTQTtBQUNELEVBZkQ7QUFnQkEsQ0FqREQiLCJmaWxlIjoiU2hvcC9KYXZhc2NyaXB0L0d4Q29va2llQ29uc2VudC5qcyIsInNvdXJjZXNDb250ZW50IjpbIi8qIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tXG4gIEd4Q29va2llQ29uc2VudC5qcyAyMDE5LTEyLTE5XG4gIEdhbWJpbyBHbWJIXG4gIGh0dHA6Ly93d3cuZ2FtYmlvLmRlXG4gIENvcHlyaWdodCAoYykgMjAxOSBHYW1iaW8gR21iSFxuICBSZWxlYXNlZCB1bmRlciB0aGUgR05VIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgKFZlcnNpb24gMilcbiAgW2h0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy9ncGwtMi4wLmh0bWxdXG4gIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKi9cblxuLyogIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tXG4gKiAgRXZlbnQgbGlzdGVuZXIgZm9yIHN0b3JpbmcgY29va2llIHNlbGVjdGlvbiBpbiBHWENvbnNlbnRzXG4gKiAgLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0gKi9cblxuKGZ1bmN0aW9uKCkge1xuXHRcblx0bGV0IGV2ZW50TWV0aG9kID0gd2luZG93LmFkZEV2ZW50TGlzdGVuZXIgPyAnYWRkRXZlbnRMaXN0ZW5lcicgOiAnYXR0YWNoRXZlbnQnLFxuXHRcdG1lc3NhZ2VFdmVudCA9IGV2ZW50TWV0aG9kID09PSAnYXR0YWNoRXZlbnQnID8gJ29ubWVzc2FnZScgOiAnbWVzc2FnZScsXG5cdFx0ZXZlbnRlciA9IHdpbmRvd1tldmVudE1ldGhvZF07XG5cdFxuXHRsZXQgaGFuZGxlT3B0SW4gPSBmdW5jdGlvbihldmVudCkge1xuXHRcdFxuXHRcdGxldCBldmVudERhdGFDb250YWlucyA9IGZ1bmN0aW9uKHN0cikge1xuXHRcdFx0cmV0dXJuIEpTT04uc3RyaW5naWZ5KGV2ZW50LmRhdGEpLmluZGV4T2Yoc3RyKSAhPT0gLTE7XG5cdFx0fVxuXHRcdFxuXHRcdGxldCBzdG9yZU9pbERhdGFJbkNvb2tpZSA9IGZ1bmN0aW9uKGRhdGEpIHtcblx0XHRcdFxuXHRcdFx0bGV0IGNvb2tpZURhdGUgPSBuZXcgRGF0ZTtcblx0XHRcdC8vICB0aGUgb2lsLmpzIGNvb2tpZSBleHBpcmVzIGFmdGVyIDEgbW9udGhcblx0XHRcdGNvb2tpZURhdGUuc2V0TW9udGgoY29va2llRGF0ZS5nZXRNb250aCgpICsgMSk7XG5cdFx0XHRcblx0XHRcdGxldCBjb29raWVTdHJpbmcgPSAnR1hDb25zZW50cz0nICsgSlNPTi5zdHJpbmdpZnkoZGF0YSkgKyAnOyc7XG5cdFx0XHRjb29raWVTdHJpbmcgKz0gJ2V4cGlyZXM9JyArIGNvb2tpZURhdGUudG9VVENTdHJpbmcoKSArICc7Jztcblx0XHRcdGNvb2tpZVN0cmluZyArPSAncGF0aD0vO1NhbWVTaXRlPUxheDsnXG5cdFx0XHRkb2N1bWVudC5jb29raWUgPSBjb29raWVTdHJpbmc7XG5cdFx0fTtcblx0XHRcblx0XHRpZiAoZXZlbnQgJiYgZXZlbnQuZGF0YSAmJiAoZXZlbnREYXRhQ29udGFpbnMoJ29pbF9vcHRpbl9kb25lJykgfHwgZXZlbnREYXRhQ29udGFpbnMoJ29pbF9oYXNfb3B0ZWRpbicpKSkge1xuXHRcdFx0XG5cdFx0XHQvLyBbOCwgMTIsIDI4XSBlbmFibGVzIHZlbmRvciBpZHMgaW4gdGhlIHJlc3BvbnNlXG5cdFx0XHRfX2NtcCgnZ2V0VmVuZG9yQ29uc2VudHMnLCBbOCwgMTIsIDI4XSwgc3RvcmVPaWxEYXRhSW5Db29raWUpO1xuXHRcdH1cblx0fVxuXHRcblx0ZXZlbnRlcihtZXNzYWdlRXZlbnQsIGhhbmRsZU9wdEluLCBmYWxzZSk7XG5cdFxuXHQkKGRvY3VtZW50KS5vbignY2xpY2snLCAnW3RyaWdnZXItY29va2llLWNvbnNlbnQtcGFuZWxdJywgIGZ1bmN0aW9uICgpIHtcblx0XG5cdFx0d2luZG93LkFTX09JTC5zaG93UHJlZmVyZW5jZUNlbnRlcigpO1xuXG5cdFx0aWYgKCEkKCcuYXMtb2lsLmxpZ2h0JykubGVuZ3RoKSB7XG5cdFx0XHQkKCdib2R5JykuYXBwZW5kKFxuXHRcdFx0XHQkKCc8ZGl2Lz4nKVxuXHRcdFx0XHRcdC5hZGRDbGFzcygnYXMtb2lsIGxpZ2h0Jylcblx0XHRcdFx0XHQuYXBwZW5kKFxuXHRcdFx0XHRcdFx0JCgnPGRpdi8+Jylcblx0XHRcdFx0XHRcdFx0LmF0dHIoJ2lkJywgJ29pbC1wcmVmZXJlbmNlLWNlbnRlcicpXG5cdFx0XHRcdFx0XHRcdC5hZGRDbGFzcygnYXMtb2lsLWNvbnRlbnQtb3ZlcmxheSBjcGMtZHluYW1pYy1wYW5lbCcpXG5cdFx0XHRcdFx0KVxuXHRcdFx0KTtcblx0XHR9XG5cdH0pO1xufSkoKTtcbiJdfQ==
