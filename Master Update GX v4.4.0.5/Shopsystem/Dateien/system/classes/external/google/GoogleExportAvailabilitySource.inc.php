<?php
/* --------------------------------------------------------------
   GoogleExportAvailabilitySource.inc.php 2021-04-09
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Description of GoogleAvailabilitySource
 */
class GoogleExportAvailabilitySource
{	
	protected $v_data_array = array();
    
    
    /**
     * GoogleExportAvailabilitySource constructor.
     */
	public function __construct()
	{
		$t_sql = 'SELECT 
                        a.*, 
                        b.shipping_status_id 
                    FROM google_export_availability a 
                    LEFT JOIN shipping_status_to_google_availability b USING (google_export_availability_id)';
		$t_result = xtc_db_query( $t_sql );
		while( $t_row = xtc_db_fetch_array( $t_result ) )
		{
			foreach( $t_row AS $t_google_export_availability_key => $t_google_export_availability_value )
			{
				$this->v_data_array[ $t_row['language_id'] ][ $t_row[ 'google_export_availability_id' ] ][ $t_google_export_availability_key ] = $t_google_export_availability_value;
			}
		}
	}

    /**
     * @deprecated not used anywhere
     */
	public function get_google_export_availabilities(  )
	{
		return $this->v_data_array;
	}
	
	public function get_google_export_availability( $p_google_export_availability_id, $language_id )
	{
	    return $this->v_data_array[$language_id][$p_google_export_availability_id] ?? [];
	}

    public function getAvailabilityByShippingStatusId( $shippingStatusId, $language_id )
    {
        foreach ($this->v_data_array[$language_id] as $availability) {
            if ((int)$availability['shipping_status_id'] === (int)$shippingStatusId){
                return $availability;
            }
        }
        
        return [];
    }
}