<?php
/*------------------------------------------------------------------------------
 ServiceProvider.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Product\Ean;

use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Product\Ean\Listener\OnGetSellingUnitEanEventListener;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitEanEvent;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Product\Ean
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        OnGetSellingUnitEanEventListener::class
    ];
    
    
    /**
     * @inheritDoc
     */
    public function boot()
    {
        /**
         * @var EventListenerProvider $listenerProvider
         */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetSellingUnitEanEvent::class, OnGetSellingUnitEanEventListener::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->container->share(OnGetSellingUnitEanEventListener::class);
    }
}