<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitPriceEventListener.php 2020-12-21
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Database\Listeners;

use Exception;
use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\Properties\Properties\Entities\CheapestCombination;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitPriceEventInterface;

/**
 * Class OnGetSellingUnitPriceEventListener
 * @package Gambio\Shop\Properties\SellingUnitPrice\Listener
 */
class OnGetSellingUnitPriceEventListener
{
    /**
     * @var PropertiesReaderServiceInterface
     */
    protected $readService;
    
    
    /**
     * OnGetSellingUnitPriceEventListener constructor.
     *
     * @param PropertiesReaderServiceInterface $readService
     */
    public function __construct(
        PropertiesReaderServiceInterface $readService
    ) {
        $this->readService = $readService;
    }
    
    
    /**
     * @param OnGetSellingUnitPriceEventInterface $event
     */
    public function __invoke(OnGetSellingUnitPriceEventInterface $event)
    {
        try {
            $cheapestCombination = $this->readService->getCheapestCombinationFor($event->id());
            if ($cheapestCombination) {
                $event->builder()
                    ->withData('combination', $cheapestCombination->id()->value())
                    ->withData('cheapest', $cheapestCombination instanceof CheapestCombination);
            }
        } catch (Exception $e) {
            //silent the exception
        }
    }
}
