<?php
/* --------------------------------------------------------------
  TrackingCodesContentView.inc.php 2020-09-01
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2017 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
 */

use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceReadService;
use Gambio\Admin\Modules\TrackingCode\Services\TrackingCodeReadService;

/**
 * Class TrackingCodesContentView
 */
class TrackingCodesContentView extends ContentView
{
    protected $orderId;
    
    protected $pageToken;
    
    /**
     * @var TrackingCodeReadService
     */
    protected $trackingCodeService;
    
    /**
     * @var ParcelServiceReadService
     */
    protected $parcelServiceService;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->set_template_dir(DIR_FS_CATALOG . 'admin/html/content/tracking/');
        $this->set_content_template('tracking_codes.html');
        
        $container = LegacyDependencyContainer::getInstance();
        
        $this->trackingCodeService  = $container->get(TrackingCodeReadService::class);
        $this->parcelServiceService = $container->get(ParcelServiceReadService::class);
    }
    
    
    public function prepare_data()
    {
        $parcelTrackingCodesArray = $this->trackingCodeService->getTrackingCodesByOrderId($this->orderId);
        
        $this->set_content_data('parcel_tracking_codes_array', $parcelTrackingCodesArray);
        $this->set_content_data('orders_id', $this->orderId);
        $this->set_content_data('page_token', $this->pageToken);
        
        /* Options */
        $this->_buildOptionsHTML();
    }
    
    
    protected function _buildOptionsHTML()
    {
        $parcelServices = $this->parcelServiceService->getParcelServices();
        foreach ($parcelServices as $parcelService) {
            $key                      = $parcelService->id();
            $val                      = $parcelService->name();
            $parcelOptionsArray[$key] = $val;
            
            if ($parcelService->isDefault()) {
                $selected = $key;
            }
        }
        
        $this->set_content_data('parcel_tracking_service_options', $parcelOptionsArray);
        $this->set_content_data('parcel_tracking_service_options_selected', $selected);
    }
    
    
    /**
     * @param mixed $p_orderId
     */
    public function setOrderId($p_orderId)
    {
        $this->orderId = (int)$p_orderId;
    }
    
    
    /**
     * @param string $p_pageToken
     */
    public function setPageToken($p_pageToken)
    {
        $this->pageToken = (string)$p_pageToken;
    }
}