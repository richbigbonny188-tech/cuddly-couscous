<?php
/*--------------------------------------------------------------------------------------------------
    ServiceProvider.php 2020-11-27
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\Properties\SellingUnit\Database;

use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\Properties\SellingUnit\Database\Listener\OnSellingUnitIdCreateListener;
use Gambio\Shop\SellingUnit\Unit\Events\OnSellingUnitIdCreateEvent;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Properties\SellingUnit\Database
 * @codeCoverageIgnore service providers don't need to be tested
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    
    /**
     * @var array
     */
    protected $provides = [
        OnSellingUnitIdCreateListener::class
    ];
    
    
    /**
     * @inheritDoc
     */
    public function boot()
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnSellingUnitIdCreateEvent::class, OnSellingUnitIdCreateListener::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->container->share(OnSellingUnitIdCreateListener::class)
                        ->addArgument(PropertiesReaderServiceInterface::class);
    }
}