<?php
/*--------------------------------------------------------------------------------------------------
    OnSellingUnitIdCreateListener.php 2020-02-18
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\Properties\SellingUnit\Database\Listener;

use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\SellingUnit\Unit\Events\Interfaces\OnSellingUnitIdCreateEventInterface;

class OnSellingUnitIdCreateListener
{
    
    /**
     * @var PropertiesReaderServiceInterface
     */
    private $service;
    
    
    /**
     * OnSellingUnitIdCreateListener constructor.
     *
     * @param PropertiesReaderServiceInterface $service
     */
    public function __construct(PropertiesReaderServiceInterface $service)
    {
        $this->service = $service;
    }
    
    
    /**
     * @param OnSellingUnitIdCreateEventInterface $event
     *
     * @return OnSellingUnitIdCreateEventInterface
     */
    public function __invoke(OnSellingUnitIdCreateEventInterface $event): OnSellingUnitIdCreateEventInterface
    {
        $combinationId = 0;
        foreach($event->sets() as $type => $value){
            if ($type === 'product') {
                preg_match("/[\d\{\}]+x(\d+)/", $value, $t_extract);
                if (isset($t_extract[1])) {
                    $combinationId = (int)$t_extract[1];
                }
            } elseif ($type === 'info') {
                preg_match("/p[\d\{\}]+x(\d+)_/", $value, $t_extract);
                
                if (isset($t_extract[1])) {
                    $combinationId = (int)$t_extract[1];
                }
            } elseif (in_array(strtolower($type),['combi_id', 'combi', 'combid'] )) {
                $combinationId = (int)$value;
            }
        }
        if ($combinationId) {
            $this->service->addPropertyInfoToBuilder($combinationId, $event->builder());
        }
        
        return $event;
    }
    
}