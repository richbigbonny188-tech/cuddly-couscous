<?php
/*--------------------------------------------------------------------
 ServiceProvider.php 2020-2-20
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Product\Model;

use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Product\Model\Listener\OnGetSellingUnitModelEventListener;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitModelEvent;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 *
 * @package Gambio\Shop\Product\Model
 * @property-read Container $container
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        OnGetSellingUnitModelEventListener::class
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(OnGetSellingUnitModelEventListener::class);
    }


    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetSellingUnitModelEvent::class, OnGetSellingUnitModelEventListener::class);
    }
}