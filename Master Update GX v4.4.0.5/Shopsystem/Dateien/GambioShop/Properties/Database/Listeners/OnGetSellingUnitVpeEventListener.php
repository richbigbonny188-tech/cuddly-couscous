<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitVpeEventListener.php 2020-12-10
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Database\Listeners;

use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\Properties\Properties\Entities\CheapestCombination;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitVpeEventInterface;

class OnGetSellingUnitVpeEventListener
{
    /**
     * @var PropertiesReaderServiceInterface
     */
    protected $service;
    
    
    /**
     * OnGetSellingUnitVpeEventListener constructor.
     *
     * @param PropertiesReaderServiceInterface $service
     */
    public function __construct(PropertiesReaderServiceInterface $service)
    {
        $this->service = $service;
    }
    
    
    /**
     * @param OnGetSellingUnitVpeEventInterface $event
     */
    public function __invoke(OnGetSellingUnitVpeEventInterface $event)
    {
        if ($event->product()->getVpeStatus()) {
            $combination = $this->service->getCheapestCombinationFor($event->id());
            if ($combination) {
                if ($combination instanceof CheapestCombination) {
                    if ($combination->surcharge() && $combination->surcharge()->isNonLinear()) {
                        $event->setVpe(null, 10000);
                    }
                } elseif ($combination->vpe()) {
                    $event->setVpe($combination->vpe(), 10000);
                }
            }
        }
    }
}
