<?php
/* --------------------------------------------------------------
   ShippingAndPaymentMatrixThemeContentView.inc.php 2020-10-21
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
 */

class ShippingAndPaymentMatrixThemeContentView extends ThemeContentView
{
    public function __construct()
    {
        $this->set_content_template('shipping_and_payment_matrix.html');
    }
    
    
    //No flat assigns for gambio template
    public function init_smarty()
    {
        parent::init_smarty();
        $this->set_flat_assigns(false);
    }
    
    
    public function prepare_data()
    {
        $coo_language_text_manager = MainFactory::create_object('LanguageTextManager',
                                                                ['countries', $_SESSION['languages_id']]);
        $t_content                 = [];
        
        $t_query  = 'SELECT
						*
					FROM
						shipping_and_payment_matrix
					WHERE
						language_id = ' . $_SESSION['languages_id']
                    .' order by country_code ASC';
        $t_result = xtc_db_query($t_query);
        while ($t_row = xtc_db_fetch_array($t_result)) {
            $t_row['country'] = $coo_language_text_manager->get_text(strtoupper($t_row['country_code']));
            $t_content[]      = $t_row;
        }
        $this->set_content_data('content', $t_content);
    }
}
