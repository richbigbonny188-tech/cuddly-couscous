<?php
/* --------------------------------------------------------------
   OrdersModalsAjaxController.inc.php 2020-12-18
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceReadService;
use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeFactory;
use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeWriteService;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;

require_once DIR_FS_CATALOG . 'admin/includes/gm/classes/GMOrderFormat.php';

/**
 * Class OrdersModalsAjaxController
 *
 * AJAX controller for the orders modals.
 *
 * @category   System
 * @package    AdminHttpViewControllers
 * @extends    AdminHttpViewController
 */
class OrdersModalsAjaxController extends AdminHttpViewController
{
    /**
     * @var LanguageTextManager
     */
    protected $languageTextManager;
    
    /**
     * @var TrackingCodeWriteService
     */
    protected $trackingCodeService;
    
    /**
     * @var TrackingCodeFactory
     */
    protected $trackingCodeFactory;
    
    /**
     * @var ParcelServiceReadService
     */
    protected $parcelServiceService;
    
    
    /**
     * Initialize Controller
     *
     * @throws Exception
     */
    public function init()
    {
        $this->languageTextManager = MainFactory::create('LanguageTextManager');
        $this->_validatePageToken();
        
        $container = LegacyDependencyContainer::getInstance();
        
        $this->trackingCodeService  = $container->get(TrackingCodeWriteService::class);
        $this->trackingCodeFactory  = $container->get(TrackingCodeFactory::class);
        $this->parcelServiceService = $container->get(ParcelServiceReadService::class);
    }
    
    
    /**
     * Stores a tracking number for a specific order.
     *
     * @return JsonHttpControllerResponse
     *
     * @throws Exception
     * @throws UnexpectedValueException
     */
    public function actionStoreTrackingNumber()
    {
        $orderId         = $this->_getPostData('orderId');
        $trackingNumber  = $this->_getPostData('trackingNumber');
        $parcelServiceId = $this->_getPostData('parcelServiceId');
        
        $trackingNumber = preg_replace('/\s/', '', $trackingNumber);
        
        $response = ['error'];
        
        if ($parcelServiceId > 0) {
            try {
                $languageCode  = $this->_getLanguageCodeByOrderId((int)$orderId);
                $parcelService = $this->parcelServiceService->getParcelServiceById((int)$parcelServiceId);
                $trackingUrl   = $this->_buildTrackingUrl($parcelService->url($languageCode), $trackingNumber);
                
                $parcelServiceDetails = $this->trackingCodeFactory->createParcelServiceDetails($parcelService->id(),
                                                                                               $languageCode,
                                                                                               $parcelService->name(),
                                                                                               $trackingUrl,
                                                                                               $parcelService->comment($languageCode));
                
                $this->trackingCodeService->createTrackingCode((int)$orderId, $trackingNumber, $parcelServiceDetails);
                
                $response = ['success'];
            } catch (Exception $e) {
                $response = AjaxException::response($e);
            }
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Change order status.
     *
     * @return JsonHttpControllerResponse
     *
     * @throws InvalidArgumentException
     */
    public function actionChangeOrderStatus()
    {
        $orderActions = MainFactory::create('OrderActions');
        
        $orderIds               = $this->_getPostData('selectedOrders');
        $statusId               = new IdType((int)$this->_getPostData('statusId'));
        $comment                = new StringType($this->_getPostData('comment'));
        $notifyCustomer         = new BoolType($this->_getPostData('notifyCustomer'));
        $sendParcelTrackingCode = new BoolType($this->_getPostData('sendParcelTrackingCode'));
        $sendComment            = new BoolType($this->_getPostData('sendComment'));
        $customerId             = new IdType($_SESSION['customer_id']);
        
        try {
            foreach ($orderIds as $orderId) {
                $orderActions->changeOrderStatus(new IdType($orderId),
                                                 $statusId,
                                                 $comment,
                                                 $notifyCustomer,
                                                 $sendParcelTrackingCode,
                                                 $sendComment,
                                                 $customerId);
            }
            
            $response = ['success'];
        } catch (Exception $e) {
            $response = AjaxException::response($e);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Download Bulk Invoices PDF.
     *
     * This method will provide a concatenated file of invoice PDFs. Provide a GET parameter "o" that contain
     * the selected order IDs.
     *
     * Notice: The "o" is used instead of "orderIds" because the final URL must be as small as possible (some
     * browsers do not work with GET URL of 100 orders).
     *
     * @see OrderActions
     */
    public function actionBulkPdfInvoices()
    {
        $orderActions = MainFactory::create('OrderActions');
        $orderIds     = $this->_getQueryParameter('o');
        
        try {
            $orderActions->bulkPdfInvoices($orderIds);
            
            return MainFactory::create('HttpControllerResponse', '');
        } catch (CrossReferenceException $e) {
            // if this exception occur, the already created pdf files are encrypted and it is not possible
            // to connect them into a large one, so the bulk creation will fail, but missing elements could be created
            
            return MainFactory::create(RedirectHttpControllerResponse::class,
                                       './admin.php?do=OrdersOverview&error=pdf_encrypted');
        }
    }
    
    
    /**
     * Download Bulk Packing Slips PDF.
     *
     * This method will provide a concatenated file of packing slip PDFs. Provide a GET parameter "o" that contain
     * the selected order IDs.
     *
     * Notice: The "o" is used instead of "orderIds" because the final URL must be as small as possible (some
     * browsers do not work with GET URL of 100 orders).
     *
     * @see OrderActions
     */
    public function actionBulkPdfPackingSlips()
    {
        $orderActions = MainFactory::create('OrderActions');
        $orderIds     = $this->_getQueryParameter('o');
        $orderActions->bulkPdfPackingSlips($orderIds);
        
        return MainFactory::create('HttpControllerResponse', '');
    }
    
    
    /**
     * Cancel Order Callback
     *
     * This method uses the OrderActions class to cancel an order and fulfill the requirements of the cancellation
     * (re-stock product, inform customer ...).
     *
     * @return JsonHttpControllerResponse
     */
    public function actionCancelOrder()
    {
        $orderActions = MainFactory::create('OrderActions');
        
        $orderIds                  = $this->_getPostData('selectedOrders');
        $restockQuantity           = new BoolType($this->_getPostData('reStock') === 'true');
        $recalculateShippingStatus = new BoolType($this->_getPostData('reShip') === 'true');
        $resetArticleStatus        = new BoolType($this->_getPostData('reActivate') === 'true');
        $notifyCustomer            = new BoolType($this->_getPostData('notifyCustomer') === 'true');
        $sendComment               = new BoolType($this->_getPostData('sendComments') === 'true');
        $comment                   = new StringType($this->_getPostData('cancellationComments'));
        $customerId                = (int)($_SESSION['customer_id'] ?? null);
        $currentUserId             = new IdType($customerId);
        
        $orderActions->cancelOrder($orderIds,
                                   $restockQuantity,
                                   $recalculateShippingStatus,
                                   $resetArticleStatus,
                                   $notifyCustomer,
                                   $sendComment,
                                   $comment,
                                   $currentUserId);
        
        $urls = [];
        
        if ($this->_getPostData('cancelInvoice') === 'true') {
            /** @var InvoiceArchiveReadService $invoiceArchiveReadService */
            $invoiceArchiveReadService = StaticGXCoreLoader::getService('InvoiceArchiveRead');
            
            foreach ($orderIds as $orderId) {
                $invoices = $invoiceArchiveReadService->getInvoiceListByConditions(['order_id' => $orderId],
                                                                                   null,
                                                                                   null,
                                                                                   new StringType('invoice_date DESC'));
                
                if (!$invoices->isEmpty()) {
                    /** @var InvoiceListItem $invoice */
                    $invoice = $invoices->getItem(0);
                    
                    if (!$invoice->isCancellationInvoice()) {
                        $urls[] = 'gm_pdf_order.php?oID=' . (int)$orderId . '&type=invoice&cancel_invoice_id='
                                  . $invoice->getInvoiceId();
                    }
                }
            }
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['urls' => $urls]);
    }
    
    
    /**
     * Delete Order Callback
     *
     * Implementation removed due to legal contraints
     *
     * @return JsonHttpControllerResponse
     */
    public function actionDeleteOrder()
    {
        return MainFactory::create('JsonHttpControllerResponse', []);
    }
    
    
    /**
     * Get Email-Invoice Subject
     */
    public function actionGetEmailInvoiceSubject()
    {
        
        /** @var InvoiceArchiveReadService $invoiceReader */
        $invoiceReader   = StaticGXCoreLoader::getService('InvoiceArchiveRead');
        $orderId         = $this->_getQueryParameter('id');
        $invoices        = $invoiceReader->getInvoiceListByConditions(['order_id' => $orderId]);
        $invoiceIdExists = $invoices->isEmpty();
        $invoiceNumbers  = [];
        $dateFormat      = DATE_FORMAT;
        $subject         = gm_get_content('GM_PDF_EMAIL_SUBJECT', $_SESSION['languages_id']);
        
        if ($invoices->count() === 1) {
            /** @var InvoiceListItem $invoice */
            $invoice = $invoices->getItem(0);
            
            $invoiceNumbers[$invoice->getInvoiceId()] = $invoice->getInvoiceNumber();
            
            $orderDate     = $invoice->getOrderDatePurchased();
            $invoiceNumber = $invoice->getInvoiceNumber();
        } elseif ($invoices->count() > 1) {
            /** @var InvoiceListItem $invoice */
            foreach ($invoices as $invoice) {
                $invoiceNumbers[$invoice->getInvoiceId()] = $invoice->getInvoiceNumber();
            }
            
            $subject = gm_get_content('GM_PDF_INVOICES_EMAIL_SUBJECT', $_SESSION['languages_id']);
            
            $orderDate     = $invoice->getOrderDatePurchased();
            $invoiceNumber = $invoice->getInvoiceNumber();
        } else {
            $orderDate     = new DateTime($this->_getQueryParameter('date'));
            $orderFormat   = new GMOrderFormat();
            $next_id       = $orderFormat->get_next_id('GM_NEXT_INVOICE_ID');
            $invoiceNumber = str_replace('{INVOICE_ID}', $next_id, gm_get_conf('GM_INVOICE_ID'));
        }
        
        $subject = str_replace(['{ORDER_ID}', '{DATE}', '{INVOICE_ID}', '{INVOICE_NUMBERS}'],
                               [
                                   $orderId,
                                   $orderDate->format($dateFormat),
                                   $invoiceNumber,
                                   implode(', ', $invoiceNumbers)
                               ],
                               $subject);
        
        // Return the response back to the client.
        return MainFactory::create('JsonHttpControllerResponse',
                                   [
                                       'subject'         => $subject,
                                       'invoiceIdExists' => $invoiceIdExists,
                                       'invoiceNumbers'  => $invoiceNumbers
                                   ]);
    }
    
    
    /**
     * Get Email-Invoice Subject (Raw Data)
     *
     * Currently the invoice ID can only be found in by parsing the PDF filename in the /export/invoice directory.
     *
     * This method will return the email subject data instead of the pre-made string.
     *
     * @deprecated not used since GX3.15.1
     */
    public function actionGetEmailInvoiceSubjectData()
    {
        $invoiceId = $this->_getPostData('invoiceId');
        
        /** @var InvoiceArchiveReadService $invoiceReader */
        $invoiceReader   = StaticGXCoreLoader::getService('InvoiceArchiveRead');
        $invoiceListItem = $invoiceReader->getInvoiceListItemById(new IdType($invoiceId));
        $dateFormat      = DATE_FORMAT;
        
        $subjectData = [
            'invoiceNumber' => $invoiceListItem->getInvoiceNumber(),
            'invoiceDate'   => $invoiceListItem->getInvoiceDate()->format($dateFormat)
        ];
        
        return MainFactory::create('JsonHttpControllerResponse', $subjectData);
    }
    
    
    /**
     * Get amount of invoices for an order.
     */
    public function actionGetInvoiceCount()
    {
        $orderId = (int)$this->_getQueryParameter('orderId');
        
        /** @var InvoiceArchiveReadService $invoiceArchiveReadService */
        $invoiceArchiveReadService = StaticGXCoreLoader::getService('InvoiceArchiveRead');
        $invoices                  = $invoiceArchiveReadService->getInvoiceListByConditions(['order_id' => $orderId]);
        
        return MainFactory::create('JsonHttpControllerResponse', ['count' => $invoices->count()]);
    }
    
    
    /**
     * @param int $p_orderId
     *
     * @return int
     * @throws Exception
     */
    protected function _getLanguageCodeByOrderId(int $p_orderId)
    {
        $query  = 'SELECT l.code
					FROM
						orders o ,
						languages l
					WHERE
						o.orders_id = ' . $p_orderId . ' AND
						o.language = l.directory
					ORDER BY l.status DESC
					LIMIT 1';
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result) == 0) {
            throw new Exception('language_id of order ' . $p_orderId . ' could not be determined');
        }
        
        $row        = xtc_db_fetch_array($result);
        $languageId = $row['code'];
        
        return $languageId;
    }
    
    
    /**
     * @param string $p_url
     * @param string $p_trackingCode
     *
     * @return string|string[]
     */
    protected function _buildTrackingUrl(string $p_url, string $p_trackingCode)
    {
        return str_replace('{TRACKING_NUMBER}', rawurlencode((string)$p_trackingCode), (string)$p_url);
    }
}
