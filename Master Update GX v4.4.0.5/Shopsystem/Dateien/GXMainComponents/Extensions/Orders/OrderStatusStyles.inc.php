<?php

/* --------------------------------------------------------------
   OrderStatusStyles.inc.php 2021-01-14
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class OrderStatusStyles
 *
 * This class works in cooperation with the "admin/html/content/layouts/main/partial/order_status_styles.html" to
 * provide the dynamic styling of the order status labels.
 *
 * @category   System
 * @package    Extensions
 * @subpackage Orders
 */
class OrderStatusStyles
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    
    /**
     * OrderStatusStyles constructor.
     *
     * @param CI_DB_query_builder $db
     */
    public function __construct(CI_DB_query_builder $db)
    {
        $this->db = $db;
    }
    
    
    /**
     * Get the order status styles for the "order_status_styles.html" partial.
     *
     * Include the order_status_styles.html in your template and pass in a variable with the "order_status_styles"
     * name. The template will output a <style> tag with the colors for each status.
     *
     * @return array
     *
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    public function getStyles()
    {
        $rows = $this->db->select('orders_status_id, color')
            ->from('orders_status')
            ->where('language_id', $_SESSION['languages_id'])
            ->get()
            ->result_array();
        
        $orderStatusStyles = [];
        
        foreach ($rows as $row) {
            $color = $row['color'];
            if ($row['color'] === '') {
                $color = '#FFFFFF'; // Use fallback color to prevent errors if color is not set properly
            }
            
            $orderStatusStyles[] = [
                'id'               => $row['orders_status_id'],
                'color'            => ColorHelper::getLuminance(new StringType($color)) > 143 ? '#333' : '#FFF',
                'background_color' => '#' . $row['color']
            ];
        }
        
        return $orderStatusStyles;
    }
}