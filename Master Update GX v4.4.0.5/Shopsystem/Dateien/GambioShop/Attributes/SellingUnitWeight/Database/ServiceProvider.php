<?php
/*--------------------------------------------------------------------
 ServiceProvider.php 2020-12-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitWeight\Database;

use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Attributes\SellingUnit\Database\Service\ReadServiceInterface;
use Gambio\Shop\Attributes\SellingUnitWeight\Database\Listener\OnGetSellingUnitWeightEventListener;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitWeightEvent;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Attributes\SellingUnitWeight\Database
 * @property-read Container $container
 * @codeCoverageIgnore
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        OnGetSellingUnitWeightEventListener::class
    ];
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(OnGetSellingUnitWeightEventListener::class)->addArgument(ReadServiceInterface::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetSellingUnitWeightEvent::class,
                                          OnGetSellingUnitWeightEventListener::class);
    }
}