<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitEanEventListener.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Database\Listeners;

use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitEanEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Ean;

/**
 * Class OnGetSellingUnitEanEventListener
 * @package Gambio\Shop\Properties\SellingUnitEan\Listener
 */
class OnGetSellingUnitEanEventListener
{
    public const PRIORITY = 5000;

    /**
     * @var PropertiesReaderServiceInterface
     */
    protected $service;
    
    /**
     * OnGetSellingUnitEanEventListener constructor.
     *
     * @param PropertiesReaderServiceInterface $service
     */
    public function __construct(
        PropertiesReaderServiceInterface $service
    ) {
        $this->service = $service;
    }
    
    
    /**
     *
     * @param OnGetSellingUnitEanEventInterface $event
     *
     */
    public function __invoke(OnGetSellingUnitEanEventInterface $event)
    {
        $combination = $this->service->getCombinationFor($event->id());
        if ($combination && $combination->ean()->value()) {
            $event->builder()->wipeData()->withEanAtPos(new Ean($combination->ean()->value()), 1000);
        }
    }
}