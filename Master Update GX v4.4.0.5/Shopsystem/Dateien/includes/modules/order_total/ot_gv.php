<?php
/* --------------------------------------------------------------
   ot_gv.php 2020-11-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_gv.php,v 1.37.3 2004/01/01); www.oscommerce.com
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: ot_gv.php 1185 2005-08-26 15:16:31Z mz $)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once DIR_FS_INC . 'xtc_get_currencies_values.inc.php';

class ot_gv_ORIGIN {
    public $code;
    public $title;
    public $header;
    public $description;
    public $user_prompt;
    public $enabled;
    public $sort_order;
    public $include_shipping;
    public $include_tax;
    public $calculate_tax;
    public $credit_tax;
    public $tax_class;
    public $show_redeem_box;
    public $credit_class;
    public $output;

    protected $deduction;
    protected $useGVBalance;
    protected $vouchersActive;
    
	public function __construct() {
        $this->code             = 'ot_gv';
        $this->credit_class     = true;
        $this->title            = defined('MODULE_ORDER_TOTAL_GV_TITLE') ? MODULE_ORDER_TOTAL_GV_TITLE : '';
        $this->header           = defined('MODULE_ORDER_TOTAL_GV_HEADER') ? MODULE_ORDER_TOTAL_GV_HEADER : '';
        $this->description      = defined('MODULE_ORDER_TOTAL_GV_DESCRIPTION') ? MODULE_ORDER_TOTAL_GV_DESCRIPTION : '';
        $this->user_prompt      = defined('MODULE_ORDER_TOTAL_GV_USER_PROMPT') ? MODULE_ORDER_TOTAL_GV_USER_PROMPT : '';
        $this->enabled          = defined('MODULE_ORDER_TOTAL_GV_STATUS') ? MODULE_ORDER_TOTAL_GV_STATUS : 'false';
        $this->sort_order       = defined('MODULE_ORDER_TOTAL_GV_SORT_ORDER') ? MODULE_ORDER_TOTAL_GV_SORT_ORDER : '0';
        $this->include_shipping = defined(
            'MODULE_ORDER_TOTAL_GV_INC_SHIPPING'
        ) ? MODULE_ORDER_TOTAL_GV_INC_SHIPPING : 'false';
        $this->include_tax      = defined('MODULE_ORDER_TOTAL_GV_INC_TAX') ? MODULE_ORDER_TOTAL_GV_INC_TAX : 'true';
        $this->calculate_tax    = defined('MODULE_ORDER_TOTAL_GV_CALC_TAX') ? MODULE_ORDER_TOTAL_GV_CALC_TAX : 'true';
        $this->credit_tax       = defined(
            'MODULE_ORDER_TOTAL_GV_CREDIT_TAX'
        ) ? MODULE_ORDER_TOTAL_GV_CREDIT_TAX : 'false';
        $this->tax_class        = defined('MODULE_ORDER_TOTAL_GV_TAX_CLASS') ? MODULE_ORDER_TOTAL_GV_TAX_CLASS : '0';
        $this->show_redeem_box  = defined('MODULE_ORDER_TOTAL_GV_REDEEM_BOX') ? MODULE_ORDER_TOTAL_GV_REDEEM_BOX : '';
		$this->output = [];
        
        $this->deduction = 0;
        $this->useGVBalance = !isset ($_SESSION['cot_gv']) || (bool)$_SESSION['cot_gv'] === true;
        $_SESSION['cot_gv'] = $this->useGVBalance;
        $this->vouchersActive = !empty($_SESSION['gift_vouchers']);
	}
    
    
    public function process(): void
    {
        $order = $GLOBALS['order'];
        $xtPrice = $GLOBALS['xtPrice'];
        $order_total = $this->get_order_total();
        $od_amount   = $this->calculate_credit($order_total, $this->useGVBalance);
    
        if ($od_amount > 0) {
            $this->deduction      = $od_amount;
            $order->info['total'] -= $od_amount;
        
            $this->output[] = [
                'title' => $this->title . ':',
                'text'  => '-' . $xtPrice->xtcFormat($od_amount, true),
                'value' => $xtPrice->xtcFormat($od_amount, false),
            ];
        }
    }
    
    
	public function pre_confirmation_check($order_total): float
    {
		$od_amount = 0.0;
		if ($order_total > 0 && ($this->useGVBalance || $this->vouchersActive)) {
            $order = $GLOBALS['order'];
			if (strtolower($this->include_tax) === 'false') {
				$order_total -= $order->info['tax'];
			}
			if (strtolower($this->include_shipping) === 'false') {
				$order_total -= $order->info['shipping_cost'];
			}
            $od_amount = $this->calculate_credit($order_total, $this->useGVBalance);
		}
		return $od_amount;
	}
    
    
    public function use_credit_amount()
    {
        return '';
    }
    
    
    /**
     * processes GIFT products, i.e. creates entries in the coupon queue.
     *
     * @param $productsIndex int index in $GLOBALS['order']->products
     */
    public function update_credit_account($productsIndex): void
    {
        // N.B.: Non-queue mode is no longer supported!
        $order       = $GLOBALS['order'];
        $insert_id   = $GLOBALS['insert_id'];
        $REMOTE_ADDR = $GLOBALS['REMOTE_ADDR'];
        if (preg_match('/^GIFT_(\d+)/', addslashes($order->products[$productsIndex]['model']), $matches)) {
            /** @var OrderReadServiceInterface $orderReadService */
            $orderReadService = StaticGXCoreLoader::getService('OrderRead');
            /** @var OrderInterface $gxOrder */
            $gxOrder = $orderReadService->getOrderById(new IdType((int)$insert_id));
            $orderItems = $gxOrder->getOrderItems()->getArray();
            /** @var StoredOrderItem $orderItem */
            $orderItem = $orderItems[$productsIndex];

            // Voucher value is determined by the product’s raw price; selling price might be a reduced special price.
            /** @var ProductReadService $productsReadService */
            $productsReadService = StaticGXCoreLoader::getService('ProductRead');
            $product = $productsReadService->getProductById(new IdType((int)$order->products[$productsIndex]['id']));
            $gv_order_amount = $product->getPrice();

            if (strtolower($this->credit_tax) === 'true') {
                $gv_order_amount = $gv_order_amount * (100 + $order->products[$productsIndex]['tax']) / 100;
            }
            $gv_order_amount = $gv_order_amount * 100 / 100;
    
            $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
            for ($i = 0; $i < $order->products[$productsIndex]['qty']; $i++) {
                $db->insert('coupon_gv_queue',
                            [
                                'customer_id'        => $_SESSION['customer_id'],
                                'order_id'           => $insert_id,
                                'amount'             => $gv_order_amount,
                                'date_created'       => date('Y-m-d H:i:s'),
                                'ipaddr'             => (string)$REMOTE_ADDR,
                                'orders_products_id' => $orderItem->getOrderItemId(),
                            ]);
            }
        }
    }
    
    
    public function credit_selection(): string
    {
        return '';
    }
    
    
    public function apply_credit(): float
    {
        $ordersId = new IdType((int)$GLOBALS['insert_id']);
        /** @var \xtcPrice_ORIGIN $xtPrice */
        $xtPrice = $GLOBALS['xtPrice'];
        $availableDeduction = $xtPrice->xtcRemoveCurr($this->deduction);
        $remainingDeduction = $availableDeduction;
        $actualDeduction = 0;

        if ($this->useGVBalance) {
            $customerGVBalance = $this->getCustomerGVBalance(new IdType((int)$_SESSION['customer_id']));
            if ($customerGVBalance > 0) {
                $customerBalanceDeduction = min($availableDeduction, $customerGVBalance);
                $this->reduceCustomerGVBalance(new IdType((int)$_SESSION['customer_id']), $customerBalanceDeduction);
                $actualDeduction += $customerBalanceDeduction;
                $remainingDeduction -= $customerBalanceDeduction;
                $this->logDeduction($ordersId, new NonEmptyStringType('balance'), new DecimalType($customerBalanceDeduction));
            }
        }
        
        if ($this->vouchersActive && $remainingDeduction > 0) {
            foreach ($_SESSION['gift_vouchers'] as $couponID => $voucherData) {
                $couponData = $this->getCouponDetails(new IdType($couponID));
                if (!empty($couponData) && $couponData['coupon_active'] === 'Y') {
                    $deductionFromCoupon = $this->deductFromCoupon(new IdType($couponID), $remainingDeduction);
                    $this->logDeduction($ordersId, new NonEmptyStringType((string)$couponData['coupon_code']), new DecimalType($deductionFromCoupon));
                    $remainingDeduction -= $deductionFromCoupon;
                    $actualDeduction += $deductionFromCoupon;
                }
                unset ($_SESSION['gift_vouchers'][$couponID]);
            }
        }

        unset($_SESSION['gift_vouchers_amount']);
        $actualDeduction = $xtPrice->xtcCalculateCurr($actualDeduction);
        return $actualDeduction;
    }

    protected function logDeduction(IdType $ordersId, NonEmptyStringType $couponIdentifier, DecimalType $amount): void
    {
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->insert('coupon_gv_redeem_track',
                    [
                        'orders_id'   => $ordersId->asInt(),
                        'coupon_code' => $couponIdentifier->asString(),
                        'amount'      => $amount->asDecimal(),
                    ]
        );
    }
    
    
    public function collect_posts(): void
    {
        // method deprecated
    }
    
    
    public function calculate_credit($amount, $useGVBalance = true): float
    {
        if ($amount <= 0) {
            return 0;
        }
        $_SESSION['gift_vouchers_amount'] = $amount;
        $currencyData   = xtc_get_currencies_values($_SESSION['currency']);
        $currencyFactor = (float)$currencyData['value'];
    
        $customerID    = (int)$_SESSION['customer_id'];
        $legacyBalance = 0.0;
        if ($useGVBalance && $customerID !== 0) {
            $legacyBalance = $this->getCustomerGVBalance(new IdType($customerID));
            $legacyBalance *= $currencyFactor;
        }
        
        $totalBalance = $legacyBalance;
        
        if (isset($_SESSION['gift_vouchers']) && is_array($_SESSION['gift_vouchers'])) {
            foreach ($_SESSION['gift_vouchers'] as $couponID => $voucherData) {
                $couponData = $this->getCouponDetails(new IdType((int)$couponID));
                if ($couponData['coupon_active'] === 'Y') {
                    $totalBalance += $currencyFactor * $couponData['coupon_amount'];
                } else {
                    unset($_SESSION['gift_vouchers'][$couponID]);
                }
            }
        }
        
        $totalBalance     = round($totalBalance, 2);
        $applicableCredit = min($amount, $totalBalance);
        
        return $applicableCredit;
    }
    
    
    public function calculate_tax_deduction($amount, $od_amount, $method): int
    {
        return 0; // no tax deduction for gift vouchers
    }
    
    
    public function get_order_total(): float
    {
        $order = $GLOBALS['order'];
        $order_total = 0.00;
        if ((int)$_SESSION['customers_status']['customers_status_show_price_tax'] !== 0) {
            $order_total = $order->info['total'];
        }
        if ((int)$_SESSION['customers_status']['customers_status_show_price_tax'] === 0
            && (int)$_SESSION['customers_status']['customers_status_add_tax_ot'] === 1) {
            $order_total = $order->info['tax'] + $order->info['total'];
        }
        if ((int)$_SESSION['customers_status']['customers_status_show_price_tax'] === 0
            && (int)$_SESSION['customers_status']['customers_status_add_tax_ot'] === 0) {
            $order_total = $order->info['total'];
        }
        if (strtolower($this->include_tax) === 'false') {
            $order_total -= $order->info['tax'];
        }
        if (strtolower($this->include_shipping) === 'false') {
            $order_total -= $order->info['shipping_cost'];
        }
        
        return $order_total;
    }
    
    
    public function check() {
		if (!isset ($this->check)) {
			$check_query = xtc_db_query("select `value` from `gx_configurations` where `key` = 'configuration/MODULE_ORDER_TOTAL_GV_STATUS'");
			$this->check = xtc_db_num_rows($check_query);
		}

		return $this->check;
	}
    
    
    public function keys(): array
    {
        return [
            'configuration/MODULE_ORDER_TOTAL_GV_STATUS',
            'configuration/MODULE_ORDER_TOTAL_GV_SORT_ORDER',
            'configuration/MODULE_ORDER_TOTAL_GV_QUEUE',
            'configuration/MODULE_ORDER_TOTAL_GV_INC_SHIPPING',
            'configuration/MODULE_ORDER_TOTAL_GV_INC_TAX',
            'configuration/MODULE_ORDER_TOTAL_GV_CALC_TAX',
            'configuration/MODULE_ORDER_TOTAL_GV_TAX_CLASS',
            'configuration/MODULE_ORDER_TOTAL_GV_CREDIT_TAX',
            'configuration/MODULE_ORDER_TOTAL_GV_SHOW_INFO',
        ];
    }
    
    
    public function install(): void
    {
		xtc_db_query("insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_GV_STATUS', 'true', '6', '1','switcher')");
		xtc_db_query("insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`) values ('configuration/MODULE_ORDER_TOTAL_GV_SORT_ORDER', '80', '6', '2')");
		xtc_db_query("insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_GV_QUEUE', 'true', '6', '3','switcher')");
		xtc_db_query("insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'true', '6', '5', 'switcher')");
		xtc_db_query("insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_GV_INC_TAX', 'true', '6', '6','switcher')");
		xtc_db_query("insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None', '6', '7','tax-calculation-mode')");
		xtc_db_query("insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_GV_TAX_CLASS', '0', '6', '0', 'tax-class')");
		xtc_db_query("insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_GV_CREDIT_TAX', 'false', '6', '8','switcher')");
        xtc_db_query("INSERT INTO `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) VALUES ('configuration/MODULE_ORDER_TOTAL_GV_SHOW_INFO', 'false', '6', '9','switcher')");

		xtc_db_query("UPDATE `gx_configurations` SET `value` = 'true' WHERE `key` = 'configuration/MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING'");
	}

	public function remove(): void
    {
		xtc_db_query("delete from `gx_configurations` where `key` in ('".implode("', '", $this->keys())."')");
	}
    
    
    /**
     * Returns a customer’s current balance from coupons/vouchers.
     *
     * @param \IdType $customerID
     *
     * @return float
     */
    protected function getCustomerGVBalance(IdType $customerID): float
    {
        $balance = 0.0;
        if ($customerID->asInt() !== 0) {
            $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
            $customerGvRow = $db->get_where('coupon_gv_customer', ['customer_id' => $customerID->asInt()])->row_array();
            if (!empty($customerGvRow)) {
                $balance = (float)$customerGvRow['amount'];
            }
        }
        return $balance;
    }
    
    
    /**
     * Reduces a customer’s balance by up to $reduction, returns actual deduction.
     *
     * @param \IdType $customerID
     * @param         $reduction
     *
     * @return float
     */
    protected function reduceCustomerGVBalance(IdType $customerID, $reduction): float
    {
        $reduction = (float)$reduction;
        $actualReduction = 0.0;
        $oldBalance = $this->getCustomerGVBalance($customerID);
        if ($oldBalance > $reduction) {
            $newBalance = $oldBalance - $reduction;
            $actualReduction = $reduction;
        } else {
            $newBalance = 0;
            $actualReduction = $oldBalance;
        }
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->replace('coupon_gv_customer', ['amount' => $newBalance, 'customer_id' => $customerID->asInt()]);
        return $actualReduction;
    }
    
    
    protected function deductFromCoupon(IdType $couponID, $reduction): float
    {
        $reduction = (float)$reduction;
        $actualReduction = 0.0;
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $couponData = $db->get_where('coupons', ['coupon_id' => $couponID->asInt()])->row_array();
        if ($couponData['coupon_active'] === 'Y' && $couponData['coupon_type'] === 'G') {
            $oldAmount = (float)$couponData['coupon_amount'];
            if ($oldAmount > $reduction) {
                $newAmount = $oldAmount - $reduction;
                $actualReduction = $reduction;
                $db->update(
                    'coupons',
                    ['coupon_amount' => $newAmount],
                    ['coupon_id' => $couponID->asInt()]
                );
            } else {
                $newAmount = 0;
                $actualReduction = $oldAmount;
                $db->update(
                    'coupons',
                    ['coupon_amount' => $newAmount, 'coupon_active' => 'N'],
                    ['coupon_id' => $couponID->asInt()]
                );
                $this->addToCouponRedeemTrack(
                    $couponID,
                    new IdType((int)$_SESSION['customer_id']),
                    $_SERVER['REMOTE_ADDR'],
                    new IdType((int)$GLOBALS['insert_id'])
                );
            }
        }
        
        return $actualReduction;
    }
    
    protected function getCouponDetails(IdType $couponID): ?array
    {
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $couponRow = $db->get_where('coupons', ['coupon_id' => $couponID->asInt()])->row_array();
        return $couponRow;
    }
    
    protected function addToCouponRedeemTrack(IdType $couponID, IdType $customerID, $remoteAddress, IdType $ordersID): void
    {
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->insert('coupon_redeem_track',
                    [
                        'coupon_id'   => $couponID->asInt(),
                        'customer_id' => $customerID->asInt(),
                        'redeem_date' => date('Y-m-d H:i:s'),
                        'redeem_ip'   => $remoteAddress,
                        'order_id'    => $ordersID->asInt(),
                    ]
        );
    }
    
}

MainFactory::load_origin_class('ot_gv');
