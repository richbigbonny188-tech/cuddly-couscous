<?php
/* --------------------------------------------------------------
   OrdersOverviewController.inc.php 2020-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceReadService;
use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\PermissionService;
use Gambio\Core\AdminAccess\Role\PermissionAction;

MainFactory::load_class('AdminHttpViewController');

/**
 * Class OrdersOverviewController
 *
 * Bootstraps the Orders overview page.
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class OrdersOverviewController extends AdminHttpViewController
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var OrderWriteService
     */
    protected $orderWriteService;
    
    /**
     * @var OrderReadService
     */
    protected $orderReadService;
    
    /**
     * @var OrderObjectService
     */
    protected $orderObjectService;
    
    /**
     * @var UserConfigurationService
     */
    protected $userConfigurationService;
    
    /**
     * @var OrderStatusStyles
     */
    protected $orderStatusStyles;
    
    /**
     * @var OrdersOverviewColumns
     */
    protected $ordersOverviewColumns;
    
    
    /**
     * Initialize Controller
     */
    public function init()
    {
        $this->db                       = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $this->orderWriteService        = StaticGXCoreLoader::getService('OrderWrite');
        $this->orderReadService         = StaticGXCoreLoader::getService('OrderRead');
        $this->orderObjectService       = StaticGXCoreLoader::getService('OrderObject');
        $this->userConfigurationService = StaticGXCoreLoader::getService('UserConfiguration');
        $this->orderStatusStyles        = MainFactory::create('OrderStatusStyles', $this->db);
        $this->ordersOverviewColumns    = MainFactory::create('OrdersOverviewColumns');
    }
    
    
    /**
     * Default Action
     *
     * Render the main order page.
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function actionDefault()
    {
        $languageTextManager = MainFactory::create('LanguageTextManager', 'admin_orders', $_SESSION['languages_id']);
        $title               = new NonEmptyStringType($languageTextManager->get_text('PAGE_TITLE'));
        $template            = new ExistingFile(new NonEmptyStringType(DIR_FS_ADMIN
                                                                       . '/html/content/orders/overview.html'));
        
        // Fetch the template data.
        $customerId = new IdType((int)$_SESSION['customer_id']);
        
        $pageLength = $this->userConfigurationService->getUserConfiguration($customerId, 'ordersOverviewPageLength');
        
        $defaultColumns = [
            'number',
            'customer',
            'group',
            'sum',
            'paymentMethod',
            'shippingMethod',
            'countryIsoCode',
            'date',
            'status',
            'totalWeight'
        ];
        $activeColumns  = $this->userConfigurationService->getUserConfiguration($customerId,
                                                                                'ordersOverviewSettingsColumns');
        if (empty($activeColumns)) {
            $activeColumns = [];
            /** @var DataTableColumn $dataTableColumn */
            foreach ($this->ordersOverviewColumns->getColumns()->getArray() as $dataTableColumn) {
                $columnName = $dataTableColumn->getName();
                if (in_array($columnName, $defaultColumns, true)) {
                    $activeColumns[] = $dataTableColumn->getName();
                }
            }
            
            $activeColumns = json_encode($activeColumns);
        } else {
            $activeColumns = str_replace('\\',
                                         '',
                                         $activeColumns); // User configuration service escapes the double quotes.
        }
        
        $activeRowHeight = $this->userConfigurationService->getUserConfiguration($customerId,
                                                                                 'ordersOverviewSettingsRowHeight');
        $displayTooltips = $this->userConfigurationService->getUserConfiguration($customerId,
                                                                                 'ordersOverviewSettingsDisplayTooltips');
        
        // Admin Access Service
        /** @var PermissionService $adminAccessService */
        $adminAccessService = LegacyDependencyContainer::getInstance()->get(PermissionService::class);
        
        $data = MainFactory::create('KeyValueCollection',
                                    [
                                        'is_pdf_creator_installed'   => gm_pdf_is_installed(),
                                        'permissionsGranted'         => [
                                            'invoices'    => $adminAccessService->checkAdminPermission((int)$_SESSION['customer_id'],
                                                                                                       PermissionAction::READ,
                                                                                                       GroupItem::CONTROLLER_TYPE,
                                                                                                       'InvoicesOverview') ? 'true' : 'false',
                                            'withdrawals' => $adminAccessService->checkAdminPermission((int)$_SESSION['customer_id'],
                                                                                                       PermissionAction::READ,
                                                                                                       GroupItem::CONTROLLER_TYPE,
                                                                                                       'Withdrawals') ? 'true' : 'false',
                                        ],
                                        'invoices'                   => [
                                            'exist' => $this->_getInvoicesExist()
                                        ],
                                        'packing_slips'              => [
                                            'exist' => $this->_getPackingSlipsExist()
                                        ],
                                        'page_length'                => $pageLength ? : 20,
                                        'parcel_services'            => $this->_getParcelServices(),
                                        'order_status_styles'        => $this->orderStatusStyles->getStyles(),
                                        'order_status'               => $this->_getStatuses(),
                                        'most_uesed_order_status'    => $this->_getMostUsedStatuses(),
                                        'row_heights'                => $this->_getRowHeights(),
                                        'columns'                    => $this->ordersOverviewColumns->serializeColumns(),
                                        'email_invoice_subject'      => gm_get_content('GM_PDF_EMAIL_SUBJECT',
                                                                                       $_SESSION['languages_id']),
                                        'default_row_action'         => $this->userConfigurationService->getUserConfiguration($customerId,
                                                                                                                              'ordersOverviewRowAction'),
                                        'default_bulk_action'        => $this->userConfigurationService->getUserConfiguration($customerId,
                                                                                                                              'ordersOverviewBulkAction'),
                                        'active_columns'             => $activeColumns,
                                        'active_row_height'          => $activeRowHeight ? : 'large',
                                        'default_column_settings'    => $defaultColumns,
                                        'bulk_email_invoice_subject' => gm_get_content("GM_PDF_EMAIL_SUBJECT",
                                                                                       $_SESSION['languages_id']),
            
                                        'max_amount_invoices_bulk_pdf'      => gm_get_conf('GM_PDF_MAX_AMOUNT_INVOICES_BULK_PDF'),
                                        'max_amount_packing_slips_bulk_pdf' => gm_get_conf('GM_PDF_MAX_AMOUNT_PACKING_SLIPS_BULK_PDF'),
                                        'bulk_settings_url'                 => xtc_href_link('gm_pdf.php#gm_pdf_bulk'),
                                        'display_tooltips'                  => $displayTooltips ? : 'true'
                                    ]);
        
        $assets = MainFactory::create('AssetCollection', $this->_getAssetsArray());
        
        $contentNavigation = MainFactory::create('ContentNavigationCollection', []);
        
        $contentNavigation->add($title, new StringType('admin.php?do=OrdersOverview'), new BoolType(true));
        
        $contentNavigation->add(new StringType($languageTextManager->get_text('BOX_ORDERS_STATUS', 'admin_general')),
                                new StringType('orders_status.php'),
                                new BoolType(false));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   $title,
                                   $template,
                                   $data,
                                   $assets,
                                   $contentNavigation);
    }
    
    
    /**
     * Check if invoices for the orders exist
     *
     * @return array
     */
    
    protected function _getInvoicesExist()
    {
        $invoices = $this->db->distinct()->select('order_id, invoice_id')->from('invoices')->get()->result_array();
        
        $result = [];
        
        foreach ($invoices as $invoice) {
            $result[$invoice['order_id']] = true;
        }
        
        return str_replace("\"", "\\\"", json_encode($result));
    }
    
    
    /**
     * Get Assets Array
     *
     * Overload this method in order to add your own assets to the page.
     *
     * @return array
     */
    protected function _getAssetsArray()
    {
        $assetsArray = [
            MainFactory::create('Asset', 'orders.lang.inc.php'),
            MainFactory::create('Asset', 'admin_orders.lang.inc.php'),
            MainFactory::create('Asset', 'gm_send_order.lang.inc.php'),
            MainFactory::create('Asset', 'gm_order_menu.lang.inc.php'),
            MainFactory::create('Asset', 'parcel_services.lang.inc.php'),
            MainFactory::create('Asset', 'order_details.lang.inc.php'),
            MainFactory::create('Asset', 'configuration.lang.inc.php')
        ];
        
        return $assetsArray;
    }
    
    
    /**
     * Returns the available row heights.
     *
     * @return array
     */
    protected function _getRowHeights()
    {
        return ['small', 'medium', 'large'];
    }
    
    
    /**
     * Get all parcel services.
     *
     * @return array
     */
    protected function _getParcelServices()
    {
        $container = LegacyDependencyContainer::getInstance();
        /** @var ParcelServiceReadService $service */
        $service            = $container->get(ParcelServiceReadService::class);
        $parcelServicesData = [];
        foreach ($service->getParcelServices() as $parcelService) {
            $parcelServicesData[] = [
                'id'      => $parcelService->id(),
                'name'    => $parcelService->name(),
                'default' => $parcelService->isDefault(),
            ];
        }
        
        return $parcelServicesData;
    }
    
    
    /**
     * Order status array.
     *
     * @return array
     */
    protected function _getStatuses()
    {
        $statuses = $this->db->distinct()
            ->select('orders_status_id, orders_status_name')
            ->from('orders_status')
            ->where('language_id',
                    $_SESSION['languages_id'])
            // Exclude "Cancelled" order status in order to keep the modal similar to order details
            // page. The order must be cancelled only by the cancel action.
            ->where('orders_status_id !=', 99)
            ->order_by('orders_status_name', 'ASC')
            ->get()
            ->result_array();
        
        $result = [];
        
        foreach ($statuses as $status) {
            $result[] = $status;
        }
        
        return $result;
    }
    
    
    /**
     * Order status array.
     *
     * @return array
     */
    protected function _getMostUsedStatuses()
    {
        $statuses = $this->db->distinct()
            ->select('count(orders_status_history.orders_status_id) as count, orders_status_history.orders_status_id, orders_status_name')
            ->from('orders_status')
            ->from('orders_status_history')
            ->where('language_id',
                    $_SESSION['languages_id'])
            // Exclude "Cancelled" order status in order to keep the modal similar to order details
            // page. The order must be cancelled only by the cancel action.
            ->where('orders_status.orders_status_id !=', 99)
            ->where('orders_status_history.orders_status_id = orders_status.orders_status_id')
            ->group_by('orders_status_id')
            ->order_by('count', 'DESC')
            ->get()
            ->result_array();
        
        $result = [];
        
        foreach ($statuses as $status) {
            $result[] = $status;
        }
        
        return $result;
    }
    
    
    private function _getPackingSlipsExist()
    {
        $packingSlips = $this->db->distinct()->select('order_id')->from('packing_slips')->get()->result_array();
        
        $result = [];
        
        foreach ($packingSlips as $packingSlip) {
            $result[$packingSlip['order_id']] = true;
        }
        
        return str_replace("\"", "\\\"", json_encode($result));
    }
}
