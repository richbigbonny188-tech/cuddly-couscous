<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitModelEventListener.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Database\Listeners;

use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\Properties\Database\Services\PropertiesReaderService;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitModelEvent;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Model;

/**
 * Class OnGetSellingUnitModelEventListener
 *
 * @package Gambio\Shop\Properties\SellingUnitModel\Database\Listener
 */
class OnGetSellingUnitModelEventListener
{
    /**
     * @var PropertiesReaderService
     */
    protected $service;

    /**
     * @var bool
     */
    protected $appendPropertiesModel;


    /**
     * OnGetSellingUnitModelEventListener constructor.
     *
     * @param PropertiesReaderServiceInterface $service
     * @param bool $appendPropertiesModel
     */
    public function __construct(
        PropertiesReaderServiceInterface $service,
        bool $appendPropertiesModel
    ) {
        $this->service               = $service;
        $this->appendPropertiesModel = $appendPropertiesModel;
    }


    /**
     *
     * @param OnGetSellingUnitModelEvent $event
     *
     * @return OnGetSellingUnitModelEvent
     */
    public function __invoke(OnGetSellingUnitModelEvent $event)
    {
        $combination = $this->service->getCombinationFor($event->id());

        if ($combination && $combination->model()->value() !== '') {
            if ($this->appendPropertiesModel === false) {
                $event->builder()->wipeData()->withModelAtPos(new Model($combination->model()->value()), 3000);
                $event->stopPropagation();
                return $event;
            }
            $event->builder()->withModelAtPos(new Model($combination->model()->value()), 3000);
        }

    }

}