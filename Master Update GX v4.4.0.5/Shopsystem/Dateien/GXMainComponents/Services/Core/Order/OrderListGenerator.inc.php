<?php
/* --------------------------------------------------------------
   OrderListGenerator.inc.php 2020-08-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

MainFactory::load_class('OrderListGeneratorInterface');

/**
 * Class OrderListGenerator
 *
 * @category System
 * @package  Order
 */
class OrderListGenerator extends AbstractDataPaginator implements OrderListGeneratorInterface
{
    /**
     * @var string
     */
    const FILTER_NO_VALUE = '{no-value}';
    
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var int
     */
    protected $defaultLanguageId;
    
    /**
     * @var \PaymentTitleProvider
     */
    protected $paymentTitleProvider;
    
    /**
     * @var \ShippingTitleProvider
     */
    protected $shippingTitleProvider;
    
    
    /**
     * OrderListGenerator Constructor
     *
     * @param CI_DB_query_builder    $db
     * @param \PaymentTitleProvider  $paymentTitleProvider
     * @param \ShippingTitleProvider $shippingTitleProvider
     */
    public function __construct(
        CI_DB_query_builder $db,
        PaymentTitleProvider $paymentTitleProvider,
        ShippingTitleProvider $shippingTitleProvider
    ) {
        $this->db                    = $db;
        $this->defaultLanguageId     = $_SESSION['languages_id'];
        $this->paymentTitleProvider  = $paymentTitleProvider;
        $this->shippingTitleProvider = $shippingTitleProvider;
    }
    
    
    /**
     * Applies the class default sorting
     */
    protected function _applyDefaultSorting()
    {
        $this->db->order_by('orders.orders_id', 'asc');
    }
    
    
    /**
     * return the child class Field Map array.
     *
     * @return array.
     */
    
    protected function _getFieldMap()
    {
        
        return [
            'id'                                    => 'orders.orders_id',
            'statusid'                              => 'orders_status.orders_status_id',
            'statusname'                            => 'orders_status.orders_status_name',
            'totalsum'                              => 'orders_total.text',
            'purchasedate'                          => 'orders.date_purchased',
            'comment'                               => 'orders.comments',
            'mailstatus'                            => 'orders.gm_send_order_status',
            'customerid'                            => 'orders.customers_id',
            'customername'                          => 'orders.customers_name',
            'customeremail'                         => 'orders.customers_email_address',
            'customerstatusid'                      => 'orders.customers_status',
            'customerstatusname'                    => 'orders.customers_status_name',
            'deliveryaddress.firstname'             => 'orders.delivery_firstname',
            'deliveryaddress.lastname'              => 'orders.delivery_lastname',
            'deliveryaddress.company'               => 'orders.delivery_company',
            'deliveryaddress.street'                => 'orders.delivery_street_address',
            'deliveryaddress.housenumber'           => 'orders.delivery_house_number',
            'deliveryaddress.additionaladdressinfo' => 'orders.delivery_additional_info',
            'deliveryaddress.postcode'              => 'orders.delivery_postcode',
            'deliveryaddress.city'                  => 'orders.delivery_city',
            'deliveryaddress.state'                 => 'orders.delivery_state',
            'deliveryaddress.country'               => 'orders.delivery_country',
            'deliveryaddress.countryisocode'        => 'orders.delivery_country_iso_code_2',
            'billingaddress.firstname'              => 'orders.billing_firstname',
            'billingaddress.lastname'               => 'orders.billing_lastname',
            'billingaddress.company'                => 'orders.billing_company',
            'billingaddress.street'                 => 'orders.billing_street_address',
            'billingaddress.housenumber'            => 'orders.billing_house_number',
            'billingaddress.additionaladdressinfo'  => 'orders.billing_additional_info',
            'billingaddress.postcode'               => 'orders.billing_postcode',
            'billingaddress.city'                   => 'orders.billing_city',
            'billingaddress.state'                  => 'orders.billing_state',
            'billingaddress.country'                => 'orders.billing_country',
            'billingaddress.countryisocode'         => 'orders.billing_country_iso_code_2',
            'orders_total.value'                    => 'orders_total.value',
            'orders.orders_id'                      => 'orders.orders_id',
            'orders.customers_lastname'             => 'orders.customers_lastname',
            'orders.customers_status_name'          => 'orders.customers_status_name',
            'orders.payment_class'                  => 'orders.payment_class',
            'orders.shipping_class'                 => 'orders.shipping_class',
            'orders.delivery_country_iso_code_2'    => 'orders.delivery_country_iso_code_2',
            'orders.date_purchased'                 => 'orders.date_purchased',
            'orders_status.orders_status_name'      => 'orders_status.orders_status_name',
            'orders.order_total_weight'             => 'orders.order_total_weight',
            'invoice_numbers'                       => 'invoice_numbers'
        ];
    }
    
    
    /**
     * Get Order List Items
     *
     * Returns an order list item collection.
     *
     * @link http://www.codeigniter.com/user_guide/database/query_builder.html#looking-for-specific-data
     *
     * @param string|array $conditions Provide a WHERE clause string or an associative array (actually any parameter
     *                                 that is acceptable by the "where" method of the CI_DB_query_builder method).
     * @param \Pager|null  $pager      (Optional) Pager object with pagination information
     * @param array        $sorters    (Optional) array of Sorter objects with data sorting information
     *
     * @return OrderListItemCollection
     *
     * @throws InvalidArgumentException If the result rows contain invalid values.
     */
    public function getOrderListByConditions($conditions = [], \Pager $pager = null, array $sorters = [])
    {
        $this->_select()->_applyPagination($pager)->_applySorting($sorters)->_group();
        
        $this->db->where('orders_status.language_id', $this->defaultLanguageId)->where('orders_total.class',
                                                                                       'ot_total');
        
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        
        $result = $this->db->get()->result_array();
        
        return $this->_prepareCollection($result);
    }
    
    
    /**
     * Filter order list items by the provided parameters.
     *
     * The following slug names need to be used:
     *
     *   - number => orders.orders_id
     *   - customer => orders.customers_lastname orders.customers_firstname
     *   - group => orders.customers_status_name
     *   - sum => orders_total.value
     *   - payment => orders.payment_method
     *   - shipping => orders.shipping_method
     *   - countryIsoCode => orders.delivery_country_iso_code_2
     *   - date => orders.date_purchased
     *   - status => orders_status.orders_status_name
     *   - totalWeight => orders.order_total_weight
     *
     * @param array       $filterParameters Contains the column slug-names and their values.
     * @param \Pager|null $pager            (Optional) Pager object with pagination information
     * @param array       $sorters          (Optional) array of Sorter objects with data sorting information
     *
     * @return OrderListItemCollection
     *
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     */
    public function filterOrderList(array $filterParameters, \Pager $pager = null, array $sorters = [])
    {
        $result = $this->_filterWithPagerAndSorter($filterParameters, $pager, $sorters);
        
        return $this->_prepareCollection($result->result_array());
    }
    
    
    /**
     * Get the filtered orders count.
     *
     * This number is useful for pagination functionality where the app needs to know the number of the filtered rows.
     *
     * @param array $filterParameters
     *
     * @return int
     *
     * @throws BadMethodCallException
     */
    public function filterOrderListCount(array $filterParameters)
    {
        $result = $this->_filterWithPagerAndSorter($filterParameters);
        
        return $result->num_rows();
    }
    
    
    /**
     * Filter records by a single keyword string.
     *
     * @param StringType  $keyword Keyword string to be used for searching in order records.
     * @param \Pager|null $pager   (Optional) Pager object with pagination information
     * @param array       $sorters (Optional) array of Sorter objects with data sorting information
     *
     * @return mixed
     *
     * @throws InvalidArgumentException If the result rows contain invalid values.
     */
    public function getOrderListByKeyword(StringType $keyword, \Pager $pager = null, array $sorters = [])
    {
        $this->_select()->_applyPagination($pager)->_applySorting($sorters)->_group();
        
        $this->_setKeywordWhereClause($keyword);
        
        $result = $this->db->get()->result_array();
        
        return $this->_prepareCollection($result);
    }
    
    
    /**
     * Get count of orders filtered by keyword
     *
     * @param StringType $keyword Keyword string to be used for searching in order records.
     *
     * @return int
     */
    public function getOrderListByKeywordCount(StringType $keyword)
    {
        $this->_select()->_group();
        
        $this->_setKeywordWhereClause($keyword);
        
        $rows = $this->db->get()->num_rows();
        
        return $rows;
    }
    
    
    /**
     * Get the total count of all orders
     *
     * @return int
     */
    public function getOrderListCount()
    {
        $rows = $this->db->count_all('orders');
        
        return $rows;
    }
    
    
    /**
     * Execute the select and join methods.
     *
     * @return OrderListGenerator Returns the instance object for method chaining.
     *
     * @throws BadMethodCallException
     */
    protected function _select()
    {
        $columns = [
            $this->_ordersColumns(),
            $this->_ordersStatusColumns(),
            $this->_ordersTotalColumns(),
            $this->_addressColumns('delivery'),
            $this->_addressColumns('billing'),
            $this->_customersStatusColumns(),
            $this->_invoicesColumns()
        ];
        
        $this->db->select(implode(', ', $columns))
            ->from('orders')
            ->join('orders_status',
                   'orders_status.orders_status_id = orders.orders_status',
                   'inner')
            ->join('orders_total', 'orders_total.orders_id = orders.orders_id', 'left')
            ->join('customers',
                   'customers.customers_id = orders.customers_id',
                   'left')
            ->join('customers_status',
                   'customers_status.customers_status_id = orders.customers_status AND customers_status.language_id = '
                   . $this->defaultLanguageId,
                   'left')
            ->join('invoices', 'orders.orders_id = invoices.order_id', 'left');
        
        return $this;
    }
    
    
    /**
     * @param StringType $keyword Keyword string to be used for searching in order records.
     */
    protected function _setKeywordWhereClause(StringType $keyword)
    {
        $match = $this->db->escape_like_str($keyword->asString());
        
        $this->db->where('
			orders_total.class = "ot_total" 
			AND orders_status.language_id = ' . $this->defaultLanguageId . ' 
			AND ( 
				orders.orders_id LIKE "%' . $match . '%"
				OR orders.customers_id LIKE "%' . $match . '%"
				OR orders.date_purchased LIKE "%' . $match . '%"
				OR orders.payment_class LIKE "%' . $match . '%"
				OR orders.payment_method LIKE "%' . $match . '%"
				OR orders.shipping_class LIKE "%' . $match . '%"
				OR orders.shipping_method LIKE "%' . $match . '%"
				OR orders.customers_firstname LIKE "%' . $match . '%"
				OR orders.customers_lastname LIKE "%' . $match . '%"
				OR orders_total.value LIKE "%' . $match . '%"
				OR orders_status.orders_status_id LIKE "%' . $match . '%"
				OR orders_status.orders_status_name LIKE "%' . $match . '%"
			)');
    }
    
    
    /**
     * Returns a string for the ::_select() method which contains column names of the orders table.
     *
     * @return string
     */
    protected function _ordersColumns()
    {
        return 'orders.orders_id, orders.customers_id, orders.date_purchased, orders.payment_class,
			orders.payment_method, orders.shipping_class, orders.shipping_method, orders.customers_name, 
			orders.customers_firstname, orders.customers_lastname, orders.comments, orders.customers_status,  
			orders.customers_status_name, orders.customers_email_address, orders.gm_send_order_status, 
			orders.order_total_weight, orders.customers_company';
    }
    
    
    /**
     * Returns a string for the ::_select() method which contains column names of the orders status table.
     *
     * @return string
     */
    protected function _ordersStatusColumns()
    {
        return 'orders_status.orders_status_id, orders_status.orders_status_name';
    }
    
    
    /**
     * Returns a string for the ::_select() method which contains column names of the orders total table.
     *
     * @return string
     */
    protected function _ordersTotalColumns()
    {
        return 'orders_total.value AS total_sum, orders_total.text AS total_sum_text';
    }
    
    
    /**
     * Returns a string for the ::_select() method which contains column names of the orders table for address data.
     *
     * @param string $type Whether delivery or billing.
     *
     * @return string
     *
     * @throws BadMethodCallException
     */
    protected function _addressColumns($type)
    {
        if ($type !== 'delivery' && $type !== 'billing') {
            throw new BadMethodCallException('the "$type" argument has to be equal to whether "delivery" or "billing"');
        }
        
        return 'orders.' . $type . '_firstname, ' . 'orders.' . $type . '_lastname, ' . 'orders.' . $type . '_company, '
               . 'orders.' . $type . '_street_address, ' . 'orders.' . $type . '_house_number, ' . 'orders.' . $type
               . '_additional_info, ' . 'orders.' . $type . '_city, ' . 'orders.' . $type . '_postcode, ' . 'orders.'
               . $type . '_state, ' . 'orders.' . $type . '_country, ' . 'orders.' . $type . '_country_iso_code_2';
    }
    
    
    /**
     * Returns a string for the ::_select() method which contains fallback customer status name if no value is
     * set in the orders table.
     *
     * @return string
     */
    protected function _customersStatusColumns()
    {
        return 'customers_status.customers_status_name AS fallback_customers_status';
    }
    
    
    /**
     * Returns a string for the ::_select() method which contains fallback implosion of all invoice IDs of the order if
     * no value is set in the orders table.
     *
     * @return string
     */
    protected function _invoicesColumns()
    {
        return 'group_concat(DISTINCT invoice_number SEPARATOR ", ") AS invoice_numbers';
    }
    
    
    /**
     * Creates an order address block object by the given type and row_array (looped result of CIDB::result_array())
     *
     * @param string $type Whether delivery or billing.
     * @param array  $row  Array which contain data about an order result row.
     *
     * @return OrderAddressBlock
     *
     * @throws BadMethodCallException
     */
    protected function _createOrderAddressBlockByRow($type, array $row)
    {
        if ($type !== 'delivery' && $type !== 'billing') {
            throw new BadMethodCallException('the "$type" argument has to be equal to whether "delivery" or "billing"');
        }
        
        $firstName             = MainFactory::create('StringType', (string)$row[$type . '_firstname']);
        $lastName              = MainFactory::create('StringType', (string)$row[$type . '_lastname']);
        $company               = MainFactory::create('StringType', (string)$row[$type . '_company']);
        $streetAddress         = MainFactory::create('StringType', (string)$row[$type . '_street_address']);
        $houseNumber           = MainFactory::create('StringType', (string)$row[$type . '_house_number']);
        $additionalAddressInfo = MainFactory::create('StringType', (string)$row[$type . '_additional_info']);
        $postCode              = MainFactory::create('StringType', (string)$row[$type . '_postcode']);
        $city                  = MainFactory::create('StringType', (string)$row[$type . '_city']);
        $state                 = MainFactory::create('StringType', (string)$row[$type . '_state']);
        $country               = MainFactory::create('StringType', (string)$row[$type . '_country']);
        $countryIsoCode        = MainFactory::create('StringType', (string)$row[$type . '_country_iso_code_2']);
        
        return MainFactory::create('OrderAddressBlock',
                                   $firstName,
                                   $lastName,
                                   $company,
                                   $streetAddress,
                                   $houseNumber,
                                   $additionalAddressInfo,
                                   $postCode,
                                   $city,
                                   $state,
                                   $country,
                                   $countryIsoCode);
    }
    
    
    /**
     * Set the order by clause of the query.
     *
     * @param StringType $orderBy
     *
     * @return OrderListGenerator Returns the instance object for method chaining.
     */
    protected function _order(StringType $orderBy = null)
    {
        if ($orderBy) {
            $this->db->order_by($orderBy->asString());
        }
        
        return $this;
    }
    
    
    /**
     * Execute the group by statement.
     *
     * @return OrderListGenerator Returns the instance object for method chaining.
     */
    protected function _group()
    {
        $this->db->group_by('orders.orders_id, orders_status.orders_status_name, orders_total.value, orders_total.text');
        
        return $this;
    }
    
    
    /**
     * Prepare the OrderListItemCollection object.
     *
     * This method will prepare the collection object which is going to be returned by both
     * the "get" and "filter" methods. The following values are required to be present in
     * each row of the $result parameter:
     *
     *      - orders_id
     *      - customers_id
     *      - customers_firstname
     *      - customers_lastname
     *      - date_purchased
     *      - payment_class
     *      - payment_method
     *      - shipping_class
     *      - shipping_method
     *      - orders_status_id
     *      - orders_status_name
     *      - total_sum
     *
     * @param array $result Contains the order data.
     *
     * @return OrderListItemCollection
     *
     * @throws InvalidArgumentException
     */
    protected function _prepareCollection(array $result)
    {
        $listItems = [];
        
        foreach ($result as $row) {
            $orderId           = new IdType((int)$row['orders_id']);
            $customerId        = new IdType((int)$row['customers_id']);
            $customerNameValue = empty($row['customers_firstname'])
                                 && empty($row['customers_lastname']) ? (string)$row['customers_name'] : (string)$row['customers_firstname']
                                                                                                         . ' '
                                                                                                         . (string)$row['customers_lastname'];
            $customerName      = new StringType($customerNameValue);
            $customerEmail     = new StringType((string)$row['customers_email_address']);
            $totalSum          = new StringType(trim((string)str_replace(['<b>', '</b>'], '', $row['total_sum_text'])));
            $customerCompany   = new StringType((string)$row['customers_company']);
            
            $purchaseDateTime = new EmptyDateTime($row['date_purchased']);
            $orderStatusId    = new IntType((int)$row['orders_status_id']);
            $orderStatusName  = new StringType((string)$row['orders_status_name']);
            
            $comment          = new StringType((string)$row['comments']);
            $customerStatusId = new IdType((int)$row['customers_status']);
            
            $customerStatusName = new StringType(!empty($row['customers_status_name']) ? (string)$row['customers_status_name'] : (string)$row['fallback_customers_status']);
            $totalWeight        = new DecimalType($row['order_total_weight'] ? : 0.0000);
            $mailStatus         = new BoolType((int)$row['gm_send_order_status'] === 1);
            
            $orderListItem = MainFactory::create('OrderListItem');
            
            $orderListItem->setOrderId($orderId);
            $orderListItem->setCustomerId($customerId);
            $orderListItem->setCustomerName($customerName);
            $orderListItem->setCustomerEmail($customerEmail);
            $orderListItem->setCustomerCompany($customerCompany);
            
            $orderListItem->setDeliveryAddress($this->_createOrderAddressBlockByRow('delivery', $row));
            $orderListItem->setBillingAddress($this->_createOrderAddressBlockByRow('billing', $row));
            
            $orderListItem->setComment($comment);
            $orderListItem->setCustomerMemos($this->_createMemoCollectionByCustomersId($row['customers_id']));
            $orderListItem->setCustomerStatusId($customerStatusId);
            $orderListItem->setCustomerStatusName($customerStatusName);
            $orderListItem->setTotalWeight($totalWeight);
            $orderListItem->setTotalSum($totalSum);
            $orderListItem->setPaymentType($this->_createOrderPaymentType($row));
            $orderListItem->setShippingType($this->_createOrderShippingType($row));
            $orderListItem->setTrackingLinks($this->_createTrackingLinksByOrderId($row['orders_id']));
            $orderListItem->setPurchaseDateTime($purchaseDateTime);
            $orderListItem->setOrderStatusId($orderStatusId);
            $orderListItem->setOrderStatusName($orderStatusName);
            $orderListItem->setWithdrawalIds($this->_createWithdrawalIdsByOrderId($row['orders_id']));
            $orderListItem->setMailStatus($mailStatus);
            $orderListItem->setInvoiceNumbers($this->_createInvoiceNumberCollectionByOrderId($row['orders_id']));
            
            $listItems[] = $orderListItem;
        }
        
        $collection = MainFactory::create('OrderListItemCollection', $listItems);
        
        return $collection;
    }
    
    
    /**
     * Creates and returns an order payment type instance by the given row data.
     *
     * @param array $row Row array with data about the order payment type.
     *
     * @return OrderPaymentType
     */
    protected function _createOrderPaymentType(array $row)
    {
        return $this->_createOrderType('payment', $row);
    }
    
    
    /**
     * Creates and returns an order shipping type instance by the given row data.
     *
     * @param array $row Row array with data about the order shipping type.
     *
     * @return OrderShippingType
     */
    protected function _createOrderShippingType(array $row)
    {
        return $this->_createOrderType('shipping', $row);
    }
    
    
    /**
     * Creates and returns whether an order shipping or payment type instance by the given row data and type argument.
     *
     * @param string $type Whether 'shipping' or 'payment', used to determine the expected order type class.
     * @param array  $row  Row array with data about the order type.
     *
     * @return OrderShippingType|OrderPaymentType
     *
     * @throws InvalidArgumentException
     */
    protected function _createOrderType($type, array $row)
    {
        $explodedMethodName = explode('_', $row[$type . '_method']);
        
        $method = (count($explodedMethodName) === 2
                   && $explodedMethodName[0] === $explodedMethodName[1]) ? $explodedMethodName[0] : $row[$type
                                                                                                         . '_method'];
        $title  = $this->_getPaymentOrShippingTitle($method, $type);
        
        $explodedClassName = explode('_', $row[$type . '_class']);
        
        $class = (count($explodedClassName) === 2
                  && $explodedClassName[0] === $explodedClassName[1]) ? $explodedClassName[0] : $row[$type . '_class'];
        
        $configurationValue = $this->db->get_where('gx_configurations',
                                                   [
                                                       'key' => 'configuration/MODULE_' . strtoupper($type) . '_'
                                                                              . strtoupper($class) . '_ALIAS'
                                                   ])->row()->value;
        
        $alias = $configurationValue ? new StringType($configurationValue) : null;
        
        return MainFactory::create('Order' . ucfirst($type) . 'Type',
                                   new StringType($title),
                                   new StringType((string)$row[$type . '_class']),
                                   $alias);
    }
    
    
    /**
     * Returns the title of the given payment or shipping title.
     *
     * @param string $method Payment or shipping method.
     * @param string $type   Whether "payment" or "shipping".
     *
     * @return string
     */
    protected function _getPaymentOrShippingTitle($method, $type)
    {
        if ($type === 'payment') {
            return $this->paymentTitleProvider->title($method);
        }
        
        return $this->shippingTitleProvider->title($method);
    }
    
    
    /**
     * Creates and returns a customer memo collection by the given customers id.
     *
     * @param int $customersId Id of customer.
     *
     * @return CustomerMemoCollection
     */
    protected function _createMemoCollectionByCustomersId($customersId)
    {
        $memoArray = $this->db->get_where('customers_memo', ['customers_id' => $customersId])->result_array();
        $memos     = [];
        
        foreach ($memoArray as $customerMemo) {
            $memoDate = new DateTime();
            $memoDate->setTimestamp(strtotime($customerMemo['memo_date']));
            
            $memos[] = MainFactory::create('CustomerMemo',
                                           MainFactory::create('IdType', $customerMemo['customers_id']),
                                           MainFactory::create('StringType', $customerMemo['memo_title']),
                                           MainFactory::create('StringType', $customerMemo['memo_text']),
                                           $memoDate,
                                           MainFactory::create('IdType', $customerMemo['poster_id']));
        }
        
        return MainFactory::create('CustomerMemoCollection', $memos);
    }
    
    
    /**
     * Creates and returns a string collection which contains the tracking links of the order.
     *
     * @param int $orderId Id of current order.
     *
     * @return StringCollection
     * @throws InvalidArgumentException
     *
     */
    protected function _createTrackingLinksByOrderId($orderId)
    {
        /* @var array $trackingLinksArray */
        $trackingLinksArray = $this->db->get_where('orders_parcel_tracking_codes', ['order_id' => $orderId])
            ->result_array();
        $links              = [];
        
        foreach ($trackingLinksArray as $trackingLink) {
            $links[] = new StringType($trackingLink['url']);
        }
        
        return new StringCollection($links);
    }
    
    
    /**
     * Creates and returns a ID collection which contains the withdrawal ids of the order.
     *
     * @param int $orderId Id of current order.
     *
     * @return IdCollection
     * @throws InvalidArgumentException
     *
     */
    protected function _createWithdrawalIdsByOrderId($orderId)
    {
        /* @var array $withdrawalsArray */
        $withdrawalsArray = $this->db->get_where('withdrawals', ['order_id' => $orderId])->result_array();
        $withdrawalIds    = [];
        
        foreach ($withdrawalsArray as $withdrawal) {
            $withdrawalIds[] = new IdType($withdrawal['withdrawal_id']);
        }
        
        return new IdCollection($withdrawalIds);
    }
    
    
    /**
     * Creates and returns a string collection which contains the invoice numbers of the order
     *
     * @param $orderId Id of current order
     *
     * @return StringCollection
     * @throws InvalidArgumentException
     *
     */
    protected function _createInvoiceNumberCollectionByOrderId($orderId)
    {
        /* @var array $invoiceNumberArray */
        $invoiceNumberArray = $this->db->select('invoice_number')
            ->distinct()
            ->from('invoices')
            ->where(['order_id' => $orderId])
            ->get()
            ->result_array();
        $invoiceNumbers     = [];
        
        foreach ($invoiceNumberArray as $invoiceNumber) {
            $invoiceNumbers[] = new StringType($invoiceNumber['invoice_number']);
        }
        
        return new StringCollection($invoiceNumbers);
    }
    
    
    /**
     *
     * @param array        $filterParameters Contains the column slug-names and their values.
     * @param IntType|null $startIndex       (Optional) Pager object with pagination information
     * @param IntType|null $maxCount         (Optional) array of Sorter objects with data sorting information
     *
     * @return CI_DB_result
     * @deprecated Filter the order records.
     *
     */
    protected function _filter(
        array $filterParameters,
        IntType $startIndex = null,
        IntType $maxCount = null,
        StringType $orderBy = null
    ) {
        $pager = null;
        if ($maxCount && $startIndex) {
            $pager = Pager::createCustom($startIndex->asInt(), $maxCount->asInt());
        } elseif ($maxCount && !$startIndex) {
            $pager = Pager::createCustom(0, $startIndex->asInt());
        }
        $sorters = $this->_translateOrderByStringIntoArrayOfSorter($orderBy);
        
        return $this->_filterWithPagerAndSorter($filterParameters, $pager, $sorters);
    }
    
    
    /**
     * Filter the order records.
     *
     * @param array       $filterParameters Contains the column slug-names and their values.
     * @param \Pager|null $pager            (Optional) Pager object with pagination information
     * @param array       $sorters          (Optional) array of Sorter objects with data sorting information
     *
     * @return CI_DB_result
     */
    protected function _filterWithPagerAndSorter(array $filterParameters, \Pager $pager = null, array $sorters = [])
    {
        $this->_setFilterArguments($filterParameters, $pager, $sorters);
        
        return $this->db->get();
    }
    
    
    /**
     * Set the where clauses for the filtered order records query.
     *
     * This method contains the filtering logic. It can be overloaded in order to provide a custom filtering logic.
     *
     * @param array       $filterParameters Contains the column slug-names and their values.
     * @param \Pager|null $pager            (Optional) Pager object with pagination information
     * @param array       $sorters          (Optional) array of Sorter objects with data sorting information
     *
     * @return OrderListGeneratorInterface Same instance for chained method calls.
     *
     * @throws BadMethodCallException
     */
    protected function _setFilterArguments(array $filterParameters, \Pager $pager = null, array $sorters = [])
    {
        $this->_select()->_applyPagination($pager)->_applySorting($sorters)->_group();
        
        $this->db->where('orders_status.language_id', $this->defaultLanguageId)->where('orders_total.class',
                                                                                       'ot_total');
        
        // Replace wildcards recursively
        array_walk_recursive($filterParameters,
            function (&$value, $key) {
                if (!is_array($value) && $value !== ''
                    && !in_array($key,
                                 [
                                     'date',
                                     'group',
                                     'paymentMethod',
                                     'shippingMethod',
                                     'countryIsoCode',
                                     'status'
                                 ])) {
                    $value = str_replace('*', '%', $this->db->escape_str($value));
                }
                
                if ($value === self::FILTER_NO_VALUE) {
                    $value = '';
                }
            });
        
        // Filter by order number. 
        if (isset($filterParameters['number']) && is_array($filterParameters['number'])) {
            $this->db->where('orders.orders_id >=', array_shift($filterParameters['number']));
            $this->db->where('orders.orders_id <=', array_shift($filterParameters['number']));
        } elseif (!empty($filterParameters['number'])
                  || (isset($filterParameters['number'])
                      && $filterParameters['number'] === '0')) {
            $this->db->where('`orders`.`orders_id` LIKE "' . $filterParameters['number'] . '"');
        }
        
        // Filter by customer. 
        if (!empty($filterParameters['customer'])) {
            $this->db->group_start();
            if (strpos($filterParameters['customer'], '#') === 0) {
                $this->db->where('orders.customers_id', substr($filterParameters['customer'], 1));
            } else {
                $this->db->where('`orders`.`customers_name` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`orders`.`customers_firstname` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`orders`.`customers_company` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`orders`.`customers_lastname` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`orders`.`customers_id` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`orders`.`customers_cid` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`orders`.`customers_vat_id` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`orders`.`customers_gender` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`orders`.`customers_email_address` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`orders`.`customers_telephone` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`customers`.`customers_firstname` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`customers`.`customers_lastname` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`customers`.`customers_id` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`customers`.`customers_cid` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`customers`.`customers_vat_id` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`customers`.`customers_gender` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`customers`.`customers_email_address` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`customers`.`customers_telephone` LIKE "' . $filterParameters['customer'] . '"')
                    ->or_where('`customers`.`customers_fax` LIKE "' . $filterParameters['customer'] . '"');
            }
            $this->db->group_end();
        }
        
        // Filter by customer group.
        if (isset($filterParameters['group']) && is_array($filterParameters['group'])) {
            $groups = $filterParameters['group'];
            $this->db->group_start()->where('orders.customers_status', array_shift($groups));
            foreach ($groups as $group) {
                $this->db->or_where('orders.customers_status', $group);
            }
            $this->db->group_end();
        }
        
        // Filter by total sum. 
        if (isset($filterParameters['sum']) && is_array($filterParameters['sum'])) {
            $this->db->where('orders_total.value >=', $filterParameters['sum'][0]);
            $this->db->where('orders_total.value <=', $filterParameters['sum'][1]);
        } elseif (!empty($filterParameters['sum'])) {
            $filterParameters['sum'] = str_replace(',', '.', $filterParameters['sum']);
            if (strpos($filterParameters['sum'], '%') !== false) {
                $this->db->where('`orders_total`.`value` LIKE "' . $filterParameters['sum'] . '"');
            } else {
                $this->db->where('orders_total.value', $filterParameters['sum']);
            }
        }
        
        // Filter by payment. 
        if (isset($filterParameters['paymentMethod']) && is_array($filterParameters['paymentMethod'])) {
            $paymentMethods = $filterParameters['paymentMethod'];
            $this->db->group_start()->where('orders.payment_class', array_shift($paymentMethods));
            foreach ($paymentMethods as $payment) {
                $this->db->or_where('orders.payment_class', $payment);
            }
            $this->db->group_end();
        }
        
        // Filter by shipping method. 
        if (isset($filterParameters['shippingMethod']) && is_array($filterParameters['shippingMethod'])) {
            $shippingMethods = $filterParameters['shippingMethod'];
            $this->db->group_start()->where('orders.shipping_class', array_shift($shippingMethods));
            foreach ($shippingMethods as $shipping) {
                $this->db->or_where('orders.shipping_class', $shipping);
            }
            $this->db->group_end();
        }
        
        // Filter by country ISO code. 
        if (isset($filterParameters['countryIsoCode']) && is_array($filterParameters['countryIsoCode'])) {
            $countryIsoCodes = $filterParameters['countryIsoCode'];
            $this->db->group_start()->where('orders.delivery_country_iso_code_2', array_shift($countryIsoCodes));
            foreach ($countryIsoCodes as $countryIsoCode) {
                $this->db->or_where('orders.delivery_country_iso_code_2', $countryIsoCode);
            }
            $this->db->group_end();
        }
        
        // Filter by purchase date. 
        $dateFormat = ($_SESSION['language_code'] === 'de') ? 'd.m.y' : 'm.d.y';
        if (isset($filterParameters['date']) && is_array($filterParameters['date'])) {
            $dateValue = DateTime::createFromFormat($dateFormat, array_shift($filterParameters['date']));
            $this->db->where('orders.date_purchased >=', $dateValue->format('Y-m-d'));
            $dateValue = DateTime::createFromFormat($dateFormat, array_shift($filterParameters['date']));
            $this->db->where('orders.date_purchased <=', $dateValue->format('Y-m-d') . ' 23:59:59');
        } elseif (!empty($filterParameters['date'])) {
            $dateValue = DateTime::createFromFormat($dateFormat, $filterParameters['date']);
            $this->db->where('orders.date_purchased >=', $dateValue->format('Y-m-d'));
            $this->db->where('orders.date_purchased <=', $dateValue->format('Y-m-d') . ' 23:59:59');
        }
        
        // Filter by order status. 
        if (isset($filterParameters['status']) && is_array($filterParameters['status'])) {
            $this->db->group_start()->where('orders.orders_status', array_shift($filterParameters['status']));
            foreach ($filterParameters['status'] as $status) {
                $this->db->or_where('orders.orders_status', $status);
            }
            $this->db->group_end();
        }
        
        // Filter by total weight. 
        if (isset($filterParameters['totalWeight']) && is_array($filterParameters['totalWeight'])) {
            $this->db->where('orders.order_total_weight >=', array_shift($filterParameters['totalWeight']));
            $this->db->where('orders.order_total_weight <=', array_shift($filterParameters['totalWeight']));
        } elseif (!empty($filterParameters['totalWeight'])) {
            $this->db->where('`orders`.`order_total_weight` LIKE "' . $filterParameters['totalWeight'] . '"');
        }
        
        // Filter by invoice number.
        if (!empty($filterParameters['invoiceNumber'])) {
            $this->db->where('`invoices`.`invoice_number` LIKE "' . $filterParameters['invoiceNumber'] . '"');
        }
        
        return $this;
    }
}
