<?php
/* --------------------------------------------------------------
   EustandardtransferPaymentDetailsProvider.inc.php 2020-10-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class EustandardtransferPaymentDetailsProvider implements PaymentDetailsProvider
{
    public function getDetails(IdType $orderId)
    {
        $details = [
            'bankname'       => (string)@constant('MODULE_PAYMENT_EUTRANSFER_BANKNAM'),
            'branch'         => (string)@constant('MODULE_PAYMENT_EUTRANSFER_BRANCH'),
            'account_holder' => (string)@constant('MODULE_PAYMENT_EUTRANSFER_ACCNAM'),
            'account_number' => (string)@constant('MODULE_PAYMENT_EUTRANSFER_ACCNUM'),
            'iban'           => (string)@constant('MODULE_PAYMENT_EUTRANSFER_ACCIBAN'),
            'bic'            => (string)@constant('MODULE_PAYMENT_EUTRANSFER_BANKBIC'),
            'sort_code'      => (string)@constant('MODULE_PAYMENT_EUTRANSFER_SORT_ORDER'),
        ];
        
        return $details;
    }
}
