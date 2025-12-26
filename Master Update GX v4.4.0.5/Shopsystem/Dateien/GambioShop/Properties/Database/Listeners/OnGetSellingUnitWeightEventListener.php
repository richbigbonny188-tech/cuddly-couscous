<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitWeightEventListener.php 2020-11-08
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Database\Listeners;

use Gambio\Shop\Properties\Database\Exceptions\CombinationNotFoundException;
use Gambio\Shop\Properties\Database\Exceptions\IncompletePropertyListException;
use Gambio\Shop\Properties\Database\Exceptions\ProductDoesntHavePropertiesException;
use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitWeightEventInterface;

/**
 * Class OnGetSellingUnitWeightEventListener
 * @package Gambio\Shop\Properties\SellingUnitWeight\Database\Listener
 */
class OnGetSellingUnitWeightEventListener
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
     * @param OnGetSellingUnitWeightEventInterface $event
     */
    public function __invoke(OnGetSellingUnitWeightEventInterface $event)
    {
        try {
            $combination = $this->service->getCombinationFor($event->id(), false);
            if ($combination && $combination->weight()) {
                if ($combination->weight()->isMainWeight()) {
                    $event->builder()->setMainWeight($combination->weight(), 10000);
                } else {
                    $event->builder()->addWeight($combination->weight());
                }
            }
        } catch (CombinationNotFoundException $e) {
            $event->builder()->setMainWeight(null, 10000);
        } catch (IncompletePropertyListException $e) {
            // When we set the Main Weight to null, it will not show in the first products info page load
            // We only set it to null if the product "show weight" is deactivated
            if (!$event->product()->showWeight()) {
                $event->builder()->setMainWeight(null, 10000);
            }
        } catch (ProductDoesntHavePropertiesException $e) {
            //do nothing
        }
    
        
    }
}
