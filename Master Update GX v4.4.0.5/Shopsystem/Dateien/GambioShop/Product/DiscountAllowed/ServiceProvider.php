<?php
/*--------------------------------------------------------------------------------------------------
    ServiceProvider.php 2021-03-29
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Shop\Product\DiscountAllowed;

use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Product\DiscountAllowed\Listener\OnGetProductInfoEventListener;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetProductInfoEvent;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Product\DiscountAllowed
 * @property-read Container $container
 * @codeCoverageIgnore
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        OnGetProductInfoEventListener::class
    ];
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetProductInfoEvent::class, OnGetProductInfoEventListener::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(OnGetProductInfoEventListener::class);
    }
}
