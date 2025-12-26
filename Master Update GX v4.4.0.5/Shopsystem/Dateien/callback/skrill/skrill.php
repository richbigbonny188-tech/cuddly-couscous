<?php
/* --------------------------------------------------------------
   skrill.php 2020-01-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
   
   based on:
   (c) 2009 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: skrill.php 22 2009-01-17 14:33:18Z mzanier $)
   
   Released under the GNU General Public License
   
   --------------------------------------------------------------
   
   2018-05-07 - Refactored to be PHP 7 compatible; modified logging to use LogControl
   
   --------------------------------------------------------------*/

class skrill_callback
{
    public function __construct()
    {
        $this->repost = false;
        $this->Error  = '';
        $this->oID    = 0;
        $this->debug  = true;
    }
    
    
    public function callback_process($data)
    {
        
        $this->data = $data;
        
        // order ID already inserted ?
        $err = $this->_CheckStatus();
        if (!$err) {
            return false;
        }
        
        // check if merchant ID matches
        $err = $this->_check_Merchant();
        
        if (!$err) {
            return false;
        }
        
        // validate md5signature (not implemented yet)
        $err = $this->_check_md5sig();
        if (!$err) {
            return false;
        }
        
        // validate transaction ID + Amount
        $err = $this->_check_TRID();
        if (!$err) {
            return false;
        }
        
        // transaction ID is correct,
        $this->_setStatus();
    }
    
    
    public function _getType($orders_id)
    {
        $this->statusPending   = _PAYMENT_SKRILL_PENDING_STATUS_ID;
        $this->statusCanceled  = _PAYMENT_SKRILL_CANCELED_STATUS_ID;
        $this->statusProcessed = _PAYMENT_SKRILL_PROCESSED_STATUS_ID;
        $this->PWD             = _PAYMENT_SKRILL_PWD;
        $this->merchantID      = _PAYMENT_SKRILL_MERCHANTID;
        $this->emailID         = _PAYMENT_SKRILL_EMAILID;
    }
    
    
    public function _CheckStatus()
    {
        
        $order_query = "SELECT `skrill_ORDERID` as `oid` FROM `payment_skrill` WHERE `skrill_TRID` = '"
                       . $this->data['transaction_id'] . "'";
        $order_query = xtc_db_query($order_query);
        $order_data  = xtc_db_fetch_array($order_query);
        
        if ($order_data['oid'] > 0) {
            $this->_getType($order_data['oid']);
            
            return true;
        }
        
        $this->Error  = '1005';
        $this->repost = true;
        
        return false;
    }
    
    
    public function _check_TRID()
    {
        // valid trid ?
        $query = "SELECT `skrill_TRID`,`skrill_ORDERID` FROM `payment_skrill` WHERE `skrill_TRID` = '"
                 . $this->data['transaction_id'] . "'";
        $query = xtc_db_query($query);
        if (!xtc_db_num_rows($query)) {
            $this->Error = '1002';
            
            return false;
        }
        // ok Insert mb transaction ID
        $query = "UPDATE `payment_skrill` SET `skrill_MBTID` ='" . $this->data['skrill_transaction_id']
                 . "'  WHERE skrill_TRID = '" . $this->data['transaction_id'] . "'";
        $query = xtc_db_query($query);
        
        return true;
    }
    
    
    public function _setStatus()
    {
        
        switch ($this->data['status']) {
            
            // processed
            case 2 :
                $result = xtc_db_query(
                    "UPDATE `payment_skrill` SET `skrill_ERRNO` = '200', `skrill_ERRTXT` = 'OK', `skrill_MBTID` = '"
                    . $this->data['skrill_transaction_id'] . "', skrill_STATUS = '" . $this->data['status']
                    . "' WHERE skrill_TRID = '" . $this->data['transaction_id'] . "'"
                );
                $status = $this->statusProcessed;
                $text   = 'OK, Payment received';
                break;
            
            // canceled
            case -2 :
            case -1 :
                $result = xtc_db_query(
                    "UPDATE `payment_skrill` SET `skrill_ERRNO` = '999', `skrill_ERRTXT` = 'Transaction failed.', `skrill_MBTID` = '"
                    . $this->data['skrill_transaction_id'] . "', skrill_STATUS = '" . $this->data['status']
                    . "' WHERE skrill_TRID = '" . $this->data['transaction_id'] . "'"
                );
                $status = $this->statusCanceled;
                $text   = 'ERROR, Transaction Failed';
                break;
            
            case 1 :
                $result = xtc_db_query(
                    "UPDATE `payment_skrill` SET `skrill_ERRNO` = '200', `skrill_ERRTXT` = 'PENDING', `skrill_MBTID` = '"
                    . $this->data['skrill_transaction_id'] . "', skrill_STATUS = '" . $this->data['status']
                    . "' WHERE skrill_TRID = '" . $this->data['transaction_id'] . "'"
                );
                $status = $this->statusPending;
                $text   = 'WAIT, Transaction Pending';
                break;
        }
        
        $order_query = "SELECT `skrill_ORDERID` as `oid` FROM `payment_skrill` WHERE `skrill_TRID` = '"
                       . $this->data['transaction_id'] . "'";
        $order_query = xtc_db_query($order_query);
        $order_data  = xtc_db_fetch_array($order_query);
    
        if (isset($status)) {
            $insertId = new IdType((int)$order_data['oid']);
            /** @var OrderWriteServiceInterface $orderWriteService */
            $orderWriteService = StaticGXCoreLoader::getService('OrderWrite');
            $orderWriteService->updateOrderStatus($insertId,
                                                  new IntType((int)$status),
                                                  new StringType(''),
                                                  new BoolType(false));
        }
        
        $this->_notifyTransaction($order_data['oid'], $text);
    }
    
    
    public function _check_md5sig()
    {
        
        if ($this->PWD == '') {
            return true;
        }
        
        $secret = $this->PWD;
        $md5sec = strtoupper(md5($secret));
        $hash   = $this->data['merchant_id'] . $this->data['transaction_id'] . $md5sec . $this->data['skrill_amount']
                  . $this->data['skrill_currency'] . $this->data['status'];
        $hash   = strtoupper(md5($hash));
        if ($hash != $this->data['md5sig']) {
            $this->Error = '1004';
            
            return false;
        }
        
        return true;
    }
    
    
    public function _check_Merchant()
    {
        
        // does merchant ID exists ?
        if (!isset ($this->data['merchant_id']) || $this->data['merchant_id'] != $this->merchantID) {
            $this->Error = '1001';
            $this->EInfo = 'Merchant ID SEND:' . $this->data['merchant_id'] . ' Merchant ID STORED:'
                           . $this->merchantID;
            
            return false;
        }
        // merchant mail ?
        if (!isset ($this->data['pay_to_email']) || $this->data['pay_to_email'] != $this->emailID) {
            $this->Error = '1003';
            $this->EInfo = 'Merchant EMAIL SEND:' . $this->data['pay_to_email'] . ' Merchant EMAIL STORED:'
                           . $this->emailID;
            
            return false;
        }
        
        return true;
    }
    
    
    public function _getError($error)
    {
        
        if ($error == '') {
            return false;
        }
        
        switch ($error) {
            
            case '1001' :
                $txt = 'merchant id does not match ' . $this->EInfo;
                break;
            case '1002' :
                $txt = 'transaction id doest not match';
                break;
            case '1003' :
                $txt = 'merchant email does not match ' . $this->EInfo;
                break;
            case '1004' :
                $txt = 'md5 signature does not match';
                break;
            case '1005' :
                $txt = 'order id not inserted yet';
                break;
        }
        
        // update order text
        if ($this->data['skrill_transaction_id'] != '') {
            xtc_db_query(
                "UPDATE `payment_skrill` SET `skrill_ERRNO` = '999', `skrill_ERRTXT` = '" . $txt
                . "' WHERE `skrill_MBTID` = '" . $this->data['skrill_transaction_id'] . "'"
            );
        }
        
        return $txt;
    }
    
    
    public function _logTransactions()
    {
        $error = $this->_getError($this->Error, $this->data);
        if ($error == '') {
            $error = 'OK';
        }
        
        $line = 'MB TRANS|' . date("d.m.Y H:i") . '|' . xtc_get_ip_address() . '|' . $error . '|';
        
        foreach ($_POST as $key => $val) {
            $line .= $key . ':' . $val . '|';
        }
        
        $logControl = LogControl::get_instance(true);
        $logControl->notice($line, 'payment', 'payment-skrill');
    }
    
    
    public function _notifyTransaction($oID, $text)
    {
        $email_body = "Order ID: " . $oID . "\n" . 'Message: ' . $text . "\n\n";
        
        if (EMAIL_TRANSPORT === 'smtp') {
            require_once DIR_WS_CLASSES . 'class.smtp.php';
        }
        
        xtc_php_mail(
            EMAIL_BILLING_ADDRESS,
            EMAIL_BILLING_NAME,
            EMAIL_BILLING_ADDRESS,
            STORE_NAME,
            EMAIL_BILLING_FORWARDING_STRING,
            EMAIL_BILLING_ADDRESS,
            STORE_NAME,
            '',
            '',
            'Skrill Payment Notification', $email_body, $email_body);
	}

 }
