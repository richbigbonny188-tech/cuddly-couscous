<?php
/* --------------------------------------------------------------
  AutoUpdatePermissionChecker.inc.php 2018-06-27
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2018 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
 */

class AutoUpdatePermissionChecker
{
	/**
	 * Corrects the file permission of all files that had been deployed by the auto updater.
	 *
	 * @param array $files
	 * @param array $directories
	 */
	public static function correctFilePermissions(array &$files, array &$directories)
	{
		$permission = self::determineCorrectFilePermission();
		
		$files       = array(
			// AutoUpdater
			DIR_FS_CATALOG . 'admin/html/assets/javascript/engine/compatibility/update_downloader.js',
			DIR_FS_CATALOG . 'admin/html/assets/javascript/engine/compatibility/update_downloader.min.js',
			DIR_FS_CATALOG . 'admin/html/assets/styles/legacy/update_downloader.css',
			DIR_FS_CATALOG . 'admin/html/assets/styles/legacy/update_downloader.min.css',
			DIR_FS_CATALOG . 'admin/javascript/engine/compatibility/update_downloader.js',
			DIR_FS_CATALOG . 'admin/styles/legacy/update_downloader.css',
			DIR_FS_CATALOG . 'GXMainComponents/Controllers/HttpView/Admin/AutoUpdaterController.inc.php',
			DIR_FS_CATALOG . 'lang/english/original_sections/admin/update_downloader.lang.inc.php',
			DIR_FS_CATALOG . 'lang/german/original_sections/admin/update_downloader.lang.inc.php',
			DIR_FS_CATALOG . 'system/classes/security/SecurityCheck.inc.php',
			
			// Hub Connector
			DIR_FS_CATALOG . 'gambio_hub_callback.php',
			DIR_FS_CATALOG . 'admin/gambio_hub.php',
			DIR_FS_CATALOG . 'admin/html/content/layouts/main/footer/hub_state.html',
			DIR_FS_CATALOG . 'GXModules/Gambio/Hub/gambio_hub_callback.php',
			DIR_FS_CATALOG . 'inc/get_transfer_charge_text.inc.php',
			DIR_FS_CATALOG . 'includes/modules/payment/gambio_hub.php',
			DIR_FS_CATALOG . 'lang/english/original_sections/admin/admin_general.gambio_hub.lang.inc.php',
			DIR_FS_CATALOG . 'lang/english/original_sections/admin/menu/admin_menu.gambio_hub.lang.inc.php',
			DIR_FS_CATALOG . 'lang/english/original_sections/admin/update_downloader.lang.inc.php',
			DIR_FS_CATALOG . 'lang/english/original_sections/modules/payment/gambio_hub.lang.inc.php',
			DIR_FS_CATALOG . 'lang/german/original_sections/admin/admin_general.gambio_hub.lang.inc.php',
			DIR_FS_CATALOG . 'lang/german/original_sections/admin/menu/admin_menu.gambio_hub.lang.inc.php',
			DIR_FS_CATALOG . 'lang/german/original_sections/admin/update_downloader.lang.inc.php',
			DIR_FS_CATALOG . 'lang/german/original_sections/modules/payment/gambio_hub.lang.inc.php',
			DIR_FS_CATALOG . 'system/conf/admin_menu/menu_hub.xml',
			DIR_FS_CATALOG . 'system/overloads/AccountHistoryInfoContentView/GambioHubAccountHistoryInfo.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminApplicationBottomExtenderComponent/GambioHubApplicationBottomExtender.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminApplicationBottomExtenderComponent/KlarnaHubApplicationBottomExtender.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminApplicationTopPrimalExtenderComponent/GambioHubAdminApplicationTopPrimalExtenderComponent.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminApplicationTopPrimalExtenderComponent/KlarnaHubAdminApplicationTopPrimalExtenderComponent.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminLayoutHttpControllerResponse/GambioHubAdminLayoutHttpControllerResponse.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminMenuContentView/GambioHubAdminMenuContentView.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminMenuControl/GambioHubAdminMenuControl.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminMenuControl/MoneyOrderPlusHubAdminMenuControl.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminOrderOverviewExtenderComponent/GambioHubAdminOrderOverviewExtender.inc.php',
			DIR_FS_CATALOG . 'system/overloads/ApiV2Authenticator/ApiV2HubAuthenticator.inc.php',
			DIR_FS_CATALOG . 'system/overloads/ApplicationTopExtenderComponent/GambioHubApplicationTopExtender.inc.php',
			DIR_FS_CATALOG . 'system/overloads/ApplicationTopPrimalExtenderComponent/GambioHubApplicationTopPrimalExtenderComponent.inc.php',
			DIR_FS_CATALOG . 'system/overloads/CartController/GambioHubCartController.inc.php',
			DIR_FS_CATALOG . 'system/overloads/CheckoutConfirmationContentControl/GambioHubCheckoutConfirmationContentControl.inc.php',
			DIR_FS_CATALOG . 'system/overloads/CheckoutConfirmationContentView/GambioHubCheckoutConfirmation.inc.php',
			DIR_FS_CATALOG . 'system/overloads/CheckoutPaymentContentControl/GambioHubStartSession.inc.php',
			DIR_FS_CATALOG . 'system/overloads/CheckoutPaymentModulesContentView/GambioHubPaymentSelection.inc.php',
			DIR_FS_CATALOG . 'system/overloads/CheckoutShippingContentControl/GambioHubStartSession.inc.php',
			DIR_FS_CATALOG . 'system/overloads/ConfigurationBoxContentView/GambioHubConfigurationBoxContentView.inc.php',
			DIR_FS_CATALOG . 'system/overloads/Countries/KlarnaHubCountries.inc.php',
			DIR_FS_CATALOG . 'system/overloads/DashboardController/GambioHubDashboardController.inc.php',
			DIR_FS_CATALOG . 'system/overloads/EnvironmentClassFinderSettings/GambioHubEnvironmentClassFinderSettings.inc.php',
			DIR_FS_CATALOG . 'system/overloads/GiftCartContentView/GambioHubGiftCartContentView.inc.php',
			DIR_FS_CATALOG . 'system/overloads/GMModuleManager/GambioHubGMModuleManager.inc.php',
			DIR_FS_CATALOG . 'system/overloads/HttpContext/HttpContextServerData.inc.php',
			DIR_FS_CATALOG . 'system/overloads/HttpContextReader/HttpContextReaderServerData.inc.php',
			DIR_FS_CATALOG . 'system/overloads/InvoiceListGenerator/GambioHubInvoiceListGenerator.inc.php',
			DIR_FS_CATALOG . 'system/overloads/InvoicesOverviewAjaxController/GambioHubInvoicesOverviewAjaxController.inc.php',
			DIR_FS_CATALOG . 'system/overloads/InvoicesOverviewColumns/GambioHubInvoicesOverviewColumns.inc.php',
			DIR_FS_CATALOG . 'system/overloads/JsonHttpControllerResponse/JsonHttpControllerResponseServerData.inc.php',
			DIR_FS_CATALOG . 'system/overloads/LanguageTextManager/GambioHubLanguageTextManager.inc.php',
			DIR_FS_CATALOG . 'system/overloads/LogoffContentControl/GambioHubLogoff.inc.php',
			DIR_FS_CATALOG . 'system/overloads/order_total/GambioHubOrderTotal.inc.php',
			DIR_FS_CATALOG . 'system/overloads/OrderExtenderComponent/GambioHubOrderExtender.inc.php',
			DIR_FS_CATALOG . 'system/overloads/OrderExtenderComponent/KlarnaHubOrderExtender.inc.php',
			DIR_FS_CATALOG . 'system/overloads/OrderListGenerator/GambioHubOrderListGenerator.inc.php',
			DIR_FS_CATALOG . 'system/overloads/OrderRepositoryReader/GambioHubOrderRepositoryReader.inc.php',
			DIR_FS_CATALOG . 'system/overloads/OrdersOverviewAjaxController/GambioHubOrdersOverviewAjaxController.inc.php',
			DIR_FS_CATALOG . 'system/overloads/OrdersOverviewColumns/GambioHubOrdersOverviewColumns.inc.php',
			DIR_FS_CATALOG . 'system/overloads/OrdersOverviewController/KlarnaHubOrdersOverviewController.inc.php',
			DIR_FS_CATALOG . 'system/overloads/ot_cod_fee/GambioHubOtCodFee.inc.php',
			DIR_FS_CATALOG . 'system/overloads/ot_gv/GambioHubOtGv.inc.php',
			DIR_FS_CATALOG . 'system/overloads/ot_payment/GambioHubOtPayment.inc.php',
			DIR_FS_CATALOG . 'system/overloads/ot_total_netto/GambioHubOtTotalNetto.inc.php',
			DIR_FS_CATALOG . 'system/overloads/payment/GambioHubPayment.inc.php',
			DIR_FS_CATALOG . 'system/overloads/PDFOrderExtenderComponent/GambioHubInvoicePackingslipExtender.inc.php',
			DIR_FS_CATALOG . 'system/overloads/PDFOrderExtenderComponent/KlarnaHubPdfOrderExtender.inc.php',
			DIR_FS_CATALOG . 'system/overloads/PopupContentContentView/KlarnaHubPopupContentContentView.inc.php',
			DIR_FS_CATALOG . 'system/overloads/PrintOrderContentView/GambioHubPrintOrder.inc.php',
			DIR_FS_CATALOG . 'system/overloads/SendOrderContentView/GambioHubSendOrder.inc.php',
			DIR_FS_CATALOG . 'system/overloads/ShopContentContentView/KlarnaHubShopContentContentView.inc.php',
			DIR_FS_CATALOG . 'system/overloads/shoppingCart/GambioHubShoppingCart.inc.php',
			DIR_FS_CATALOG . 'templates/EyeCandy/usermod/css/gambio_hub_paypal.css',
			DIR_FS_CATALOG . 'templates/Honeygrid/usermod/css/gambio_hub_paypal.css',
			
			// Google Services
			DIR_FS_CATALOG . 'admin/html/assets/javascript/engine/libs/adwords_overview_columns.js',
			DIR_FS_CATALOG . 'admin/html/assets/javascript/engine/libs/adwords_overview_columns.min.js',
			DIR_FS_CATALOG . 'admin/html/assets/styles/modules/google_services/campaigns_overview.css.map',
			DIR_FS_CATALOG . 'admin/html/content/hub_connector_updater/overview.html',
			DIR_FS_CATALOG . 'admin/html/content/layouts/main/footer/footer.html',
			DIR_FS_CATALOG . 'admin/includes/google_shopping_default_fields.inc.php',
			DIR_FS_CATALOG . 'admin/javascript/engine/controllers/layouts/main/menu/menu.js',
			DIR_FS_CATALOG . 'admin/javascript/engine/libs/adwords_overview_columns.js',
			DIR_FS_CATALOG . 'admin/javascript/engine/widgets/switcher.js',
			DIR_FS_CATALOG . 'admin/styles/admin/javascript/_datepicker.scss',
			DIR_FS_CATALOG . 'admin/styles/admin/javascript/_daterangepicker.scss',
			DIR_FS_CATALOG . 'admin/styles/admin/layouts/main/footer/_version.scss',
			DIR_FS_CATALOG . 'GXModules/Gambio/Hub/Admin/Javascript/extenders/footer_hub_state.js',
			DIR_FS_CATALOG . 'includes/classes/xtcPrice.php',
			DIR_FS_CATALOG . 'lang/english/original_sections/admin/menu/admin_menu.google_services.lang.inc.php',
			DIR_FS_CATALOG . 'lang/german/original_sections/admin/menu/admin_menu.google_services.lang.inc.php',
			DIR_FS_CATALOG . 'system/classes/csv/CSVAjaxHandler.inc.php',
			DIR_FS_CATALOG . 'system/classes/csv/CSVContentView.inc.php',
			DIR_FS_CATALOG . 'system/classes/csv/CSVControl.inc.php',
			DIR_FS_CATALOG . 'system/classes/csv/CSVFunctionLibrary.inc.php',
			DIR_FS_CATALOG . 'system/classes/csv/CSVImportFunctionLibrary.inc.php',
			DIR_FS_CATALOG . 'system/classes/csv/CSVSchemeModel.inc.php',
			DIR_FS_CATALOG . 'system/classes/csv/CSVSource.php',
			DIR_FS_CATALOG . 'system/conf/admin_menu/menu_google.xml',
			DIR_FS_CATALOG . 'system/overloads/AdminLayoutHttpControllerResponse/GoogleAdminLayoutHttpControllerResponse.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminMenuContentView/GoogleAdwordsAdminMenuContentView.inc.php',
			DIR_FS_CATALOG . 'system/overloads/AdminMenuContentView/index.html',
			DIR_FS_CATALOG . 'system/overloads/EnvironmentClassFinderSettings/GoogleServicesEnvironmentClassFinderSettings.inc.php',
		);
		$directories = array();
		
		// AutoUpdater
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/html/content/update_downloader/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'GXMainComponents/Extensions/UpdateDownloader/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'GXModules/Gambio/UpdateDownloader/', $files, $directories);
		
		// Ovisto
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'GXModules/Gambio/Ovisto/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'GXModules/Gambio/Sunnycash/', $files, $directories);
		
		// Hub Connector
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/html/assets/javascript/modules/gambio_hub/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/html/assets/styles/modules/gambio_hub/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/html/content/hub/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'lang/english/original_sections/gambio_hub/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'lang/german/original_sections/gambio_hub/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'system/classes/gambio_hub/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'vendor/gambio-hub/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'GXModules/Gambio/Hub/', $files, $directories);
		
		// Google Services
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/html/assets/images/modules/google_services/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG
		                              . 'admin/html/assets/javascript/engine/controllers/google_services/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/html/assets/javascript/modules/google_services/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/html/assets/styles/modules/google_services/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/html/content/google_services/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/javascript/engine/controllers/google_services/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/javascript/modules/google_services/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'admin/styles/modules/google_services/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'GXModules/Gambio/Google/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'GXModules/Gambio/GoogleAdWords/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'GXModules/Gambio/GoogleOAuth/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'GXModules/Gambio/GoogleShopping/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'lang/english/original_sections/google_services/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'lang/german/original_sections/google_services/', $files,
		                              $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'system/classes/google_services/', $files, $directories);
		
		// General files
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'gambio_updater/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'templates/EyeCandy/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'templates/Honeygrid/', $files, $directories);
		self::loadFilesAndDirectories(DIR_FS_CATALOG . 'version_info/', $files, $directories);
		
		self::correctPermisisons(array_merge($files, $directories), $permission);
	}
	
	
	/**
	 * @param string $path
	 * @param array  $files
	 * @param array  $directories
	 */
	protected static function loadFilesAndDirectories($path, array &$files, array &$directories)
	{
		if(is_dir($path))
		{
			$directories[] = $path;
			$globFiles     = glob($path . '*', GLOB_MARK);
			if($globFiles !== false && count($globFiles) > 0)
			{
				foreach($globFiles as $globFile)
				{
					if(substr($globFile, -1) === DIRECTORY_SEPARATOR)
					{
						self::loadFilesAndDirectories($globFile, $files, $directories);
					}
					else
					{
						$files[] = $globFile;
					}
				}
			}
		}
	}
	
	
	/**
	 * @param array $files
	 * @param int   $permission
	 */
	protected static function correctPermisisons(array $files, $permission = 0777)
	{
		if(count($files) > 0)
		{
			foreach($files as $file)
			{
				if(file_exists($file) && substr(sprintf('%o', fileperms($file)), -4) != $permission)
				{
					@chmod($file, $permission);
				}
			}
		}
	}
	
	
	/**
	 * @return int
	 */
	protected static function determineCorrectFilePermission()
	{
		if(is_writeable(DIR_FS_CATALOG . 'export'))
		{
			$file = @fopen(DIR_FS_CATALOG . 'export/permission-test.php', 'w');
			@fwrite($file, '<?php echo "test ok";');
			@fclose($file);
			@chmod(DIR_FS_CATALOG . 'export/permission-test.php', 0777);
			
			$file = @fopen(DIR_FS_CATALOG . 'export/permission-test2.php', 'w');
			@fclose($file);
			@chmod(DIR_FS_CATALOG . 'export/permission-test2.php', 0755);
			
			$curlHandle = @curl_init();
			@curl_setopt_array($curlHandle, array(
				CURLOPT_URL            => HTTP_SERVER . DIR_WS_CATALOG . 'export/permission-test.php',
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_RETURNTRANSFER => true,
			));
			$response = @curl_exec($curlHandle);
			$header   = @curl_getinfo($curlHandle);
			@curl_close($curlHandle);
			@unlink(DIR_FS_CATALOG . 'export/permission-test.php');
			
			if(((isset($header['http_code']) && $header['http_code'] !== 200) || $response != 'test ok')
			   && is_writeable(DIR_FS_CATALOG . 'export/permission-test2.php'))
			{
                @unlink(DIR_FS_CATALOG . 'export/permission-test2.php');
                return 0755;
			}
		}
        @unlink(DIR_FS_CATALOG . 'export/permission-test2.php');
        
        return 0777;
	}
}