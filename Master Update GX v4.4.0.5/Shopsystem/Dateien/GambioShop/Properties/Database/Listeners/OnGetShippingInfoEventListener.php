<?php
/*--------------------------------------------------------------------
 OnGetShippingInfoEventListener.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Database\Listeners;

use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\Properties\Properties\Entities\CheapestCombination;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetShippingInfoEventInterface;

class OnGetShippingInfoEventListener
{
    /**
     * @var PropertiesReaderServiceInterface
     */
    protected $service;
    
    
    /**
     * OnGetSellingUnitWeightEventListener constructor.
     *
     * @param PropertiesReaderServiceInterface $service
     */
    public function __construct(PropertiesReaderServiceInterface $service)
    {
        $this->service = $service;
    }
    
    
    /**
     * @param OnGetShippingInfoEventInterface $event
     */
    public function __invoke(OnGetShippingInfoEventInterface $event)
    {
        $event->builder()->withLanguage($event->id()->language());
        $combination = $this->service->getCheapestCombinationFor($event->id());
        
        if ($combination && $combination->shippingStatus()) {
            if (!($combination instanceof CheapestCombination)) {
                $event->builder()->withStatus($combination->shippingStatus()->value(), 1000);
            } else {
                $event->builder()->withStatus(0, 1000);
            }
        }
    }
}