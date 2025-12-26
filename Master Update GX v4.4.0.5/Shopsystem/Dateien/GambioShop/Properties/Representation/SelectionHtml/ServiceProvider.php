<?php
/*--------------------------------------------------------------------------------------------------
    ServiceProvider.php 2020-11-27
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Representation\SelectionHtml;

use Doctrine\DBAL\Connection;
use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Properties\Representation\SelectionHtml\Listener\OnGetModifierHtmlEventListener;
use Gambio\Shop\Properties\Representation\SelectionHtml\Repository\Readers\Reader;
use Gambio\Shop\Properties\Representation\SelectionHtml\Repository\Readers\ReaderInterface;
use Gambio\Shop\Properties\Representation\SelectionHtml\Repository\Repository;
use Gambio\Shop\Properties\Representation\SelectionHtml\Repository\RepositoryInterface;
use Gambio\Shop\SellingUnit\Presentation\Events\OnGetModifierHtmlEvent;
use Gambio\Shop\SellingUnit\Presentation\ValueObjects\OutOfStockMarkings;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Properties\Representation\SelectionHtml
 * @property-read Container $container
 * @codeCoverageIgnore service providers don't need to be tested
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    protected $provides = [
        OnGetModifierHtmlEventListener::class
    ];
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(OnGetModifierHtmlEventListener::class)->addArgument(ReadServiceInterface::class);
        $this->container->share(ReadServiceInterface::class, ReadService::class)->addArgument(RepositoryInterface::class);
        $this->container
            ->share(RepositoryInterface::class, Repository::class)
            ->addArgument(ReaderInterface::class);
        $this->container->share(ReaderInterface::class, Reader::class)->addArgument(Connection::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetModifierHtmlEvent::class, OnGetModifierHtmlEventListener::class);
    }
}