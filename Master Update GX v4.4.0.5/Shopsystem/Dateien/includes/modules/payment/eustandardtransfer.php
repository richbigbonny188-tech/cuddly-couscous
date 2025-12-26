<?php
/* --------------------------------------------------------------
   eustandardtransfer.php 2020-10-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2014 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ptebanktransfer.php,v 1.4.1 2003/09/25 19:57:14); www.oscommerce.com
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: eustandardtransfer.php 998 2005-07-07 14:18:20Z mz $)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class eustandardtransfer_ORIGIN
{
    public $code, $title, $description, $enabled;
    
    
    // class constructor
    /**
     * @var string
     */
    public $sort_order;
    /**
     * @var string
     */
    public $info;
    
    
    public function __construct()
    {
        $this->code        = 'eustandardtransfer';
        $this->title       = defined('MODULE_PAYMENT_EUTRANSFER_TEXT_TITLE') ? MODULE_PAYMENT_EUTRANSFER_TEXT_TITLE : '';
        $this->description = defined('MODULE_PAYMENT_EUTRANSFER_TEXT_DESCRIPTION')
                             && defined('MODULE_PAYMENT_EUTRANSFER_BANKNAM') ? sprintf(MODULE_PAYMENT_EUTRANSFER_TEXT_DESCRIPTION,
                                                                                       MODULE_PAYMENT_EUTRANSFER_BANKNAM,
                                                                                       MODULE_PAYMENT_EUTRANSFER_BRANCH,
                                                                                       MODULE_PAYMENT_EUTRANSFER_ACCNAM,
                                                                                       MODULE_PAYMENT_EUTRANSFER_ACCNUM,
                                                                                       MODULE_PAYMENT_EUTRANSFER_ACCIBAN,
                                                                                       MODULE_PAYMENT_EUTRANSFER_BANKBIC,
                                                                                       MODULE_PAYMENT_EUTRANSFER_SORT_CODE) : '';
        $this->sort_order  = defined('MODULE_PAYMENT_EUTRANSFER_SORT_ORDER') ? MODULE_PAYMENT_EUTRANSFER_SORT_ORDER : '0';
        $this->info        = defined('MODULE_PAYMENT_EUTRANSFER_TEXT_INFO') ? MODULE_PAYMENT_EUTRANSFER_TEXT_INFO : '';
        $this->enabled     = defined('MODULE_PAYMENT_EUTRANSFER_STATUS') && filter_var(constant('MODULE_PAYMENT_EUTRANSFER_STATUS'), FILTER_VALIDATE_BOOLEAN);
    }
    
    
    // class methods
    public function javascript_validation()
    {
        return false;
    }
    
    
    public function selection()
    {
        return ['id' => $this->code, 'module' => $this->title, 'description' => $this->info];
    }
    //    function selection() {
    //      return false;
    //    }
    
    public function pre_confirmation_check()
    {
        return false;
    }
    
    // I take no credit for this, I just hunted down variables, the actual code was stolen from the 2checkout
    // module.  About 20 minutes of trouble shooting and poof, here it is. -- Thomas Keats
    public function confirmation()
    {
        global $_POST;
        
        $confirmation = [
            'title'       => $this->title . ': ' . $this->check,
            'fields'      => [['title' => $this->description]],
            'description' => $this->info
        ];
        
        return $confirmation;
    }
    
    
    public function process_button()
    {
        return false;
    }
    
    
    public function before_process()
    {
        return false;
    }
    
    
    public function after_process()
    {
        if ($this->order_status) {
            $insertId = new IdType((int)$GLOBALS['insert_id']);
            /** @var OrderWriteServiceInterface $orderWriteService */
            $orderWriteService = StaticGXCoreLoader::getService('OrderWrite');
            $orderWriteService->updateOrderStatus($insertId,
                                                  new IntType((int)$this->order_status),
                                                  new StringType(''),
                                                  new BoolType(false));
        }
    }
    
    
    public function output_error()
    {
        return false;
    }
    
    
    // BOF GM_MOD
    public function payment_action()
    {
        return false;
    }
    
    
    // EOF GM_MOD
    
    public function check()
    {
        if (!isset ($this->check)) {
            $check_query = xtc_db_query("select `value` from `gx_configurations` where `key` = 'configuration/MODULE_PAYMENT_EUTRANSFER_STATUS'");
            $this->check = xtc_db_num_rows($check_query);
        }
        
        return $this->check;
    }
    
    
    public function install()
    {
        xtc_db_query("insert into `gx_configurations` ( `key`, `value`,  `legacy_group_id`, `sort_order`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED', '', '6', '0', now())");
        xtc_db_query("insert into `gx_configurations` (`key`, `value`,`legacy_group_id`, `sort_order`, `type`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUTRANSFER_STATUS', 'True', '6', '3', 'switcher', now());");
        xtc_db_query("insert into `gx_configurations` (`key`, `value`,`legacy_group_id`, `sort_order`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUTRANSFER_BANKNAM', '---',  '6', '1', now());");
        xtc_db_query("insert into `gx_configurations` (`key`, `value`,`legacy_group_id`, `sort_order`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUTRANSFER_BRANCH', '---', '6', '1', now());");
        xtc_db_query("insert into `gx_configurations` (`key`, `value`,`legacy_group_id`, `sort_order`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUTRANSFER_ACCNAM', '---',  '6', '1', now());");
        xtc_db_query("insert into `gx_configurations` (`key`, `value`,`legacy_group_id`, `sort_order`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUTRANSFER_ACCNUM', '---',  '6', '1', now());");
        xtc_db_query("insert into `gx_configurations` (`key`, `value`,`legacy_group_id`, `sort_order`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUTRANSFER_ACCIBAN', '---',  '6', '1', now());");
        xtc_db_query("insert into `gx_configurations` (`key`, `value`,`legacy_group_id`, `sort_order`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUTRANSFER_BANKBIC', '---',  '6', '1', now());");
        xtc_db_query("insert into `gx_configurations` (`key`, `value`,`legacy_group_id`, `sort_order`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUTRANSFER_SORT_CODE', '---',  '6', '0', now())");
        xtc_db_query("insert into `gx_configurations` (`key`, `value`,`legacy_group_id`, `sort_order`, `last_modified`) values ('configuration/MODULE_PAYMENT_EUTRANSFER_SORT_ORDER', '0',  '6', '0', now())");
    }
    
    
    public function remove()
    {
        xtc_db_query("delete from `gx_configurations` where `key` in ('" . implode("', '", $this->keys()) . "')");
    }
    
    
    public function keys()
    {
        $keys = [
            'configuration/MODULE_PAYMENT_EUTRANSFER_STATUS',
            'configuration/MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED',
            'configuration/MODULE_PAYMENT_EUTRANSFER_BANKNAM',
            'configuration/MODULE_PAYMENT_EUTRANSFER_BRANCH',
            'configuration/MODULE_PAYMENT_EUTRANSFER_ACCNAM',
            'configuration/MODULE_PAYMENT_EUTRANSFER_ACCNUM',
            'configuration/MODULE_PAYMENT_EUTRANSFER_ACCIBAN',
            'configuration/MODULE_PAYMENT_EUTRANSFER_BANKBIC',
            'configuration/MODULE_PAYMENT_EUTRANSFER_SORT_CODE',
            'configuration/MODULE_PAYMENT_EUTRANSFER_SORT_ORDER'
        ];
        
        return $keys;
    }
}

MainFactory::load_origin_class('eustandardtransfer');
